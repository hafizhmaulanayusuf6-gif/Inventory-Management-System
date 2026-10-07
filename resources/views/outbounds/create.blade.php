@extends('layouts.app')

@section('title', 'Barang Keluar Baru')

@section('content')
    <form action="{{ route('outbounds.store') }}" method="POST" id="transaction-form">
        @csrf

        <div class="card mb-4">
            <h5 class="card-header">Barang Keluar Baru</h5>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="date" class="form-label">Tanggal</label>
                        <input type="date" id="date" name="date"
                               class="form-control @error('date') is-invalid @enderror"
                               value="{{ old('date', now()->toDateString()) }}" max="{{ now()->toDateString() }}">
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-8 mb-3">
                        <label for="destination" class="form-label">Tujuan Pengiriman</label>
                        <input type="text" id="destination" name="destination"
                               class="form-control @error('destination') is-invalid @enderror"
                               value="{{ old('destination') }}" placeholder="Nama customer / toko / cabang">
                        @error('destination')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <h5 class="card-header">Daftar Barang</h5>

            @error('items')
                <div class="alert alert-danger mx-4">{{ $message }}</div>
            @enderror

            @if ($products->isEmpty())
                <div class="alert alert-warning mx-4" role="alert">
                    Belum ada barang yang memiliki stok. Catat Barang Masuk terlebih dahulu.
                </div>
            @endif

            @include('partials.item-rows', [
                'products' => $products,
                'rows' => old('items', []),
                'checkStock' => true,
            ])

            <div class="card-footer">
                <button type="submit" class="btn btn-primary me-2">Simpan</button>
                <a href="{{ route('outbounds.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </div>
    </form>
@endsection