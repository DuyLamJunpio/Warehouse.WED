<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductComboItem;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

/**
 * Chuyển các dòng bán thành biến thể vật lý phải xuất kho.
 *
 * Hóa đơn luôn giữ nguyên dòng combo (ví dụ 1 combo), còn lớp này là nơi duy
 * nhất nhân số thành phần (ví dụ 1 × 5 thanh gỗ) để kiểm tồn và ghi kho.
 */
class StockAllocator
{
    /**
     * @param array<int, int> $wanted variant_id => số lượng bán
     * @return array{requirements: array<int, int>, variants: Collection<int, ProductVariant>}
     */
    public function requirements(array $wanted): array
    {
        $wanted = collect($wanted)
            ->mapWithKeys(fn ($quantity, $id) => [(int) $id => (int) $quantity])
            ->filter(fn ($quantity) => $quantity > 0)
            ->all();

        if ($wanted === []) {
            return ['requirements' => [], 'variants' => collect()];
        }

        $variants = ProductVariant::with([
            'product',
            'comboComponents.componentVariant.product',
        ])
            ->whereIn('id', array_keys($wanted))
            ->get()
            ->keyBy('id');

        $requirements = [];
        foreach ($wanted as $variantId => $quantity) {
            $variant = $variants->get($variantId);
            if (! $variant || ! $variant->product) {
                throw new \RuntimeException("Không tìm thấy biến thể #{$variantId} của đơn hàng.");
            }

            if ($variant->comboComponents->isNotEmpty()) {
                foreach ($variant->comboComponents as $component) {
                    $physical = $component->componentVariant;
                    if (! $physical || ! $physical->product) {
                        throw new \RuntimeException('Combo ' . $variant->product->product_name . ' có thành phần không còn tồn tại.');
                    }

                    $requirements[$physical->id] = ($requirements[$physical->id] ?? 0)
                        + ($quantity * $component->quantity);
                }

                continue;
            }

            // Hàng đặt theo yêu cầu không có tồn vật lý để trừ.
            if ($variant->product->manage_stock) {
                $requirements[$variant->id] = ($requirements[$variant->id] ?? 0) + $quantity;
            }
        }

        return ['requirements' => $requirements, 'variants' => $variants];
    }

    /** Trả null khi biến thể không bị giới hạn tồn kho. */
    public function availableForVariant(ProductVariant $variant): ?int
    {
        $variant->loadMissing([
            'product',
            'comboComponents.componentVariant.product',
        ]);

        if (! $variant->product) {
            return 0;
        }

        if ($variant->comboComponents->isEmpty()) {
            return $variant->product->manage_stock ? max(0, (int) $variant->quantity) : null;
        }

        $available = null;
        foreach ($variant->comboComponents as $component) {
            $physical = $component->componentVariant;
            if (! $physical || ! $physical->product) {
                return 0;
            }

            if (! $physical->product->manage_stock) {
                continue;
            }

            $componentAvailable = intdiv(max(0, (int) $physical->quantity), (int) $component->quantity);
            $available = $available === null ? $componentAvailable : min($available, $componentAvailable);
        }

        return $available;
    }

    /**
     * Kiểm tồn cho tất cả nhu cầu đã nhân combo. Caller cần gọi trong transaction
     * và khóa các dòng kết quả trước khi thực sự trừ kho.
     *
     * @param array<int, int> $requirements variant_id => số lượng vật lý
     * @param Collection<int, ProductVariant>|null $lockedVariants
     */
    public function assertSufficient(array $requirements, ?Collection $lockedVariants = null): void
    {
        if ($requirements === []) {
            return;
        }

        $variants = $lockedVariants
            ?? ProductVariant::with('product')->whereIn('id', array_keys($requirements))->get()->keyBy('id');

        foreach ($requirements as $variantId => $quantity) {
            $variant = $variants->get($variantId);
            if (! $variant || ! $variant->product) {
                throw new \RuntimeException("Không tìm thấy biến thể #{$variantId} để trừ tồn.");
            }

            if ($variant->product->manage_stock && (int) $variant->quantity < $quantity) {
                throw new \RuntimeException(
                    'Tồn kho không đủ cho ' . $variant->product->product_name . ' (' . $variant->label . ').'
                );
            }
        }
    }

    /** Cập nhật trạng thái các combo chịu ảnh hưởng khi một thành phần thay đổi tồn. */
    public function syncAffectedComboStatuses(array $componentVariantIds): void
    {
        $componentVariantIds = array_values(array_unique(array_map('intval', $componentVariantIds)));
        if ($componentVariantIds === []) {
            return;
        }

        $comboVariants = ProductVariant::with([
            'product',
            'comboComponents.componentVariant.product',
        ])
            ->whereIn('id', ProductComboItem::whereIn('component_variant_id', $componentVariantIds)
                ->pluck('combo_variant_id'))
            ->get();

        foreach ($comboVariants->groupBy('product_id') as $variants) {
            /** @var ProductVariant $first */
            $first = $variants->first();
            $product = $first->product;
            if (! $product || ! $product->is_combo) {
                continue;
            }

            $hasStock = $variants->contains(fn (ProductVariant $variant) => $this->availableForVariant($variant) === null
                || $this->availableForVariant($variant) > 0);
            $status = $hasStock ? 1 : 2;
            if ((int) $product->status !== $status) {
                $product->update(['status' => $status]);
            }
        }
    }

    /** Tính trạng thái còn bán của một sản phẩm combo sau khi thay đổi cấu hình. */
    public function syncComboProductStatus(Product $product): void
    {
        if (! $product->is_combo) {
            return;
        }

        $variants = $product->variants()->with([
            'product',
            'comboComponents.componentVariant.product',
        ])->get();

        $hasStock = $variants->contains(fn (ProductVariant $variant) => $this->availableForVariant($variant) === null
            || $this->availableForVariant($variant) > 0);
        $status = $hasStock ? 1 : 2;
        if ((int) $product->status !== $status) {
            $product->update(['status' => $status]);
        }
    }
}
