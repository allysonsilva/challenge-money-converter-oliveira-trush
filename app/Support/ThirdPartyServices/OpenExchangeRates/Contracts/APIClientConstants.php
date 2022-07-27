<?php

namespace Support\ThirdPartyServices\OpenExchangeRates\Contracts;

interface APIClientConstants
{
    /**
     * Enter the three-letter currency code of your preferred base currency.
     */
    public const BASE_CURRENCY = 'USD';

    /**
     * Name of the key in redis used to store the latest exchange currencies.
     */
    public const REDIS_KEY_LATEST = 'latest';

    /**
     * Is used to set the number of digits after the decimal place in the result.
     */
    public const DIGITS_AFTER_DECIMAL_PLACE = 6;
}
