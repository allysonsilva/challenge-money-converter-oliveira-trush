@component('mail::message')
# Informações na conversão da Moeda

- **Moeda de origem:** {{ $details->originCurrency() }}
- **Moeda de destino:** {{ $details->targetCurrency() }}
- **Valor para conversão:** {{ $details->getValueToBeConverted() }}
- **Forma de pagamento:** {{ $details->getPaymentMethod() }}
- **Valor da "Moeda de destino" usado para conversão:** {{ $details->getRateOfConverted() }}
- **Valor comprado em "Moeda de destino":** {{ $details->getValueOfConverted() }}
- **Taxa de pagamento:** {{ $details->getPaymentRate() }}
- **Taxa de conversão:** {{ $details->getConversionRate() }}
- **Valor utilizado para conversão descontando as taxas:** {{ $details->getValueWithoutRates() }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
