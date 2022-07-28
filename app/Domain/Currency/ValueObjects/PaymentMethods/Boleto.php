<?php

namespace CurrencyDomain\ValueObjects\PaymentMethods;

use CurrencyDomain\Enums\PaymentMethod;
use CurrencyDomain\ValueObjects\PaymentMethods\Traits\Shared;
use CurrencyDomain\ValueObjects\PaymentMethods\Contracts\PaymentMethod as PaymentMethodContract;

class Boleto implements PaymentMethodContract
{
    use Shared;

    /**
     * @inheritDoc
     */
    public function getPercentage(): float
    {
        return 0.0145;
    }

    /**
     * @inheritDoc
     */
    public function isSatisfied(): bool
    {
        return PaymentMethod::BOLETO === $this->paymentMethod;
    }
}
