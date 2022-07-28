<?php

namespace App\API\Currency\Http\Routes;

use Illuminate\Routing\Router;
use Support\Http\Routing\RouteFile;
use Illuminate\Support\Facades\Route;
use App\API\Currency\Http\Controllers\ConvertMoney;

class Api extends RouteFile
{
    protected function routes(Router $router): void
    {
        $router->prefix('currency')->group(function () {
            Route::get('convert', ConvertMoney::class)->name('convert');
        });
    }
}
