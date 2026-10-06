@php
    // Pakai '#' jika route belum dibuat. Link otomatis aktif saat modulnya selesai.
    $link = fn (string $name) => \Illuminate\Support\Facades\Route::has($name) ? route($name) : '#';

    $masterDataOpen = request()->routeIs('categories.*', 'units.*', 'suppliers.*', 'products.*');
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <i class="bx bx-package bx-md text-primary"></i>
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-2">Gudang</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">

        {{-- Dashboard: semua user yang login --}}
        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div>Dashboard</div>
            </a>
        </li>

        {{-- Master Data --}}
        @canany(['category.view', 'unit.view', 'supplier.view', 'product.view'])
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Master Data</span>
            </li>

            @can('product.view')
                <li class="menu-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <a href="{{ $link('products.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-box"></i>
                        <div>Barang</div>
                    </a>
                </li>
            @endcan

            @can('category.view')
                <li class="menu-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <a href="{{ $link('categories.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-category"></i>
                        <div>Kategori</div>
                    </a>
                </li>
            @endcan

            @can('unit.view')
                <li class="menu-item {{ request()->routeIs('units.*') ? 'active' : '' }}">
                    <a href="{{ $link('units.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-ruler"></i>
                        <div>Satuan</div>
                    </a>
                </li>
            @endcan

            @can('supplier.view')
                <li class="menu-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                    <a href="{{ $link('suppliers.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-store"></i>
                        <div>Supplier</div>
                    </a>
                </li>
            @endcan
        @endcanany

        {{-- Transaksi --}}
        @canany(['inbound.view', 'outbound.view', 'stock-movement.view'])
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Transaksi</span>
            </li>

            @can('inbound.view')
                <li class="menu-item {{ request()->routeIs('inbounds.*') ? 'active' : '' }}">
                    <a href="{{ $link('inbounds.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-log-in-circle"></i>
                        <div>Barang Masuk</div>
                    </a>
                </li>
            @endcan

            @can('outbound.view')
                <li class="menu-item {{ request()->routeIs('outbounds.*') ? 'active' : '' }}">
                    <a href="{{ $link('outbounds.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-log-out-circle"></i>
                        <div>Barang Keluar</div>
                    </a>
                </li>
            @endcan

            @can('stock-movement.view')
                <li class="menu-item {{ request()->routeIs('stock-movements.*') ? 'active' : '' }}">
                    <a href="{{ $link('stock-movements.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-transfer"></i>
                        <div>Kartu Stok</div>
                    </a>
                </li>
            @endcan
        @endcanany

        {{-- Administrasi --}}
        @canany(['user.manage', 'role.manage'])
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Administrasi</span>
            </li>

            @can('user.manage')
                <li class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <a href="{{ $link('users.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-user"></i>
                        <div>Pengguna</div>
                    </a>
                </li>
            @endcan
        @endcanany

    </ul>
</aside>