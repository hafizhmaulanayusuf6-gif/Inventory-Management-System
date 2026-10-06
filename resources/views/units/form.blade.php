@extends('layouts.app')

@section('title', $unit->exists ? 'Ubah Satuan' : 'Tambah Satuan')

@section('content')
<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <h5 class="card-header">{{ $unit->exists ? 'Ubah Satuan' : 'Tambah Satuan' }}</h5>

            <div class="card-body">
                <form action="{{ $unit->exists ? route('units.update', $unit) : route('units.store') }}" method="POST">
                    @csrf
                    @if ($unit->exists)
                    @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Satuan</label>
                        <input type="text" id="name" name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $unit->name) }}" placeholder="Contoh: Kilogram" autofocus>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="symbol" class="form-label">Simbol</label>
                        <input type="text" id="symbol" name="symbol"
                            class="form-control @error('symbol') is-invalid @enderror"
                            value="{{ old('symbol', $unit->symbol) }}" placeholder="Contoh: kg">
                        @error('symbol')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary me-2">Simpan</button>
                    <a href="{{ route('units.index') }}" class="btn btn-outline-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection