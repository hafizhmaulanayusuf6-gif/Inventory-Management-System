<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\Inbound;
use App\Models\InboundItem;
use App\Models\Outbound;
use App\Models\OutboundItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InboundService;
use App\Services\OutboundService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InboundOutboundTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function staff(): User
    {
        return $this->userWithRole('Staff Gudang');
    }

    private function inboundPayload(array $items, array $override = []): array
    {
        return array_merge([
            'date' => now()->toDateString(),
            'supplier_id' => Supplier::factory()->create()->id,
            'reference_no' => 'SJ-001',
            'items' => $items,
        ], $override);
    }

    private function outboundPayload(array $items, array $override = []): array
    {
        return array_merge([
            'date' => now()->toDateString(),
            'destination' => 'Toko Sumber Rejeki',
            'items' => $items,
        ], $override);
    }

    // ---------- Akses ----------

    public function test_guest_is_redirected_to_login_on_transaction_routes(): void
    {
        foreach (['inbounds.index', 'inbounds.create', 'outbounds.index', 'outbounds.create'] as $name) {
            $this->get(route($name))->assertRedirect(route('login'));
        }
    }

    // ---------- Inbound ----------

    public function test_staff_records_inbound_and_stock_increases(): void
    {
        $staff = $this->staff();
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();

        $payload = $this->inboundPayload([
            ['product_id' => $productA->id, 'qty' => 10],
            ['product_id' => $productB->id, 'qty' => 5],
        ]);

        $inbound = null;

        $this->actingAs($staff)
            ->post(route('inbounds.store'), $payload)
            ->assertRedirect();

        $inbound = Inbound::firstOrFail();

        $this->assertSame(10, $productA->fresh()->stock);
        $this->assertSame(5, $productB->fresh()->stock);
        $this->assertSame('SJ-001', $inbound->reference_no);
        $this->assertDatabaseCount('inbound_items', 2);
        $this->assertDatabaseCount('stock_movements', 2);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $productA->id,
            'type' => 'in',
            'qty' => 10,
            'balance' => 10,
            'reference_type' => 'inbound',
            'reference_id' => $inbound->id,
            'user_id' => $staff->id,
        ]);
    }

    public function test_inbound_reference_is_generated_when_blank(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->staff())
            ->post(route('inbounds.store'), $this->inboundPayload(
                [['product_id' => $product->id, 'qty' => 3]],
                ['reference_no' => null],
            ))
            ->assertRedirect();

        $this->assertStringStartsWith('IN-', Inbound::firstOrFail()->reference_no);
    }

    public function test_inbound_reference_number_must_be_unique(): void
    {
        Inbound::factory()->create(['reference_no' => 'SJ-001']);
        $product = Product::factory()->create();

        $this->actingAs($this->staff())
            ->post(route('inbounds.store'), $this->inboundPayload(
                [['product_id' => $product->id, 'qty' => 3]],
            ))
            ->assertSessionHasErrors('reference_no');

        $this->assertDatabaseCount('inbounds', 1);
        $this->assertSame(0, $product->fresh()->stock);
    }

    public function test_inbound_requires_at_least_one_item(): void
    {
        $this->actingAs($this->staff())
            ->post(route('inbounds.store'), $this->inboundPayload([]))
            ->assertSessionHasErrors('items');

        $this->assertDatabaseCount('inbounds', 0);
    }

    public function test_inbound_rejects_invalid_quantity_and_duplicate_product(): void
    {
        $staff = $this->staff();
        $product = Product::factory()->create();

        $this->actingAs($staff)
            ->post(route('inbounds.store'), $this->inboundPayload(
                [['product_id' => $product->id, 'qty' => 0]],
            ))
            ->assertSessionHasErrors('items.0.qty');

        $this->actingAs($staff)
            ->post(route('inbounds.store'), $this->inboundPayload([
                ['product_id' => $product->id, 'qty' => 2],
                ['product_id' => $product->id, 'qty' => 3],
            ]))
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('inbounds', 0);
        $this->assertSame(0, $product->fresh()->stock);
    }

    public function test_inbound_date_cannot_be_in_the_future(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->staff())
            ->post(route('inbounds.store'), $this->inboundPayload(
                [['product_id' => $product->id, 'qty' => 3]],
                ['date' => now()->addDay()->toDateString()],
            ))
            ->assertSessionHasErrors('date');

        $this->assertDatabaseCount('inbounds', 0);
    }

    public function test_kepala_gudang_can_view_inbound_but_not_create(): void
    {
        $kepala = $this->userWithRole('Kepala Gudang');
        $product = Product::factory()->create();

        $this->actingAs($kepala)->get(route('inbounds.index'))->assertOk();
        $this->actingAs($kepala)->get(route('inbounds.create'))->assertForbidden();
        $this->actingAs($kepala)
            ->post(route('inbounds.store'), $this->inboundPayload(
                [['product_id' => $product->id, 'qty' => 3]],
            ))
            ->assertForbidden();

        $this->assertDatabaseCount('inbounds', 0);
    }

    public function test_inbound_detail_page_shows_items(): void
    {
        $inbound = Inbound::factory()->create();
        $item = InboundItem::factory()->create(['inbound_id' => $inbound->id, 'qty' => 12]);

        $this->actingAs($this->staff())
            ->get(route('inbounds.show', $inbound))
            ->assertOk()
            ->assertSee($inbound->reference_no)
            ->assertSee($item->product->name);
    }

    public function test_inbound_service_merges_duplicate_product_lines(): void
    {
        $user = $this->staff();
        $product = Product::factory()->create();

        $inbound = app(InboundService::class)->create([
            'date' => now()->toDateString(),
            'supplier_id' => Supplier::factory()->create()->id,
            'reference_no' => 'SJ-MERGE',
            'items' => [
                ['product_id' => $product->id, 'qty' => 4],
                ['product_id' => $product->id, 'qty' => 6],
            ],
        ], $user->id);

        $this->assertCount(1, $inbound->items);
        $this->assertSame(10, $inbound->items->first()->qty);
        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame(1, StockMovement::count());
    }

    // ---------- Outbound ----------

    public function test_staff_records_outbound_and_stock_decreases(): void
    {
        $staff = $this->staff();
        $product = Product::factory()->withStock(20)->create();

        $this->actingAs($staff)
            ->post(route('outbounds.store'), $this->outboundPayload(
                [['product_id' => $product->id, 'qty' => 8]],
            ))
            ->assertRedirect();

        $outbound = Outbound::firstOrFail();

        $this->assertSame(12, $product->fresh()->stock);
        $this->assertDatabaseCount('outbound_items', 1);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'out',
            'qty' => 8,
            'balance' => 12,
            'reference_type' => 'outbound',
            'reference_id' => $outbound->id,
            'user_id' => $staff->id,
        ]);
    }

    public function test_outbound_with_insufficient_stock_is_rejected_and_nothing_changes(): void
    {
        $product = Product::factory()->withStock(5)->create();

        $this->actingAs($this->staff())
            ->post(route('outbounds.store'), $this->outboundPayload(
                [['product_id' => $product->id, 'qty' => 6]],
            ))
            ->assertSessionHasErrors('items.0.qty');

        $this->assertSame(5, $product->fresh()->stock);
        $this->assertDatabaseCount('outbounds', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_outbound_can_take_stock_down_to_exactly_zero(): void
    {
        $product = Product::factory()->withStock(5)->create();

        $this->actingAs($this->staff())
            ->post(route('outbounds.store'), $this->outboundPayload(
                [['product_id' => $product->id, 'qty' => 5]],
            ))
            ->assertRedirect();

        $this->assertSame(0, $product->fresh()->stock);
    }

    public function test_outbound_requires_destination(): void
    {
        $product = Product::factory()->withStock(5)->create();

        $this->actingAs($this->staff())
            ->post(route('outbounds.store'), $this->outboundPayload(
                [['product_id' => $product->id, 'qty' => 1]],
                ['destination' => ''],
            ))
            ->assertSessionHasErrors('destination');

        $this->assertDatabaseCount('outbounds', 0);
    }

    public function test_outbound_service_rolls_back_everything_when_a_later_item_fails(): void
    {
        $user = $this->staff();
        $productA = Product::factory()->withStock(10)->create();
        $productB = Product::factory()->withStock(2)->create();

        try {
            // Dipanggil langsung (tanpa validasi form), meniru kondisi stok
            // yang berubah setelah validasi lolos.
            app(OutboundService::class)->create([
                'date' => now()->toDateString(),
                'destination' => 'Toko X',
                'items' => [
                    ['product_id' => $productA->id, 'qty' => 5],
                    ['product_id' => $productB->id, 'qty' => 5],
                ],
            ], $user->id);

            $this->fail('Seharusnya melempar InsufficientStockException.');
        } catch (InsufficientStockException) {
            // diharapkan
        }

        $this->assertSame(10, $productA->fresh()->stock);
        $this->assertSame(2, $productB->fresh()->stock);
        $this->assertDatabaseCount('outbounds', 0);
        $this->assertDatabaseCount('outbound_items', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_controller_shows_friendly_error_when_service_reports_insufficient_stock(): void
    {
        $product = Product::factory()->withStock(10)->create();

        $this->mock(OutboundService::class, function ($mock) use ($product) {
            $mock->shouldReceive('create')
                ->once()
                ->andThrow(new InsufficientStockException($product, 5, 2));
        });

        $this->actingAs($this->staff())
            ->from(route('outbounds.create'))
            ->post(route('outbounds.store'), $this->outboundPayload(
                [['product_id' => $product->id, 'qty' => 5]],
            ))
            ->assertRedirect(route('outbounds.create'))
            ->assertSessionHas('error');
    }

    public function test_kepala_gudang_can_view_outbound_but_not_create(): void
    {
        $kepala = $this->userWithRole('Kepala Gudang');
        $product = Product::factory()->withStock(5)->create();

        $this->actingAs($kepala)->get(route('outbounds.index'))->assertOk();
        $this->actingAs($kepala)->get(route('outbounds.create'))->assertForbidden();
        $this->actingAs($kepala)
            ->post(route('outbounds.store'), $this->outboundPayload(
                [['product_id' => $product->id, 'qty' => 1]],
            ))
            ->assertForbidden();

        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_outbound_detail_page_shows_items(): void
    {
        $outbound = Outbound::factory()->create(['destination' => 'Toko Maju']);
        $item = OutboundItem::factory()->create(['outbound_id' => $outbound->id]);

        $this->actingAs($this->staff())
            ->get(route('outbounds.show', $outbound))
            ->assertOk()
            ->assertSee('Toko Maju')
            ->assertSee($item->product->name);
    }

    // ---------- Alur gabungan ----------

    public function test_inbound_then_outbound_keeps_stock_card_balance_consistent(): void
    {
        $staff = $this->staff();
        $product = Product::factory()->create();

        $this->actingAs($staff)->post(route('inbounds.store'), $this->inboundPayload(
            [['product_id' => $product->id, 'qty' => 10]],
        ));

        $this->actingAs($staff)->post(route('outbounds.store'), $this->outboundPayload(
            [['product_id' => $product->id, 'qty' => 6]],
        ));

        $movements = StockMovement::where('product_id', $product->id)->orderBy('id')->get();

        $this->assertEquals([10, 4], $movements->pluck('balance')->all());
        $this->assertSame(4, $product->fresh()->stock);
    }
}