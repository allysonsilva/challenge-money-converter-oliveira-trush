<?php

namespace CurrencyDomain\ValueObjects\PaymentMethods;

use CurrencyDomain\Enums\PaymentMethod;
use CurrencyDomain\ValueObjects\PaymentMethods\Traits\Shared;
use CurrencyDomain\ValueObjects\PaymentMethods\Contracts\PaymentMethod as PaymentMethodContract;

class CreditCard implements PaymentMethodContract
{
    use Shared;

    /**
     * @inheritDoc
     */
    public function getPercentage(): float
    {
        return 0.0763;
    }

    /**
     * @inheritDoc
     */
    public function isSatisfied(): bool
    {
        return PaymentMethod::CREDIT_CARD === $this->paymentMethod;
    }
}
