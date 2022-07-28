<?php

namespace App\API\Currency\Providers;

use App\API\Currency\Http\Routes\Api;
use Illuminate\Support\Facades\Route;
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
            Route::prefix('api/v1')->name('api.v1.')->group(function () {
                (new Api())
                    ->middleware(['api'])
                    ->name('currency.')
                    ->registerRouteGroups();
            });
        });
    }
}
