<?php

namespace Support\ThirdPartyServices;

use Illuminate\Support\AggregateServiceProvider;
use Support\ThirdPartyServices\OpenExchangeRates\OpenExchangeRatesServiceProvider;

final class ThirdPartyAPIsServiceProvider extends AggregateServiceProvider
{
    /**
     * The provider class names.
     *
     * @var array<class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected $providers = [ // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
        OpenExchangeRatesServiceProvider::class,
    ];
}
