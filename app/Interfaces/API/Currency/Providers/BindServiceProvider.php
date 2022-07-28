<?php

namespace App\API\Currency\Providers;

use Illuminate\Support\ServiceProvider;

class BindServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerMiddlewares();

        $this->loadViewsFrom(__DIR__ . '/../Mail/Views', 'currency-interface');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerServices();
    }

    /**
     * Register any domain services.
     *
     * @return void
     */
    public function registerServices(): void
    {
        // $this->app->bind(
        //     Contracts\CurrencyService::class,
        //     Services\CurrencyService::class
        // );
    }

    /**
     * Register any middlewares services.
     *
     * @return void
     */
    private function registerMiddlewares(): void
    {
        // @see https://laravelpackage.com/11-middleware.html
        // $this->app->router->aliasMiddleware('middlewareName', MiddlewareClass::class);
    }
}
