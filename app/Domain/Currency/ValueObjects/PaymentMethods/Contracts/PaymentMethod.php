<?php

namespace CurrencyDomain\ValueObjects\PaymentMethods\Contracts;

use Cknow\Money\Money;

interface PaymentMethod
{
    /**
     * Percentage value used to subtract the original purchase currency value.
     *
     * @return float
     */
    public function getPercentage(): float;

    /**
     * Retrieves the method used for payment.
     *
     * @return string
     */
    public function getMethod(): string;

    /**
     * Recovers the rate amount used in the payment.
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
    public function calculatePaymentRate(): Money;
}
