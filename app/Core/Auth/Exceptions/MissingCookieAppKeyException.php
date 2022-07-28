<?php

namespace Core\Auth\Exceptions;

use RuntimeException;

/**
 * @codeCoverageIgnore
 */
class MissingCookieAppKeyException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('No encryption key for use in authentication via cookies has been specified!');
    }
}
