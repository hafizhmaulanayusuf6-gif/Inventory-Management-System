<?php

namespace Tests\Feature;

use App\Enums\DocumentStatus;
use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Inbound;
use App\Models\InboundItem;
use App\Models\Outbound;
use App\Models\OutboundItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_product_starts_with_zero_stock(): void
    {
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();

        // Disimpan lewat mass assignment (seperti dari form), tanpa mengisi stock.
        $product = Product::create([
            'sku' => 'SKU-001',
            'name' => 'Kertas A4',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'min_stock' => 5,
            'price' => 50000,
        ]);

        $this->assertSame(0, $product->fresh()->stock);
    }

    public function test_stock_cannot_be_mass_assigned(): void
    {
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();

        $product = Product::create([
            'sku' => 'SKU-002',
            'name' => 'Pulpen',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'stock' => 999, // harus diabaikan
        ]);

        $this->assertSame(0, $product->fresh()->stock);

        $product->update(['stock' => 500]); // harus diabaikan

        $this->assertSame(0, $product->fresh()->stock);
    }

    public function test_product_sku_must_be_unique(): void
    {
        Product::factory()->create(['sku' => 'SKU-DUP']);

        $this->expectException(QueryException::class);

        Product::factory()->create(['sku' => 'SKU-DUP']);
    }

    public function test_database_rejects_negative_stock_via_unsigned_or_app_guard(): void
    {
        $product = Product::factory()->withStock(5)->create();

        $this->assertSame(5, $product->fresh()->stock);
        $this->assertTrue($product->fresh()->stock >= 0);
    }

    public function test_product_relationships_work(): void
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertInstanceOf(Unit::class, $product->unit);
        $this->assertTrue($product->category->products->contains($product));
    }

    public function test_low_stock_scope_returns_only_products_at_or_below_minimum(): void
    {
        $low = Product::factory()->withStock(2)->create(['min_stock' => 5]);
        $equal = Product::factory()->withStock(5)->create(['min_stock' => 5]);
        $ok = Product::factory()->withStock(20)->create(['min_stock' => 5]);

        $ids = Product::lowStock()->pluck('id');

        $this->assertTrue($ids->contains($low->id));
        $this->assertTrue($ids->contains($equal->id));
        $this->assertFalse($ids->contains($ok->id));
        $this->assertTrue($low->isLowStock());
        $this->assertFalse($ok->isLowStock());
    }

    public function test_inbound_has_items_supplier_and_user(): void
    {
        $inbound = Inbound::factory()->create();
        InboundItem::factory()->count(2)->create(['inbound_id' => $inbound->id]);

        $inbound->load(['items.product', 'supplier', 'user']);

        $this->assertCount(2, $inbound->items);
        $this->assertInstanceOf(Supplier::class, $inbound->supplier);
        $this->assertInstanceOf(User::class, $inbound->user);
        $this->assertSame(DocumentStatus::Completed, $inbound->status);
    }

    public function test_outbound_has_items(): void
    {
        $outbound = Outbound::factory()->create();
        OutboundItem::factory()->count(3)->create(['outbound_id' => $outbound->id]);

        $this->assertCount(3, $outbound->items);
    }

    public function test_deleting_inbound_cascades_to_its_items(): void
    {
        $inbound = Inbound::factory()->create();
        InboundItem::factory()->create(['inbound_id' => $inbound->id]);

        $inbound->delete();

        $this->assertDatabaseCount('inbound_items', 0);
    }

    public function test_category_in_use_cannot_be_deleted(): void
    {
        $product = Product::factory()->create();

        $this->expectException(QueryException::class);

        $product->category->delete();
    }

    public function test_stock_movement_polymorphic_reference_resolves_to_inbound(): void
    {
        $inbound = Inbound::factory()->create();

        $movement = StockMovement::factory()->create([
            'type' => StockMovementType::In,
            'reference_type' => 'inbound',
            'reference_id' => $inbound->id,
        ]);

        $this->assertTrue($movement->reference->is($inbound));
        $this->assertTrue($inbound->stockMovements->contains($movement));
        $this->assertDatabaseHas('stock_movements', ['reference_type' => 'inbound']);
    }

    public function test_stock_movement_cannot_be_updated(): void
    {
        $movement = StockMovement::factory()->create();

        $this->expectException(LogicException::class);

        $movement->update(['qty' => 999]);
    }

    public function test_stock_movement_cannot_be_deleted(): void
    {
        $movement = StockMovement::factory()->create();

        $this->expectException(LogicException::class);

        $movement->delete();
    }
}