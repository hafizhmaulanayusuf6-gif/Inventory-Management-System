@extends('layouts.app')

@section('title', 'Satuan')

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0">Daftar Satuan</h5>

        <div class="d-flex flex-wrap gap-2">
            <form action="{{ route('units.index') }}" method="GET" class="d-flex gap-2">
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari satuan...">
                <button type="submit" class="btn btn-outline-primary"><i class="bx bx-search"></i></button>
            </form>

            @can('unit.create')
            <a href="{{ route('units.create') }}" class="btn btn-primary">
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
                    <th>Simbol</th>
                    <th>Jumlah Barang</th>
                    <th style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($units as $unit)
                <tr>
                    <td>{{ $units->firstItem() + $loop->index }}</td>
                    <td class="fw-medium">{{ $unit->name }}</td>
                    <td><span class="badge bg-label-secondary">{{ $unit->symbol }}</span></td>
                    <td><span class="badge bg-label-primary">{{ $unit->products_count }}</span></td>
                    <td>
                        @can('unit.update')
                        <a href="{{ route('units.edit', $unit) }}" class="btn btn-sm btn-icon btn-outline-warning" title="Ubah">
                            <i class="bx bx-edit-alt"></i>
                        </a>
                        @endcan

                        @can('unit.delete')
                        <form action="{{ route('units.destroy', $unit) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('Hapus satuan {{ $unit->name }}?')">
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
                    <td colspan="5" class="text-center text-muted py-4">Belum ada data satuan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($units->hasPages())
    <div class="card-footer">{{ $units->links() }}</div>
    @endif
</div>
@endsection