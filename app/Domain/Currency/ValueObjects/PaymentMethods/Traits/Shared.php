<?php

namespace CurrencyDomain\ValueObjects\PaymentMethods\Traits;

use Cknow\Money\Money;
use CurrencyDomain\Enums\PaymentMethod;
use CurrencyDomain\ValueObjects\ValueToBeConverted;

trait Shared
{
    public function __construct(private ValueToBeConverted $value, public readonly PaymentMethod $paymentMethod)
    {
        $this->prepare();
    }

    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return $this->paymentMethod->render();
    }

    /**
     * @inheritDoc
     */
    public function getRate(): string
    {
        return $this->calculatePaymentRate()->render();
    }

    /**
     * @inheritDoc
     */
    public function calculatePaymentRate(): Money
    {
        return money($this->value->multiply($this->getPercentage()));
    }

    protected function prepare(): void
    {
    }
}
