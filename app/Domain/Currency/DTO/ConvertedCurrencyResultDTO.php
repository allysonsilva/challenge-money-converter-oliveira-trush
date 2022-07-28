<?php

namespace CurrencyDomain\DTO;

use Support\DTO\BaseDto;
use CurrencyDomain\ValueObjects\ValueWithoutRates;
use CurrencyDomain\ValueObjects\ValueToBeConverted;
use CurrencyDomain\ValueObjects\ValueTargetCurrency;
use CurrencyDomain\ValueObjects\PaymentMethods\Contracts\PaymentMethod as PaymentMethodContract;
use CurrencyDomain\ValueObjects\ConversionRates\Contracts\ConversionRate as ConversionRateContract;

final class ConvertedCurrencyResultDTO extends BaseDto
{
    public string $originCurrency;

    public string $targetCurrency;

    public ValueToBeConverted $valueToBeConverted;

    public PaymentMethodContract $paymentDetails;

    public ConversionRateContract $conversionRate;

    public ValueWithoutRates $valueUsedToConvert;

    public ValueTargetCurrency $convertedToTargetCurrency;

    public function originCurrency(): string
    {
        return strtoupper($this->originCurrency);
    }

    public function targetCurrency(): string
    {
        return strtoupper($this->targetCurrency);
    }

    public function getValueToBeConverted(): string
    {
        return $this->valueToBeConverted->render();
    }

    public function getConversionRate(): string
    {
        return $this->conversionRate->getRate();
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentDetails->getMethod();
    }

    public function getPaymentRate(): string
    {
        return $this->paymentDetails->getRate();
    }

    public function getValueOfConverted(): string
    {
        return $this->convertedToTargetCurrency->value();
    }

    public function getRateOfConverted(): string
    {
        return $this->convertedToTargetCurrency->rate();
    }

    public function getValueWithoutRates(): string
    {
        return $this->valueUsedToConvert->render();
    }
}
