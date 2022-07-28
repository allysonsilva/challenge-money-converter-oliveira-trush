<?php

namespace CurrencyDomain\ValueObjects;

use NumberFormatter;
use Support\ThirdPartyServices\OpenExchangeRates\DTO\ConvertedRateDTO;

final class ValueTargetCurrency
{
    public readonly string $value;

    public readonly string $rate;

    // phpcs:disable
    public function __construct(private readonly ConvertedRateDTO $dto,
                                public readonly string $originCurrencyCode,
                                public readonly string $targetCurrencyCode)
    {
        // phpcs:enable
        $this->value = $this->getFormattedCurrency($dto->convertedValue, $targetCurrencyCode, 2);
        $this->rate = $this->getFormattedCurrency($dto->rateFromTo, $targetCurrencyCode);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function rate(): string
    {
        return $this->rate;
    }

    private function getFormattedCurrency(float $value, $currencyCode = 'BRL', int $precision = 4): string
    {
        $formatter = new NumberFormatter(config('money.locale'), NumberFormatter::CURRENCY);
        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $precision);
        $formatter->setAttribute(NumberFormatter::GROUPING_USED, true);
        $formatter->setTextAttribute(NumberFormatter::CURRENCY_CODE, $currencyCode);

        return $formatter->format($value);
    }
}
