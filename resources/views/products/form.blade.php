@extends('layouts.app')

@section('title', $product->exists ? 'Ubah Barang' : 'Tambah Barang')

@section('content')
<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <h5 class="card-header">{{ $product->exists ? 'Ubah Barang' : 'Tambah Barang' }}</h5>

            <div class="card-body">
                @if ($product->exists)
                <div class="alert alert-info" role="alert">
                    Stok saat ini: <strong>{{ number_format($product->stock, 0, ',', '.') }}</strong>.
                    Stok tidak bisa diubah dari sini, hanya lewat Barang Masuk dan Barang Keluar.
                </div>
                @else
                <div class="alert alert-info" role="alert">
                    Stok awal barang selalu <strong>0</strong> dan hanya bertambah lewat Barang Masuk.
                </div>
                @endif

                <form action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}"
                    method="POST">
                    @csrf
                    @if ($product->exists)
                    @method('PUT')
                    @endif

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="sku" class="form-label">SKU</label>
                            <input type="text" id="sku" name="sku"
                                class="form-control @error('sku') is-invalid @enderror"
                                value="{{ old('sku', $product->sku) }}" autofocus>
                            @error('sku')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-8 mb-3">
                            <label for="name" class="form-label">Nama Barang</label>
                            <input type="text" id="name" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $product->name) }}">
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Kategori</label>
                            <select id="category_id" name="category_id"
                                class="form-select @error('category_id') is-invalid @enderror">
                                <option value="">-- Pilih kategori --</option>
                                @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    @selected((int) old('category_id', $product->category_id) === $category->id)>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="unit_id" class="form-label">Satuan</label>
                            <select id="unit_id" name="unit_id"
                                class="form-select @error('unit_id') is-invalid @enderror">
                                <option value="">-- Pilih satuan --</option>
                                @foreach ($units as $unit)
                                <option value="{{ $unit->id }}"
                                    @selected((int) old('unit_id', $product->unit_id) === $unit->id)>
                                    {{ $unit->name }} ({{ $unit->symbol }})
                                </option>
                                @endforeach
                            </select>
                            @error('unit_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="min_stock" class="form-label">Stok Minimum</label>
                            <input type="number" id="min_stock" name="min_stock" min="0" step="1"
                                class="form-control @error('min_stock') is-invalid @enderror"
                                value="{{ old('min_stock', $product->min_stock ?? 0) }}">
                            @error('min_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Barang ditandai merah jika stok sama dengan atau di bawah angka ini.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Harga (Rp)</label>
                            <input type="number" id="price" name="price" min="0" step="0.01"
                                class="form-control @error('price') is-invalid @enderror"
                                value="{{ old('price', $product->price ?? 0) }}">
                            @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary me-2">Simpan</button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection