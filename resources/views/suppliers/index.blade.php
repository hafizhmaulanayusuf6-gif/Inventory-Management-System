@extends('layouts.app')

@section('title', 'Supplier')

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0">Daftar Supplier</h5>

        <div class="d-flex flex-wrap gap-2">
            <form action="{{ route('suppliers.index') }}" method="GET" class="d-flex gap-2">
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama / telepon...">
                <button type="submit" class="btn btn-outline-primary"><i class="bx bx-search"></i></button>
            </form>

            @can('supplier.create')
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
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
                    <th>Telepon</th>
                    <th>Alamat</th>
                    <th>Transaksi</th>
                    <th style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($suppliers as $supplier)
                <tr>
                    <td>{{ $suppliers->firstItem() + $loop->index }}</td>
                    <td class="fw-medium">{{ $supplier->name }}</td>
                    <td>{{ $supplier->phone ?: '-' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($supplier->address, 40) ?: '-' }}</td>
                    <td><span class="badge bg-label-primary">{{ $supplier->inbounds_count }}</span></td>
                    <td>
                        @can('supplier.update')
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-icon btn-outline-warning" title="Ubah">
                            <i class="bx bx-edit-alt"></i>
                        </a>
                        @endcan

                        @can('supplier.delete')
                        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('Hapus supplier {{ $supplier->name }}?')">
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
                    <td colspan="6" class="text-center text-muted py-4">Belum ada data supplier.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($suppliers->hasPages())
    <div class="card-footer">{{ $suppliers->links() }}</div>
    @endif
</div>
@endsection