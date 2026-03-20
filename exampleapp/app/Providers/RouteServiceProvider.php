<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Boot the route service provider.
     */
    public function boot(): void
    {
        parent::boot();

        // Load web routes (required).
        Route::middleware('web')
            ->group(base_path('routes/web.php'));

        // Load API routes only if the file exists.
        if (File::exists(base_path('routes/api.php'))) {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
        }
    }
}

