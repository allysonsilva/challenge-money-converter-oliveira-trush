<?php

namespace App\API\Auth\Providers;

use App\API\Auth\Http\Routes\Api;
use Illuminate\Support\Facades\Route;
use Core\Auth\Middleware\BlockIfNotSameDomain;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->routes(function () {
            Route::prefix('api/v1/auth')->name('api.v1.auth.')->group(function () {
                (new Api())
                    ->middleware(['api', BlockIfNotSameDomain::class])
                    ->registerRouteGroups();
            });
        });
    }
}
