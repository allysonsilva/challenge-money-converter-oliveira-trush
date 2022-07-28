<?php

namespace App\API\Currency\Providers;

use Illuminate\Support\AggregateServiceProvider;

class CurrencyInterfaceServiceProvider extends AggregateServiceProvider
{
    /**
     * The provider class names.
     *
     * @var array<class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected $providers = [ // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
        BindServiceProvider::class,
        RouteServiceProvider::class,
    ];
}
