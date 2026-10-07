<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Models\Inbound;
use App\Services\Concerns\MergesItems;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InboundService
{
    use MergesItems;

    public function __construct(private readonly StockService $stock)
    {
    }

    /**
     * Simpan dokumen barang masuk beserta itemnya, lalu tambah stok.
     * Semua dalam satu transaksi: jika satu langkah gagal, semuanya dibatalkan.
     *
     * @param  array{date: string, supplier_id: int, reference_no?: ?string, items: array}  $data
     */
    public function create(array $data, int $userId): Inbound
    {
        $items = $this->mergeItems($data['items']);

        return DB::transaction(function () use ($data, $items, $userId) {
            $inbound = Inbound::create([
                'date' => $data['date'],
                'supplier_id' => $data['supplier_id'],
                'reference_no' => $data['reference_no'] ?? $this->generateReferenceNo(),
                'user_id' => $userId,
                'status' => DocumentStatus::Completed,
            ]);

            foreach ($items as $productId => $qty) {
                $inbound->items()->create([
                    'product_id' => $productId,
                    'qty' => $qty,
                ]);

                $this->stock->increase($productId, $qty, $userId, $inbound);
            }

            return $inbound;
        });
    }

    private function generateReferenceNo(): string
    {
        return 'IN-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
    }
}