<?php

namespace App\API\Currency\Http\Requests;

use Illuminate\Validation\Rules\Enum;
use CurrencyDomain\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use App\API\Currency\Http\Requests\Rules\MoneyBetween;

class ConvertMoneyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'amount' => [
                'required',
                'regex:/^\d+\.\d{2,6}$/',
                new MoneyBetween(),
            ],
            'currency_symbol' => 'required|size:3',
            'payment_method' => ['required', new Enum(PaymentMethod::class)],
        ];
    }
}
