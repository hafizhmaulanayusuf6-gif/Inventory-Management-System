@extends('layouts.app')

@section('title', 'Barang Masuk')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0">Riwayat Barang Masuk</h5>

            <div class="d-flex flex-wrap gap-2">
                <form action="{{ route('inbounds.index') }}" method="GET" class="d-flex flex-wrap gap-2">
                    <input type="text" name="search" value="{{ $search }}" class="form-control" style="width: auto;"
                           placeholder="Cari no. referensi...">
                    <input type="date" name="from" value="{{ $from }}" class="form-control" style="width: auto;" title="Dari tanggal">
                    <input type="date" name="to" value="{{ $to }}" class="form-control" style="width: auto;" title="Sampai tanggal">
                    <button type="submit" class="btn btn-outline-primary"><i class="bx bx-search"></i></button>
                </form>

                @can('inbound.create')
                    <a href="{{ route('inbounds.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Barang Masuk Baru
                    </a>
                @endcan
            </div>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Tanggal</th>
                        <th>No. Referensi</th>
                        <th>Supplier</th>
                        <th class="text-end">Jenis Barang</th>
                        <th class="text-end">Total Qty</th>
                        <th>Petugas</th>
                        <th>Status</th>
                        <th style="width: 80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse ($inbounds as $inbound)
                        <tr>
                            <td>{{ $inbounds->firstItem() + $loop->index }}</td>
                            <td>{{ $inbound->date->format('d/m/Y') }}</td>
                            <td><code>{{ $inbound->reference_no }}</code></td>
                            <td class="fw-medium">{{ $inbound->supplier->name }}</td>
                            <td class="text-end">{{ $inbound->items_count }}</td>
                            <td class="text-end">{{ number_format($inbound->total_qty, 0, ',', '.') }}</td>
                            <td>{{ $inbound->user->name }}</td>
                            <td>
                                <span class="badge {{ $inbound->status->value === 'completed' ? 'bg-label-success' : 'bg-label-danger' }}">
                                    {{ $inbound->status->label() }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('inbounds.show', $inbound) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Detail">
                                    <i class="bx bx-show"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Belum ada data barang masuk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($inbounds->hasPages())
            <div class="card-footer">{{ $inbounds->links() }}</div>
        @endif
    </div>
@endsection