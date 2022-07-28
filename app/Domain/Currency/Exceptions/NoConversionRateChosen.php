<?php

namespace CurrencyDomain\Exceptions;

use Core\Exceptions\HttpException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class NoConversionRateChosen extends HttpException
{
    public function __construct()
    {
        $message = __('currency.no conversion rate chosen');

        parent::__construct(HttpResponse::HTTP_BAD_REQUEST, $message);
    }

    /**
     * Type, exception identity code.
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    protected function getType(): string
    {
        return 'NO_PAYMENT_METHOD_CHOSEN';
    }
}
