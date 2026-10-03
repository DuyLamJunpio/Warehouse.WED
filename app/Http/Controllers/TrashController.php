<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Categories;
use App\Models\Collection;
use App\Models\Customer;
use App\Models\ImageModel;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductLocation;
use App\Models\ProductStyle;
use App\Models\ProductVariant;
use App\Models\PrintBlank;
use App\Models\SiteText;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TrashController extends Controller
{
    /** @var array<string, array{model: class-string<Model>, label: string, name: string}> */
    private const TYPES = [
        'product' => ['model' => Product::class, 'label' => 'Sản phẩm', 'name' => 'product_name'],
        'image' => ['model' => ImageModel::class, 'label' => 'Ảnh sản phẩm', 'name' => 'name'],
        'style' => ['model' => ProductStyle::class, 'label' => 'Mẫu sản phẩm', 'name' => 'name'],
        'variant' => ['model' => ProductVariant::class, 'label' => 'Biến thể', 'name' => 'sku'],
        'location' => ['model' => ProductLocation::class, 'label' => 'Vị trí kho', 'name' => 'code'],
        'category' => ['model' => Categories::class, 'label' => 'Danh mục', 'name' => 'name'],
        'customer' => ['model' => Customer::class, 'label' => 'Khách hàng', 'name' => 'customer_name'],
        'invoice' => ['model' => Invoice::class, 'label' => 'Hóa đơn', 'name' => 'order_code'],
        'supplier' => ['model' => Supplier::class, 'label' => 'Nhà cung cấp', 'name' => 'supplier_name'],
        'user' => ['model' => User::class, 'label' => 'Tài khoản', 'name' => 'name'],
        'banner' => ['model' => Banner::class, 'label' => 'Banner', 'name' => 'heading'],
        'collection' => ['model' => Collection::class, 'label' => 'Bộ sưu tập', 'name' => 'title'],
        'site_text' => ['model' => SiteText::class, 'label' => 'Nội dung trang', 'name' => 'value'],
        'print_blank' => ['model' => PrintBlank::class, 'label' => 'Phôi in áo', 'name' => 'name'],
    ];

    public function index()
    {
        $groups = collect(self::TYPES)->map(function (array $type, string $key): array {
            $items = $type['model']::onlyTrashed()
                ->latest('deleted_at')
                ->get()
                ->map(fn (Model $item): array => [
                    'id' => $item->getKey(),
                    'name' => (string) ($item->{$type['name']} ?: '#' . $item->getKey()),
                    'deleted_at' => $item->deleted_at?->format('d/m/Y H:i'),
                    'days_left' => max(0, 30 - $item->deleted_at?->diffInDays(now())),
                ]);

            return [
                'key' => $key,
                'label' => $type['label'],
                'items' => $items,
            ];
        })->filter(fn (array $group): bool => $group['items']->isNotEmpty())->values();

        return view('trash.index', compact('groups'));
    }

    public function restore(string $type, int $id)
    {
        abort_unless(isset(self::TYPES[$type]), 404);
        $modelClass = self::TYPES[$type]['model'];
        $record = $modelClass::onlyTrashed()->findOrFail($id);

        try {
            DB::transaction(fn (): bool => $record->restore());
        } catch (QueryException $exception) {
            report($exception);

            return response()->json([
                'error' => 'Không thể khôi phục vì dữ liệu đang trùng với một bản ghi đang hoạt động.',
            ], 422);
        }

        return response()->json([
            'success' => 'Đã khôi phục ' . self::TYPES[$type]['label'] . '.',
        ]);
    }

    public function data()
    {
        return $this->index();
    }
}
