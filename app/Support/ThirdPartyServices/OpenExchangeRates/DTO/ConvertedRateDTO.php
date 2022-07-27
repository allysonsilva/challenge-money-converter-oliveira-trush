<?php

declare(strict_types=1);

namespace Support\ThirdPartyServices\OpenExchangeRates\DTO;

use Support\DTO\BaseDto;
use Spatie\DataTransferObject\Attributes\MapTo;
use Spatie\DataTransferObject\Attributes\Strict;

#[Strict]
final class ConvertedRateDTO extends BaseDto
{
    #[MapTo('value_to_rate')]
    public readonly float $valueToRate;

    #[MapTo('value_from_rate')]
    public readonly float $valueFromRate;

    #[MapTo('converted_currency')]
    public readonly float $convertedValue;

    #[MapTo('rate_to_from')]
    public readonly float $rateToFrom;

    #[MapTo('rate_from_to')]
    public readonly float $rateFromTo;

    #[MapTo('from_symbol')]
    public readonly string $fromSymbol;

    #[MapTo('to_symbol')]
    public readonly string $toSymbol;

    #[MapTo('base_currency')]
    public readonly string $baseCurrency;
}
