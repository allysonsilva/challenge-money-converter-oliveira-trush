<?php

namespace App;

use Core\ConsoleKernel;
use App\API\InterfaceAPIServiceProvider;
use Illuminate\Support\AggregateServiceProvider;

/**
 * phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint
 */
final class InterfacesServiceProvider extends AggregateServiceProvider
{
    /**
     * The provider class names.
     *
     * @var array<class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected $providers = [
        InterfaceAPIServiceProvider::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        app(ConsoleKernel::class)->loadCommandsFromPaths(
            __DIR__ . DIRECTORY_SEPARATOR . ('Console' . DIRECTORY_SEPARATOR . 'Commands')
        );
    }
}
