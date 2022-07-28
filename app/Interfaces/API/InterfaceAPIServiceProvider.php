<?php

namespace App\API;

use Illuminate\Support\AggregateServiceProvider;
use App\API\Auth\Providers\AuthInterfaceServiceProvider;
use App\API\Currency\Providers\CurrencyInterfaceServiceProvider;

/**
 * phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint
 */
final class InterfaceAPIServiceProvider extends AggregateServiceProvider
{
    /**
     * The provider class names.
     *
     * @var array<class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected $providers = [
        AuthInterfaceServiceProvider::class,
        // UserInterfaceServiceProvider::class,
        CurrencyInterfaceServiceProvider::class,
    ];
}
