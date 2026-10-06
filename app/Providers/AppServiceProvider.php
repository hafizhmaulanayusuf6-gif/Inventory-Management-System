<?php

namespace App\Providers;

use App\Models\Inbound;
use App\Models\Outbound;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Super Admin otomatis lolos semua pengecekan permission.
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        Relation::enforceMorphMap([
            'inbound' => Inbound::class,
            'outbound' => Outbound::class,
            'user' => \App\Models\User::class,
        ]);

        Paginator::useBootstrapFive();
    }
}