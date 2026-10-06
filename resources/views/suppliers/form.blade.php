@extends('layouts.app')

@section('title', $supplier->exists ? 'Ubah Supplier' : 'Tambah Supplier')

@section('content')
<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <h5 class="card-header">{{ $supplier->exists ? 'Ubah Supplier' : 'Tambah Supplier' }}</h5>

            <div class="card-body">
                <form action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}"
                    method="POST">
                    @csrf
                    @if ($supplier->exists)
                    @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Supplier</label>
                        <input type="text" id="name" name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $supplier->name) }}" autofocus>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Telepon</label>
                        <input type="text" id="phone" name="phone"
                            class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone', $supplier->phone) }}">
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea id="address" name="address" rows="3"
                            class="form-control @error('address') is-invalid @enderror">{{ old('address', $supplier->address) }}</textarea>
                        @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary me-2">Simpan</button>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection