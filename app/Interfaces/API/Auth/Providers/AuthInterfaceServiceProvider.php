<?php

namespace App\API\Auth\Providers;

use Illuminate\Support\AggregateServiceProvider;

class AuthInterfaceServiceProvider extends AggregateServiceProvider
{
    /**
     * The provider class names.
     *
     * @var array<class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected $providers = [ // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
        RouteServiceProvider::class,
    ];
}
