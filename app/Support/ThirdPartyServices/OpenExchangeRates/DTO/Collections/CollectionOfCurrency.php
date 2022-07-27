<?php

namespace Support\ThirdPartyServices\OpenExchangeRates\DTO\Collections;

use Illuminate\Support\Collection;
use Support\ThirdPartyServices\OpenExchangeRates\DTO\RateDTO;

class CollectionOfCurrency extends Collection
{
    /**
     * Convert the Collection instance to an array.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        /** @var \Support\ThirdPartyServices\OpenExchangeRates\DTO\RateDTO $rateDTO */
        return $this->mapWithKeys(function ($rateDTO) {
            return $rateDTO->toArray();
        })->all();
    }

    /**
     * Get an item at a given offset.
     *
     * @param string $key
     *
     * @return \Support\ThirdPartyServices\OpenExchangeRates\DTO\RateDTO
     */
    public function offsetGet($key): RateDTO // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
    {
        return parent::offsetGet($key);
    }
}
