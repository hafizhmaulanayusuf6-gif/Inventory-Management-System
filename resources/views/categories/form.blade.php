@extends('layouts.app')

@section('title', $category->exists ? 'Ubah Kategori' : 'Tambah Kategori')

@section('content')
<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <h5 class="card-header">{{ $category->exists ? 'Ubah Kategori' : 'Tambah Kategori' }}</h5>

            <div class="card-body">
                <form action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}"
                    method="POST">
                    @csrf
                    @if ($category->exists)
                    @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Kategori</label>
                        <input type="text" id="name" name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $category->name) }}" autofocus>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary me-2">Simpan</button>
                    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection