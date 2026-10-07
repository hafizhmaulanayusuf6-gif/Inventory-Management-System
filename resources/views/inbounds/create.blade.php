@extends('layouts.app')

@section('title', 'Barang Masuk Baru')

@section('content')
    <form action="{{ route('inbounds.store') }}" method="POST" id="transaction-form">
        @csrf

        <div class="card mb-4">
            <h5 class="card-header">Barang Masuk Baru</h5>

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

                    <div class="col-md-4 mb-3">
                        <label for="supplier_id" class="form-label">Supplier</label>
                        <select id="supplier_id" name="supplier_id"
                                class="form-select @error('supplier_id') is-invalid @enderror">
                            <option value="">-- Pilih supplier --</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected((int) old('supplier_id') === $supplier->id)>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="reference_no" class="form-label">No. Referensi (opsional)</label>
                        <input type="text" id="reference_no" name="reference_no"
                               class="form-control @error('reference_no') is-invalid @enderror"
                               value="{{ old('reference_no') }}" placeholder="No. surat jalan / PO">
                        @error('reference_no')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Kosongkan untuk dibuat otomatis.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <h5 class="card-header">Daftar Barang</h5>

            @error('items')
                <div class="alert alert-danger mx-4">{{ $message }}</div>
            @enderror

            @include('partials.item-rows', [
                'products' => $products,
                'rows' => old('items', []),
                'checkStock' => false,
            ])

            <div class="card-footer">
                <button type="submit" class="btn btn-primary me-2">Simpan</button>
                <a href="{{ route('inbounds.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </div>
    </form>
@endsection