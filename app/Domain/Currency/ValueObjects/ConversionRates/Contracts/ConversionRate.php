<?php

namespace CurrencyDomain\ValueObjects\ConversionRates\Contracts;

use Cknow\Money\Money;

interface ConversionRate
{
    /**
     * Value used to know if the rule must be satisfied or not.
     *
     * @return int
     */
    public function getCompareValue(): int;

    /**
     * Percentage value used to subtract the original purchase currency value.
     *
     * @return float
     */
    public function getPercentage(): float;

    /**
     * Retrieves the value of the rate used in the conversion..
     *
     * @return string
     */
    public function getRate(): string;

    /**
     * Checks if the rule / class is satisfied to be used / manipulated.
     *
     * @return bool
     */
    public function isSatisfied(): bool;

    /**
     * Calculates the value of the purchase currency conversion rate.
     *
     * @return \Cknow\Money\Money
     */
    public function calculateConversionRate(): Money;
}
