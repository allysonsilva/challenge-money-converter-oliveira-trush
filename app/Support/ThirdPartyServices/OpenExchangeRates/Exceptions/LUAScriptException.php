<?php

namespace Support\ThirdPartyServices\OpenExchangeRates\Exceptions;

use Core\Exceptions\HttpException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class LUAScriptException extends HttpException
{
    public function __construct(string $message)
    {
        parent::__construct(HttpResponse::HTTP_INTERNAL_SERVER_ERROR, $message);
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
        return 'ERROR_EXECUTING_LUA_SCRIPT';
    }
}
