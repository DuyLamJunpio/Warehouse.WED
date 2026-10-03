<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\Categories;
use App\Models\Collection;
use App\Models\Customer;
use App\Models\ImageModel;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductComboItem;
use App\Models\ProductInvoice;
use App\Models\ProductLocation;
use App\Models\ProductStyle;
use App\Models\ProductVariant;
use App\Models\PrintBlank;
use App\Models\PrintMockup;
use App\Models\SiteText;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Dọn vĩnh viễn bản ghi đã nằm trong thùng rác quá thời hạn.
 *
 * Phôi in áo cũng được giữ trong thùng rác cùng thời hạn với catalogue.
 */
class TrashPurger
{
    public const RETENTION_DAYS = 30;

    /** @return array<string, int> */
    public function purge(?CarbonInterface $before = null, bool $dryRun = false): array
    {
        $before ??= now()->subDays(self::RETENTION_DAYS);
        $summary = [];

        $summary['products'] = $this->purgeExpiredProducts($before, $dryRun);
        $summary['print_blanks'] = $this->purgeExpiredPrintBlanks($before, $dryRun);

        foreach ([
            'images' => ImageModel::class,
            'styles' => ProductStyle::class,
            'variants' => ProductVariant::class,
            'locations' => ProductLocation::class,
            'categories' => Categories::class,
            'customers' => Customer::class,
            'invoices' => Invoice::class,
            'suppliers' => Supplier::class,
            'users' => User::class,
            'banners' => Banner::class,
            'collections' => Collection::class,
            'site_texts' => SiteText::class,
        ] as $label => $modelClass) {
            $summary[$label] = $this->purgeModel($modelClass, $before, $dryRun);
        }

        return $summary;
    }

    private function purgeExpiredPrintBlanks(CarbonInterface $before, bool $dryRun): int
    {
        $blanks = PrintBlank::onlyTrashed()->where('deleted_at', '<=', $before)->get();
        if ($dryRun) {
            return $blanks->count();
        }

        $count = 0;
        foreach ($blanks as $blank) {
            $paths = PrintMockup::where('print_blank_id', $blank->id)->pluck('path')->filter()->unique()->all();
            DB::transaction(function () use ($blank): void {
                $blank->techniques()->detach();
                PrintMockup::where('print_blank_id', $blank->id)->delete();
                $blank->colors()->delete();
                $blank->forceDelete();
            });
            $this->deleteUnreferencedFiles($paths);
            $count++;
        }

        return $count;
    }

    private function purgeExpiredProducts(CarbonInterface $before, bool $dryRun): int
    {
        $products = Product::onlyTrashed()
            ->where('deleted_at', '<=', $before)
            ->get();

        if ($dryRun) {
            return $products->count();
        }

        $count = 0;
        foreach ($products as $product) {
            $mediaPaths = [];

            DB::transaction(function () use ($product, &$mediaPaths): void {
                $mediaPaths = ImageModel::withTrashed()
                    ->where('product_id', $product->id)
                    ->pluck('path')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                // Giữ lại dòng lịch sử đơn hàng, chỉ bỏ liên kết tới catalogue
                // đã purge. Các bảng con thuộc riêng sản phẩm có thể dọn theo.
                ProductInvoice::where('product_id', $product->id)
                    ->update(['product_id' => null]);

                $variantIds = ProductVariant::withTrashed()
                    ->where('product_id', $product->id)
                    ->pluck('id');

                if ($variantIds->isNotEmpty()) {
                    ProductInvoice::whereIn('variant_id', $variantIds)
                        ->update(['variant_id' => null]);
                    StockAdjustment::whereIn('variant_id', $variantIds)
                        ->update(['variant_id' => null]);
                    StockMovement::whereIn('variant_id', $variantIds)
                        ->update(['variant_id' => null]);
                }

                ProductVariant::withTrashed()
                    ->where('product_id', $product->id)
                    ->forceDelete();
                ProductStyle::withTrashed()
                    ->where('product_id', $product->id)
                    ->forceDelete();
                ProductLocation::withTrashed()
                    ->where('product_id', $product->id)
                    ->forceDelete();
                ImageModel::withTrashed()
                    ->where('product_id', $product->id)
                    ->forceDelete();

                $product->forceDelete();
            });

            $this->deleteUnreferencedFiles($mediaPaths);
            $count++;
        }

        return $count;
    }

    private function purgeModel(string $modelClass, CarbonInterface $before, bool $dryRun): int
    {
        $records = $modelClass::onlyTrashed()
            ->where('deleted_at', '<=', $before)
            ->get();

        if ($dryRun) {
            return $records->count();
        }

        $count = 0;
        foreach ($records as $record) {
            $paths = $this->pathsFor($record);

            DB::transaction(function () use ($record, $modelClass): void {
                if ($modelClass === ProductVariant::class) {
                    ProductInvoice::where('variant_id', $record->id)->update(['variant_id' => null]);
                    StockAdjustment::where('variant_id', $record->id)->update(['variant_id' => null]);
                    StockMovement::where('variant_id', $record->id)->update(['variant_id' => null]);
                    ProductComboItem::where(function ($query) use ($record): void {
                        $query->where('combo_variant_id', $record->id)
                            ->orWhere('component_variant_id', $record->id);
                    })->delete();
                }

                if ($modelClass === ProductStyle::class) {
                    // Không để khóa ngoại cascade vô tình xóa biến thể đang
                    // hoạt động khi một mẫu bị purge riêng lẻ.
                    ProductVariant::withTrashed()
                        ->where('product_style_id', $record->id)
                        ->update(['product_style_id' => null]);
                }

                $record->forceDelete();
            });

            $this->deleteUnreferencedFiles($paths);
            $count++;
        }

        return $count;
    }

    /** @return list<string> */
    private function pathsFor(object $record): array
    {
        $paths = [];

        foreach (['path', 'avatar', 'image', 'image_path', 'media_path', 'poster_path', 'mobile_path', 'signature'] as $field) {
            if (! empty($record->{$field})) {
                $paths[] = (string) $record->{$field};
            }
        }

        return array_values(array_unique($paths));
    }

    /** @param list<string> $paths */
    private function deleteUnreferencedFiles(array $paths): void
    {
        foreach ($paths as $path) {
            $stillReferenced = ImageModel::withTrashed()->where('path', $path)->exists()
                || Categories::withTrashed()->where('image', $path)->exists()
                || Customer::withTrashed()->where('avatar', $path)->exists()
                || Banner::withTrashed()->where(function ($query) use ($path): void {
                    $query->where('media_path', $path)
                        ->orWhere('poster_path', $path)
                        ->orWhere('mobile_path', $path);
                })->exists()
                || Collection::withTrashed()->where('image_path', $path)->exists();

            if (! $stillReferenced) {
                Storage::delete($path);
            }
        }
    }
}
