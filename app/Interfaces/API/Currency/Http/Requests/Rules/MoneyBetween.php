<?php

namespace App\API\Currency\Http\Requests\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class MoneyBetween implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param \Closure $fail
     *
     * @return void
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    public function __invoke($attribute, $value, $fail)
    {
        $amountInput = floatval($value);

        $amountMoney = money($amountInput);
        $minMoney = money(1000_00);
        $maxMoney = money(100_000_00);

        if ($amountMoney->greaterThan($minMoney) && $amountMoney->lessThan($maxMoney)) {
            return;
        }

        $fail('validation.between.numeric')->translate([
            'min' => $minMoney->render(),
            'max' => $maxMoney->render(),
        ]);
    }
}
