<?php

namespace Support\ThirdPartyServices\OpenExchangeRates\Facades;

use Illuminate\Support\Facades\Facade;
use Support\ThirdPartyServices\OpenExchangeRates\Contracts\APIClient;

/**
 * @see \Support\ThirdPartyServices\OpenExchangeRates\Classes\APIClient
 */
class ExchangeRate extends Facade
{
    protected static function getFacadeAccessor()
    {
        return APIClient::class;
    }
}
