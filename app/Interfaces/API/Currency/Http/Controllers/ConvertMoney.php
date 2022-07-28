<?php

namespace App\API\Currency\Http\Controllers;

use Support\Http\Controller;
use Illuminate\Support\Facades\Mail;
use App\API\Currency\Mail\ConvertedCurrency;
use CurrencyDomain\Actions\CurrencyExchangeConvert;
use App\API\Currency\Http\Requests\ConvertMoneyRequest;
use App\API\Currency\Http\Transformers\ConvertMoneyResource;
use Throwable;

class ConvertMoney extends Controller
{
    public function __construct(private readonly CurrencyExchangeConvert $action)
    {
    }

    /**
     * Currency conversion endpoint, can be used to convert
     * any amount from one currency to another.
     *
     * @param \App\API\Currency\Http\Requests\ConvertMoneyRequest $request
     *
     * @return \Illuminate\Http\Resources\Json\JsonResource|\Illuminate\Http\Response
     */
    public function __invoke(ConvertMoneyRequest $request)
    {
        $dto = $this->action
                    ->execute($request->currency_symbol, $request->amount, $request->payment_method);

        try {
            Mail::to($request->user())->send(new ConvertedCurrency($dto));
        } catch (Throwable) { // @codeCoverageIgnore
        }

        return new ConvertMoneyResource($dto);
    }
}
