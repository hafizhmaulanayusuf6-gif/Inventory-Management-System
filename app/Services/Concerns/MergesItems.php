<?php

namespace App\Services\Concerns;

trait MergesItems
{
    /**
     * Gabungkan baris dengan product_id yang sama (qty dijumlahkan) lalu
     * urutkan berdasarkan product_id. Urutan konsisten mencegah deadlock
     * antar transaksi yang mengunci produk yang sama.
     *
     * @param  array<int, array{product_id: int|string, qty: int|string}>  $items
     * @return array<int, int> [product_id => qty]
     */
    protected function mergeItems(array $items): array
    {
        $merged = [];

        foreach ($items as $item) {
            $productId = (int) $item['product_id'];
            $merged[$productId] = ($merged[$productId] ?? 0) + (int) $item['qty'];
        }

        ksort($merged);

        return $merged;
    }
}