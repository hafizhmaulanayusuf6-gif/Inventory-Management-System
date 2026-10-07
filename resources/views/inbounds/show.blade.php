@extends('layouts.app')

@section('title', 'Detail Barang Masuk')

@section('content')
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Barang Masuk <code>{{ $inbound->reference_no }}</code></h5>
            <span class="badge {{ $inbound->status->value === 'completed' ? 'bg-label-success' : 'bg-label-danger' }}">
                {{ $inbound->status->label() }}
            </span>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <small class="text-muted d-block">Tanggal</small>
                    <span class="fw-medium">{{ $inbound->date->format('d/m/Y') }}</span>
                </div>
                <div class="col-md-3 mb-2">
                    <small class="text-muted d-block">Supplier</small>
                    <span class="fw-medium">{{ $inbound->supplier->name }}</span>
                </div>
                <div class="col-md-3 mb-2">
                    <small class="text-muted d-block">Petugas</small>
                    <span class="fw-medium">{{ $inbound->user->name }}</span>
                </div>
                <div class="col-md-3 mb-2">
                    <small class="text-muted d-block">Dicatat pada</small>
                    <span class="fw-medium">{{ $inbound->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <h5 class="card-header">Barang</h5>

        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>SKU</th>
                        <th>Nama Barang</th>
                        <th class="text-end">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($inbound->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code>{{ $item->product->sku }}</code></td>
                            <td class="fw-medium">{{ $item->product->name }}</td>
                            <td class="text-end">
                                {{ number_format($item->qty, 0, ',', '.') }} {{ $item->product->unit->symbol }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total</th>
                        <th class="text-end">{{ number_format($inbound->items->sum('qty'), 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="card-footer">
            <a href="{{ route('inbounds.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>
@endsection