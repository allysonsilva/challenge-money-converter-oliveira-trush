<?php

namespace CurrencyDomain\ValueObjects\ConversionRates\Traits;

use Cknow\Money\Money;
use CurrencyDomain\ValueObjects\ValueToBeConverted;

trait Shared
{
    public function __construct(private readonly ValueToBeConverted $value)
    {
        $this->prepare();
    }

    /**
     * @inheritDoc
     */
    public function getRate(): string
    {
        return $this->calculateConversionRate()->render();
    }

    /**
     * @inheritDoc
     */
    public function calculateConversionRate(): Money
    {
        return $this->value->multiply($this->getPercentage());
    }

    protected function prepare(): void
    {
    }
}
