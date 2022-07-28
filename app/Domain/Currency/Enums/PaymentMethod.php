<?php

namespace CurrencyDomain\Enums;

enum PaymentMethod: string
{
    case BOLETO = 'BOLETO';
    case CREDIT_CARD = 'CREDIT_CARD';

    public function render(): string
    {
        return match ($this) {
            self::BOLETO => 'Boleto',
            self::CREDIT_CARD => 'Cartão de crédito',
        };
    }
}
