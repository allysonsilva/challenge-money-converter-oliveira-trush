<?php

namespace Support\ThirdPartyServices\OpenExchangeRates\Exceptions;

use Core\Exceptions\HttpException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class InvalidSymbolException extends HttpException
{
    public function __construct(string $currencyCode)
    {
        $message = __('currency.invalid code', ['symbol' => $currencyCode]);

        parent::__construct(HttpResponse::HTTP_BAD_REQUEST, $message);
    }

    /**
     * Report the exception.
     *
     * @return bool|null
     *
     * @codeCoverageIgnore
     */
    public function report(): ?bool
    {
        return false;
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
        return 'INVALID_SYMBOL_CODE';
    }
}
