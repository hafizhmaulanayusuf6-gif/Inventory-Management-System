<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inbound;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
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

    private function admin(): User
    {
        return $this->userWithRole('Super Admin');
    }

    private function productPayload(array $override = []): array
    {
        return array_merge([
            'sku' => 'SKU-100',
            'name' => 'Kertas A4',
            'category_id' => Category::factory()->create()->id,
            'unit_id' => Unit::factory()->create()->id,
            'min_stock' => 5,
            'price' => 55000,
        ], $override);
    }

    // ---------- Akses umum ----------

    public function test_guest_is_redirected_to_login_on_master_data_routes(): void
    {
        foreach (['categories', 'units', 'suppliers', 'products'] as $name) {
            $this->get(route("{$name}.index"))->assertRedirect(route('login'));
        }
    }

    // ---------- Kategori ----------

    public function test_super_admin_can_create_category(): void
    {
        $this->actingAs($this->admin())
            ->post(route('categories.store'), ['name' => 'Elektronik'])
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', ['name' => 'Elektronik']);
    }

    public function test_category_name_must_be_unique(): void
    {
        Category::factory()->create(['name' => 'Elektronik']);

        $this->actingAs($this->admin())
            ->post(route('categories.store'), ['name' => 'Elektronik'])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('categories', 1);
    }

    public function test_category_in_use_cannot_be_deleted(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('categories.destroy', $product->category))
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $product->category_id]);
    }

    public function test_unused_category_can_be_deleted(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_staff_cannot_access_categories(): void
    {
        $this->actingAs($this->userWithRole('Staff Gudang'))
            ->get(route('categories.index'))
            ->assertForbidden();
    }

    public function test_kepala_gudang_can_view_but_not_create_category(): void
    {
        $kepala = $this->userWithRole('Kepala Gudang');

        $this->actingAs($kepala)->get(route('categories.index'))->assertOk();

        $this->actingAs($kepala)
            ->post(route('categories.store'), ['name' => 'Baru'])
            ->assertForbidden();

        $this->assertDatabaseMissing('categories', ['name' => 'Baru']);
    }

    // ---------- Satuan ----------

    public function test_unit_requires_name_and_symbol(): void
    {
        $this->actingAs($this->admin())
            ->post(route('units.store'), ['name' => '', 'symbol' => ''])
            ->assertSessionHasErrors(['name', 'symbol']);

        $this->actingAs($this->admin())
            ->post(route('units.store'), ['name' => 'Kilogram', 'symbol' => 'kg'])
            ->assertRedirect(route('units.index'));

        $this->assertDatabaseHas('units', ['name' => 'Kilogram', 'symbol' => 'kg']);
    }

    // ---------- Supplier ----------

    public function test_kepala_gudang_can_manage_supplier(): void
    {
        $kepala = $this->userWithRole('Kepala Gudang');

        $this->actingAs($kepala)
            ->post(route('suppliers.store'), [
                'name' => 'PT Maju Jaya',
                'phone' => '0812345678',
                'address' => 'Bandung',
            ])
            ->assertRedirect(route('suppliers.index'));

        $supplier = Supplier::where('name', 'PT Maju Jaya')->firstOrFail();

        $this->actingAs($kepala)
            ->put(route('suppliers.update', $supplier), ['name' => 'PT Maju Mundur'])
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'name' => 'PT Maju Mundur']);

        $this->actingAs($kepala)
            ->delete(route('suppliers.destroy', $supplier))
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }

    public function test_staff_cannot_access_supplier(): void
    {
        $this->actingAs($this->userWithRole('Staff Gudang'))
            ->get(route('suppliers.index'))
            ->assertForbidden();
    }

    public function test_supplier_with_inbound_cannot_be_deleted(): void
    {
        $inbound = Inbound::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('suppliers.destroy', $inbound->supplier))
            ->assertRedirect(route('suppliers.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('suppliers', ['id' => $inbound->supplier_id]);
    }

    // ---------- Barang ----------

    public function test_admin_creates_product_with_zero_stock_even_if_stock_is_sent(): void
    {
        $payload = $this->productPayload(['stock' => 999]);

        $this->actingAs($this->admin())
            ->post(route('products.store'), $payload)
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', ['sku' => 'SKU-100', 'stock' => 0]);
    }

    public function test_sku_is_normalized_to_uppercase_and_must_be_unique(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('products.store'), $this->productPayload(['sku' => ' abc-1 ']))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', ['sku' => 'ABC-1']);

        $this->actingAs($admin)
            ->post(route('products.store'), $this->productPayload(['sku' => 'abc-1']))
            ->assertSessionHasErrors('sku');

        $this->assertDatabaseCount('products', 1);
    }

    public function test_staff_can_view_products_but_not_create(): void
    {
        $staff = $this->userWithRole('Staff Gudang');

        $this->actingAs($staff)->get(route('products.index'))->assertOk();
        $this->actingAs($staff)->get(route('products.create'))->assertForbidden();
        $this->actingAs($staff)
            ->post(route('products.store'), $this->productPayload())
            ->assertForbidden();

        $this->assertDatabaseCount('products', 0);
    }

    public function test_kepala_gudang_cannot_create_product(): void
    {
        $this->actingAs($this->userWithRole('Kepala Gudang'))
            ->post(route('products.store'), $this->productPayload())
            ->assertForbidden();
    }

    public function test_update_product_does_not_change_stock(): void
    {
        $product = Product::factory()->withStock(10)->create();

        $this->actingAs($this->admin())
            ->put(route('products.update', $product), [
                'sku' => $product->sku,
                'name' => 'Nama Baru',
                'category_id' => $product->category_id,
                'unit_id' => $product->unit_id,
                'min_stock' => 3,
                'price' => 1000,
                'stock' => 999, // harus diabaikan
            ])
            ->assertRedirect(route('products.index'));

        $product->refresh();

        $this->assertSame('Nama Baru', $product->name);
        $this->assertSame(10, $product->stock);
    }

    public function test_product_with_stock_cannot_be_deleted(): void
    {
        $product = Product::factory()->withStock(5)->create();

        $this->actingAs($this->admin())
            ->delete(route('products.destroy', $product))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_product_with_stock_movement_history_cannot_be_deleted(): void
    {
        $product = Product::factory()->create(); // stok 0
        StockMovement::factory()->create(['product_id' => $product->id]);

        $this->actingAs($this->admin())
            ->delete(route('products.destroy', $product))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_unused_product_can_be_deleted(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}