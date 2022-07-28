<?php

namespace App\API\Auth\Http\Routes;

use Illuminate\Routing\Router;
use Support\Http\Routing\RouteFile;
use Illuminate\Support\Facades\Route;
use App\API\Auth\Http\Controllers\LoginController;
use App\API\Auth\Http\Controllers\LogoutController;
use App\API\Auth\Http\Controllers\RefreshController;

class Api extends RouteFile
{
    protected function routes(Router $router): void
    {
        $router->post('login', LoginController::class)
               ->name('login')
               ->middleware('guest');

        $router->name('logged.')->middleware('auth')->group(function () {
            Route::put('refresh', RefreshController::class)->name('refresh');

            Route::delete('logout', LogoutController::class)->name('logout');
        });
    }
}
