<?php

namespace App\API\Currency\Http\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint
 */
class ConvertMoneyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @SuppressWarnings("UnusedFormalParameter")
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<mixed>
     */
    public function toArray($request) // phpcs:ignore
    {
        return [
            'moeda' => [
                'origem' => $this->originCurrency(),
                'destino' => $this->targetCurrency(),
            ],
            'taxas' => [
                'pagamento' => $this->getPaymentRate(),
                'conversao' => $this->getConversionRate(),
            ],
            'valores' => [
                'entrada' => $this->getValueToBeConverted(),
                'utilizado_conversao' => $this->getValueWithoutRates(),
                'valor_moeda_destino_usado_conversao' => $this->getRateOfConverted(),
                'convertido_moeda_destino' => $this->getValueOfConverted(),
            ],
            'forma_pagamento' => $this->getPaymentMethod(),
        ];
    }
}
