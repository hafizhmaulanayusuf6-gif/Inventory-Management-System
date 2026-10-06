@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-primary">Halo, {{ auth()->user()->name }}!</h5>
                    <p class="mb-0">
                        Selamat datang di Sistem Manajemen Gudang.
                        Role Anda: <strong>{{ auth()->user()->getRoleNames()->first() ?? 'Belum ada role' }}</strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection