<?php

namespace App\Support;

final class ProductPricing
{
    /** Convert a fixed or percentage discount into the sale price stored for a product. */
    public static function discountedPrice(int $sellPrice, string $type, int|float $value): ?int
    {
        $reduction = $type === 'percent'
            ? (int) round($sellPrice * (float) $value / 100)
            : (int) $value;

        return $reduction > 0 ? max(0, $sellPrice - $reduction) : null;
    }

    /** Return the displayed discount percentage from the stored sale and original prices. */
    public static function discountPercent(?int $sellPrice, ?int $discountPrice): ?float
    {
        if ($sellPrice === null || $sellPrice <= 0 || $discountPrice === null || $discountPrice >= $sellPrice) {
            return null;
        }

        return round((($sellPrice - $discountPrice) / $sellPrice) * 100, 1);
    }
}
