<?php

use CurrencyDomain\ValueObjects\PaymentMethods\Boleto;
use CurrencyDomain\ValueObjects\PaymentMethods\CreditCard;
use CurrencyDomain\ValueObjects\ConversionRates\ValueAbove;
use CurrencyDomain\ValueObjects\ConversionRates\ValueBelow;

return [

    'default_currency' => 'BRL',

    'payment_methods' => [
        Boleto::class,
        CreditCard::class,
    ],

    'conversion_rates' => [
        ValueAbove::class,
        ValueBelow::class,
    ],

];
