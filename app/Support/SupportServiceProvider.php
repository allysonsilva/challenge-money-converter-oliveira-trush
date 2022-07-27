<?php

namespace Support;

use Illuminate\Support\AggregateServiceProvider;
use Support\ThirdPartyServices\ThirdPartyAPIsServiceProvider;

final class SupportServiceProvider extends AggregateServiceProvider
{
    /**
     * The provider class names.
     *
     * @var array<class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected $providers = [ // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
        ThirdPartyAPIsServiceProvider::class,
    ];
}
