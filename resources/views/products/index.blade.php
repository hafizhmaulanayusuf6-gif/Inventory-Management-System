@extends('layouts.app')

@section('title', 'Barang')

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0">Daftar Barang</h5>

        <div class="d-flex flex-wrap gap-2">
            <form action="{{ route('products.index') }}" method="GET" class="d-flex flex-wrap gap-2">
                <select name="category_id" class="form-select" style="width: auto;">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($categoryId===$category->id)>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
                <input type="text" name="search" value="{{ $search }}" class="form-control" style="width: auto;"
                    placeholder="Cari nama / SKU...">
                <button type="submit" class="btn btn-outline-primary"><i class="bx bx-search"></i></button>
            </form>

            @can('product.create')
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Tambah
            </a>
            @endcan
        </div>
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>SKU</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th class="text-end">Stok</th>
                    <th class="text-end">Min. Stok</th>
                    <th class="text-end">Harga</th>
                    @canany(['product.update', 'product.delete'])
                    <th style="width: 140px;">Aksi</th>
                    @endcanany
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($products as $product)
                <tr>
                    <td>{{ $products->firstItem() + $loop->index }}</td>
                    <td><code>{{ $product->sku }}</code></td>
                    <td class="fw-medium">{{ $product->name }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td class="text-end">
                        <span class="badge {{ $product->isLowStock() ? 'bg-label-danger' : 'bg-label-success' }}">
                            {{ number_format($product->stock, 0, ',', '.') }} {{ $product->unit->symbol }}
                        </span>
                    </td>
                    <td class="text-end">{{ number_format($product->min_stock, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                    @canany(['product.update', 'product.delete'])
                    <td>
                        @can('product.update')
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-icon btn-outline-warning" title="Ubah">
                            <i class="bx bx-edit-alt"></i>
                        </a>
                        @endcan

                        @can('product.delete')
                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('Hapus barang {{ $product->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus">
                                <i class="bx bx-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </td>
                    @endcanany
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">Belum ada data barang.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($products->hasPages())
    <div class="card-footer">{{ $products->links() }}</div>
    @endif
</div>
@endsection