<?php

namespace Support\ThirdPartyServices\OpenExchangeRates;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;
use Support\ThirdPartyServices\OpenExchangeRates\Classes\APIClient;
use Support\ThirdPartyServices\OpenExchangeRates\Classes\RedisRepository;
use Support\ThirdPartyServices\OpenExchangeRates\Contracts\APIClient as APIClientContract;

class OpenExchangeRatesServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(APIClientContract::class, function ($app) {
            return new APIClient(
                $app->make(RedisRepository::class),
                appId: config('services.openexchangerates.token') ?? '',
                baseUrl: config('services.openexchangerates.url') ?? '',
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<mixed>
     *
     * @codeCoverageIgnore
     */
    public function provides(): array
    {
        return [APIClient::class];
    }
}
