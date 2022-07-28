<?php

namespace CurrencyDomain\ValueObjects\ConversionRates;

use CurrencyDomain\ValueObjects\ConversionRates\Traits\Shared;
use CurrencyDomain\ValueObjects\ConversionRates\Contracts\ConversionRate;

class ValueAbove implements ConversionRate
{
    use Shared;

    /**
     * @inheritDoc
     */
    public function getCompareValue(): int
    {
        return 3000_00;
    }

    /**
     * @inheritDoc
     */
    public function getPercentage(): float
    {
        return 0.01;
    }

    /**
     * @inheritDoc
     */
    public function isSatisfied(): bool
    {
        return $this->value->greaterThanOrEqual(money($this->getCompareValue()));
    }
}
