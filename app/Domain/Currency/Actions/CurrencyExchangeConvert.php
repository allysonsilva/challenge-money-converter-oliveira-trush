<?php

namespace CurrencyDomain\Actions;

use Support\Contracts\ShouldActionInterface;
use CurrencyDomain\DTO\ConvertedCurrencyResultDTO;
use CurrencyDomain\ValueObjects\ValueWithoutRates;
use CurrencyDomain\ValueObjects\ValueToBeConverted;
use CurrencyDomain\Exceptions\NoPaymentMethodChosen;
use CurrencyDomain\ValueObjects\ValueTargetCurrency;
use CurrencyDomain\Exceptions\NoConversionRateChosen;
use CurrencyDomain\Enums\PaymentMethod as PaymentMethodEnum;
use Support\ThirdPartyServices\OpenExchangeRates\Facades\ExchangeRate;
use CurrencyDomain\ValueObjects\PaymentMethods\Contracts\PaymentMethod as PaymentMethodContract;
use CurrencyDomain\ValueObjects\ConversionRates\Contracts\ConversionRate as ConversionRateContract;

class CurrencyExchangeConvert implements ShouldActionInterface
{
    private PaymentMethodContract $chosenPaymentMethod;

    private ConversionRateContract $chosenConversionRate;

    /**
     * Execute the Action.
     *
     * @param string $targetCurrencyCode
     * @param float $amountToBeConverted
     * @param string $formOfPayment
     *
     * @return \CurrencyDomain\DTO\ConvertedCurrencyResultDTO
     */
    public function execute(string $targetCurrencyCode, float $amountToBeConverted, string $formOfPayment): ConvertedCurrencyResultDTO
    {
        $valueToBeConverted = new ValueToBeConverted(money($amountToBeConverted));
        $paymentMethodEnum = PaymentMethodEnum::from($formOfPayment);

        $this->chooseConversionRate($valueToBeConverted);
        $this->choosePaymentMethod($valueToBeConverted, $paymentMethodEnum);

        $valueUsedToConvert = $this->discountRates($valueToBeConverted);

        $defaultCurrency = config('currency.default_currency');
        $convertedDTO = ExchangeRate::convert($defaultCurrency, $targetCurrencyCode, (float) $valueUsedToConvert->formatByDecimal());

        return new ConvertedCurrencyResultDTO(
            originCurrency: $defaultCurrency,
            targetCurrency: $targetCurrencyCode,
            valueToBeConverted: $valueToBeConverted,
            paymentDetails: $this->chosenPaymentMethod,
            conversionRate: $this->chosenConversionRate,
            valueUsedToConvert: $valueUsedToConvert,
            convertedToTargetCurrency: new ValueTargetCurrency($convertedDTO, $defaultCurrency, $targetCurrencyCode),
        );
    }

    /**
     * Choose the payment method according to the rule.
     *
     * @param \CurrencyDomain\ValueObjects\ValueToBeConverted $valueToBeConverted
     * @param \CurrencyDomain\Enums\PaymentMethod $paymentMethodEnum
     *
     * @return void
     *
     * @throws \CurrencyDomain\Exceptions\NoPaymentMethodChosen
     */
    private function choosePaymentMethod(ValueToBeConverted $valueToBeConverted, PaymentMethodEnum $paymentMethodEnum): void
    {
        foreach (config('currency.payment_methods') as $paymentMethodClass) {
            $paymentMethod = new $paymentMethodClass($valueToBeConverted, $paymentMethodEnum);

            if ($paymentMethod->isSatisfied()) {
                $this->chosenPaymentMethod = $paymentMethod;
            }
        }

        if (! isset($this->chosenPaymentMethod)) {
            // phpcs:ignore
            throw new NoPaymentMethodChosen;
        }
    }

    /**
     * Choose the conversion rate according to the rule.
     *
     * @param \CurrencyDomain\ValueObjects\ValueToBeConverted $valueToBeConverted
     *
     * @return void
     *
     * @throws \CurrencyDomain\Exceptions\NoConversionRateChosen
     */
    private function chooseConversionRate(ValueToBeConverted $valueToBeConverted): void
    {
        foreach (config('currency.conversion_rates') as $conversionRateClass) {
            $conversionRate = new $conversionRateClass($valueToBeConverted);

            if ($conversionRate->isSatisfied()) {
                $this->chosenConversionRate = $conversionRate;
            }
        }

        if (! isset($this->chosenConversionRate)) {
            // phpcs:ignore
            throw new NoConversionRateChosen;
        }
    }

    /**
     * Deducts fees from the conversion value.
     *
     * @return \CurrencyDomain\ValueObjects\ValueWithoutRates
     */
    private function discountRates(ValueToBeConverted $valueToBeConverted): ValueWithoutRates
    {
        /** @var \Cknow\Money\Money */
        $money = $valueToBeConverted->subtract($this->chosenPaymentMethod->calculatePaymentRate())
                                    ->subtract($this->chosenConversionRate->calculateConversionRate());

        return new ValueWithoutRates($money);
    }
}
