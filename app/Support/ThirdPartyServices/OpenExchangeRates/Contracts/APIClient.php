<?php

namespace Support\ThirdPartyServices\OpenExchangeRates\Contracts;

use Support\ThirdPartyServices\OpenExchangeRates\DTO\ConvertedRateDTO;
use Support\ThirdPartyServices\OpenExchangeRates\DTO\LatestExchangeRatesDTO;

interface APIClient
{
    /**
     * Changing base currency.
     * Enter the three-letter currency code of your preferred base currency.
     *
     * @param string $base
     *
     * @return $this
     */
    public function base(string $base): static;

    /**
     * Enter a list of comma-separated currency codes to limit output currencies.
     *
     * @param array<string> $symbols
     *
     * @return $this
     */
    public function symbols(string ...$symbols): static;

    /**
     * Extend returned values with alternative, black market and digital currency rates.
     *
     * @return $this
     */
    public function showAlternative(): static;

    /**
     * Get the latest foreign exchange reference rates.
     * Latest endpoint will return exchange rate data updated on daily basis.
     *
     * @return \Support\ThirdPartyServices\OpenExchangeRates\DTO\LatestExchangeRatesDTO|null
     */
    public function latest(): ?LatestExchangeRatesDTO;

    /**
     * Return the converted values between the $from and $to parameters.
     *
     * @param string $from
     * @param string $to
     * @param float $amount
     *
     * @return \Support\ThirdPartyServices\OpenExchangeRates\DTO\ConvertedRateDTO
     */
    public function convert(string $from, string $to, float $amount): ConvertedRateDTO;

    /**
     * Update for currency exchanges to the latest version.
     *
     * @return void
     */
    public function refresh(): void;
}
