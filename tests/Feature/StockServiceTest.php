<?php

namespace Tests\Feature;

use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\Inbound;
use App\Models\Outbound;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $service;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(StockService::class);
        $this->user = User::factory()->create();
    }

    public function test_increase_adds_stock_and_records_movement(): void
    {
        $product = Product::factory()->create();
        $inbound = Inbound::factory()->create();

        $movement = $this->service->increase($product, 10, $this->user->id, $inbound);

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame(StockMovementType::In, $movement->type);

        $this->assertDatabaseHas('stock_movements', [
            'id' => $movement->id,
            'product_id' => $product->id,
            'type' => 'in',
            'qty' => 10,
            'balance' => 10,
            'reference_type' => 'inbound',
            'reference_id' => $inbound->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_decrease_subtracts_stock_and_records_movement(): void
    {
        $product = Product::factory()->withStock(20)->create();
        $outbound = Outbound::factory()->create();

        $movement = $this->service->decrease($product, 8, $this->user->id, $outbound);

        $this->assertSame(12, $product->fresh()->stock);
        $this->assertSame(StockMovementType::Out, $movement->type);

        $this->assertDatabaseHas('stock_movements', [
            'id' => $movement->id,
            'product_id' => $product->id,
            'type' => 'out',
            'qty' => 8,
            'balance' => 12,
            'reference_type' => 'outbound',
            'reference_id' => $outbound->id,
        ]);
    }

    public function test_decrease_beyond_available_stock_throws_and_changes_nothing(): void
    {
        $product = Product::factory()->withStock(5)->create();

        try {
            $this->service->decrease($product, 6, $this->user->id);
            $this->fail('Seharusnya melempar InsufficientStockException.');
        } catch (InsufficientStockException $e) {
            $this->assertSame(6, $e->requested);
            $this->assertSame(5, $e->available);
        }

        $this->assertSame(5, $product->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_decrease_to_exactly_zero_is_allowed(): void
    {
        $product = Product::factory()->withStock(5)->create();

        $movement = $this->service->decrease($product, 5, $this->user->id);

        $this->assertSame(0, $product->fresh()->stock);
        $this->assertSame(0, $movement->balance);
    }

    public function test_quantity_zero_is_rejected(): void
    {
        $product = Product::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        $this->service->increase($product, 0, $this->user->id);
    }

    public function test_negative_quantity_is_rejected(): void
    {
        $product = Product::factory()->withStock(10)->create();

        $this->expectException(InvalidArgumentException::class);

        $this->service->decrease($product, -3, $this->user->id);
    }

    public function test_balance_chain_is_consistent_across_operations(): void
    {
        $product = Product::factory()->create(); // stok 0

        $this->service->increase($product, 10, $this->user->id);
        $this->service->increase($product, 5, $this->user->id);
        $this->service->decrease($product, 7, $this->user->id);

        $movements = StockMovement::where('product_id', $product->id)->orderBy('id')->get();

        $this->assertEquals([10, 15, 8], $movements->pluck('balance')->all());
        $this->assertEquals(
            [StockMovementType::In, StockMovementType::In, StockMovementType::Out],
            $movements->pluck('type')->all()
        );
        $this->assertSame(8, $product->fresh()->stock);
        $this->assertSame($movements->last()->balance, $product->fresh()->stock);
    }

    public function test_movement_reference_resolves_to_the_document(): void
    {
        $product = Product::factory()->withStock(10)->create();
        $outbound = Outbound::factory()->create();

        $movement = $this->service->decrease($product, 3, $this->user->id, $outbound);

        $this->assertSame('outbound', $movement->reference_type);
        $this->assertTrue($movement->fresh()->reference->is($outbound));
    }

    public function test_outer_transaction_rollback_reverts_stock_and_movements(): void
    {
        $product = Product::factory()->create();

        try {
            DB::transaction(function () use ($product) {
                $this->service->increase($product, 5, $this->user->id);

                throw new RuntimeException('Gagal di tengah proses.');
            });
        } catch (RuntimeException) {
            // diharapkan
        }

        $this->assertSame(0, $product->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_multi_item_operation_is_atomic_when_one_item_fails(): void
    {
        $productA = Product::factory()->withStock(10)->create();
        $productB = Product::factory()->withStock(2)->create();

        try {
            DB::transaction(function () use ($productA, $productB) {
                $this->service->decrease($productA, 5, $this->user->id); // berhasil
                $this->service->decrease($productB, 5, $this->user->id); // stok tidak cukup
            });
            $this->fail('Seharusnya melempar InsufficientStockException.');
        } catch (InsufficientStockException) {
            // diharapkan
        }

        // Pengurangan produk A ikut dibatalkan.
        $this->assertSame(10, $productA->fresh()->stock);
        $this->assertSame(2, $productB->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_service_uses_fresh_stock_from_database_not_stale_model(): void
    {
        $product = Product::factory()->withStock(10)->create();

        // Proses lain mengubah stok di database, instance $product di memori jadi basi.
        Product::query()->whereKey($product->id)->update(['stock' => 15]);

        $movement = $this->service->increase($product, 5, $this->user->id);

        $this->assertSame(20, $product->fresh()->stock);
        $this->assertSame(20, $movement->balance);
    }

    public function test_passed_product_instance_reflects_new_stock(): void
    {
        $product = Product::factory()->withStock(10)->create();

        $this->service->increase($product, 5, $this->user->id);

        $this->assertSame(15, $product->stock);
        $this->assertFalse($product->isDirty('stock'));
    }

    public function test_service_accepts_product_id(): void
    {
        $product = Product::factory()->create();

        $this->service->increase($product->id, 7, $this->user->id);

        $this->assertSame(7, $product->fresh()->stock);
    }
}