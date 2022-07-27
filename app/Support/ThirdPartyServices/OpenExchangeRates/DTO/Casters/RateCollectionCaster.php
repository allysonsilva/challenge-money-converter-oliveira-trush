<?php

namespace Support\ThirdPartyServices\OpenExchangeRates\DTO\Casters;

use Illuminate\Support\Arr;
use Spatie\DataTransferObject\Caster;
use Support\ThirdPartyServices\OpenExchangeRates\DTO\RateDTO;
use Support\ThirdPartyServices\OpenExchangeRates\DTO\Collections\CollectionOfCurrency;

class RateCollectionCaster implements Caster
{
    public function cast(mixed $rates): CollectionOfCurrency
    {
        $ratesDTO = Arr::map($rates, fn ($value, $symbol) => new RateDTO(symbol: $symbol, exchangeRate: $value));

        return new CollectionOfCurrency($ratesDTO);
    }
}
