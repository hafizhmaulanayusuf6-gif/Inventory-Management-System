<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Satu-satunya tempat yang boleh mengubah kolom products.stock.
 * Setiap perubahan stok selalu tercatat di stock_movements (kartu stok).
 */
class StockService
{
    /**
     * Tambah stok (barang masuk).
     */
    public function increase(
        Product|int $product,
        int $qty,
        int $userId,
        ?Model $reference = null,
    ): StockMovement {
        return $this->mutate($product, StockMovementType::In, $qty, $userId, $reference);
    }

    /**
     * Kurangi stok (barang keluar).
     *
     * @throws InsufficientStockException jika stok tidak cukup
     */
    public function decrease(
        Product|int $product,
        int $qty,
        int $userId,
        ?Model $reference = null,
    ): StockMovement {
        return $this->mutate($product, StockMovementType::Out, $qty, $userId, $reference);
    }

    private function mutate(
        Product|int $product,
        StockMovementType $type,
        int $qty,
        int $userId,
        ?Model $reference,
    ): StockMovement {
        if ($qty < 1) {
            throw new InvalidArgumentException('Jumlah harus lebih dari 0.');
        }

        $productId = $product instanceof Product ? $product->getKey() : $product;

        // Jika dipanggil di dalam transaksi luar (misalnya saat menyimpan dokumen
        // inbound/outbound), Laravel otomatis memakai savepoint, jadi tetap aman.
        return DB::transaction(function () use ($product, $productId, $type, $qty, $userId, $reference) {
            // Kunci baris produk sampai transaksi selesai. Request lain yang mengubah
            // produk yang sama menunggu, sehingga tidak ada race condition. Stok
            // SELALU dibaca ulang dari database, bukan dari instance di memori.
            $locked = Product::query()
                ->lockForUpdate()
                ->findOrFail($productId);

            $balance = $type === StockMovementType::In
                ? $locked->stock + $qty
                : $locked->stock - $qty;

            if ($balance < 0) {
                throw new InsufficientStockException($locked, $qty, $locked->stock);
            }

            // 'stock' tidak ada di $fillable, jadi diisi lewat forceFill.
            $locked->forceFill(['stock' => $balance])->save();

            $movement = StockMovement::create([
                'product_id' => $productId,
                'type' => $type,
                'qty' => $qty,
                'balance' => $balance,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'user_id' => $userId,
            ]);

            // Sinkronkan instance yang dikirim pemanggil agar tidak basi.
            if ($product instanceof Product) {
                $product->setAttribute('stock', $balance);
                $product->syncOriginalAttribute('stock');
            }

            return $movement;
        });
    }
}