<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        // Route sementara untuk menguji middleware permission.
        Route::middleware(['web', 'auth', 'permission:supplier.create'])
            ->get('/_test/supplier-create', fn () => 'ok');

        Route::middleware(['web', 'auth', 'permission:inbound.create'])
            ->get('/_test/inbound-create', fn () => 'ok');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_three_roles_are_seeded(): void
    {
        foreach (['Super Admin', 'Kepala Gudang', 'Staff Gudang'] as $role) {
            $this->assertDatabaseHas('roles', ['name' => $role]);
        }
    }

    public function test_super_admin_passes_every_permission_check(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->assertTrue($admin->can('product.create'));
        $this->assertTrue($admin->can('role.manage'));
        $this->assertTrue($admin->can('inbound.create'));
    }

    public function test_kepala_gudang_can_manage_supplier_but_not_products(): void
    {
        $kepala = $this->userWithRole('Kepala Gudang');

        $this->assertTrue($kepala->can('supplier.create'));
        $this->assertTrue($kepala->can('stock-adjustment.approve'));
        $this->assertTrue($kepala->can('report.view'));
        $this->assertFalse($kepala->can('product.create'));
        $this->assertFalse($kepala->can('inbound.create'));
    }

    public function test_staff_gudang_only_records_transactions_and_views_stock(): void
    {
        $staff = $this->userWithRole('Staff Gudang');

        $this->assertTrue($staff->can('inbound.create'));
        $this->assertTrue($staff->can('outbound.create'));
        $this->assertTrue($staff->can('product.view'));
        $this->assertFalse($staff->can('product.create'));
        $this->assertFalse($staff->can('supplier.create'));
        $this->assertFalse($staff->can('stock-movement.view'));
    }

    public function test_permission_middleware_blocks_staff_from_supplier_route(): void
    {
        $this->actingAs($this->userWithRole('Staff Gudang'))
            ->get('/_test/supplier-create')
            ->assertForbidden();
    }

    public function test_permission_middleware_allows_kepala_gudang_and_super_admin(): void
    {
        $this->actingAs($this->userWithRole('Kepala Gudang'))
            ->get('/_test/supplier-create')
            ->assertOk();

        $this->actingAs($this->userWithRole('Super Admin'))
            ->get('/_test/supplier-create')
            ->assertOk();
    }

    public function test_permission_middleware_allows_staff_on_inbound_route(): void
    {
        $this->actingAs($this->userWithRole('Staff Gudang'))
            ->get('/_test/inbound-create')
            ->assertOk();
    }

    public function test_sidebar_only_shows_menus_allowed_for_role(): void
    {
        $this->actingAs($this->userWithRole('Staff Gudang'))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Barang Masuk')
            ->assertSee('Barang Keluar')
            ->assertDontSee('Supplier')
            ->assertDontSee('Kartu Stok')
            ->assertDontSee('Pengguna');
    }
}