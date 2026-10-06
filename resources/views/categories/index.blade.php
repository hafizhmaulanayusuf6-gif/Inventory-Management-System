@extends('layouts.app')

@section('title', 'Kategori')

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0">Daftar Kategori</h5>

        <div class="d-flex flex-wrap gap-2">
            <form action="{{ route('categories.index') }}" method="GET" class="d-flex gap-2">
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari kategori...">
                <button type="submit" class="btn btn-outline-primary"><i class="bx bx-search"></i></button>
            </form>

            @can('category.create')
            <a href="{{ route('categories.create') }}" class="btn btn-primary">
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
                    <th>Nama</th>
                    <th>Jumlah Barang</th>
                    <th style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($categories as $category)
                <tr>
                    <td>{{ $categories->firstItem() + $loop->index }}</td>
                    <td class="fw-medium">{{ $category->name }}</td>
                    <td><span class="badge bg-label-primary">{{ $category->products_count }}</span></td>
                    <td>
                        @can('category.update')
                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-icon btn-outline-warning" title="Ubah">
                            <i class="bx bx-edit-alt"></i>
                        </a>
                        @endcan

                        @can('category.delete')
                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('Hapus kategori {{ $category->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus">
                                <i class="bx bx-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">Belum ada data kategori.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($categories->hasPages())
    <div class="card-footer">{{ $categories->links() }}</div>
    @endif
</div>
@endsection