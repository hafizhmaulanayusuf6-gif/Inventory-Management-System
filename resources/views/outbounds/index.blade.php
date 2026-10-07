@extends('layouts.app')

@section('title', 'Barang Keluar')

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
            <h5 class="mb-0">Riwayat Barang Keluar</h5>

            <div class="d-flex flex-wrap gap-2">
                <form action="{{ route('outbounds.index') }}" method="GET" class="d-flex flex-wrap gap-2">
                    <input type="text" name="search" value="{{ $search }}" class="form-control" style="width: auto;"
                           placeholder="Cari tujuan...">
                    <input type="date" name="from" value="{{ $from }}" class="form-control" style="width: auto;" title="Dari tanggal">
                    <input type="date" name="to" value="{{ $to }}" class="form-control" style="width: auto;" title="Sampai tanggal">
                    <button type="submit" class="btn btn-outline-primary"><i class="bx bx-search"></i></button>
                </form>

                @can('outbound.create')
                    <a href="{{ route('outbounds.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Barang Keluar Baru
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
                        <th>No. Dokumen</th>
                        <th>Tujuan</th>
                        <th class="text-end">Jenis Barang</th>
                        <th class="text-end">Total Qty</th>
                        <th>Petugas</th>
                        <th>Status</th>
                        <th style="width: 80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse ($outbounds as $outbound)
                        <tr>
                            <td>{{ $outbounds->firstItem() + $loop->index }}</td>
                            <td>{{ $outbound->date->format('d/m/Y') }}</td>
                            <td><code>{{ sprintf('OUT-%06d', $outbound->id) }}</code></td>
                            <td class="fw-medium">{{ $outbound->destination }}</td>
                            <td class="text-end">{{ $outbound->items_count }}</td>
                            <td class="text-end">{{ number_format($outbound->total_qty, 0, ',', '.') }}</td>
                            <td>{{ $outbound->user->name }}</td>
                            <td>
                                <span class="badge {{ $outbound->status->value === 'completed' ? 'bg-label-success' : 'bg-label-danger' }}">
                                    {{ $outbound->status->label() }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('outbounds.show', $outbound) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Detail">
                                    <i class="bx bx-show"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Belum ada data barang keluar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($outbounds->hasPages())
            <div class="card-footer">{{ $outbounds->links() }}</div>
        @endif
    </div>
@endsection