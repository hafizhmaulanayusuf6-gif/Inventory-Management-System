<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Exceptions\InsufficientStockException;
use App\Models\Outbound;
use App\Services\Concerns\MergesItems;
use Illuminate\Support\Facades\DB;

class OutboundService
{
    use MergesItems;

    public function __construct(private readonly StockService $stock)
    {
    }

    /**
     * Simpan dokumen barang keluar beserta itemnya, lalu kurangi stok.
     * Jika stok salah satu barang tidak cukup, seluruh dokumen dibatalkan.
     *
     * @param  array{date: string, destination: string, items: array}  $data
     *
     * @throws InsufficientStockException
     */
    public function create(array $data, int $userId): Outbound
    {
        $items = $this->mergeItems($data['items']);

        return DB::transaction(function () use ($data, $items, $userId) {
            $outbound = Outbound::create([
                'date' => $data['date'],
                'destination' => $data['destination'],
                'user_id' => $userId,
                'status' => DocumentStatus::Completed,
            ]);

            foreach ($items as $productId => $qty) {
                $outbound->items()->create([
                    'product_id' => $productId,
                    'qty' => $qty,
                ]);

                $this->stock->decrease($productId, $qty, $userId, $outbound);
            }

            return $outbound;
        });
    }
}