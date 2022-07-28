<?php

namespace CurrencyDomain\ValueObjects;

use Stringable;
use Cknow\Money\Money;
use Illuminate\Support\Traits\ForwardsCalls;

final class ValueToBeConverted implements Stringable
{
    use ForwardsCalls;

    public function __construct(private readonly Money $value)
    {
    }

    /**
     * @codeCoverageIgnore
     */
    public function money(): Money
    {
        return $this->value;
    }

    /**
     * @codeCoverageIgnore
     */
    public function __toString(): string
    {
        return $this->value->__toString();
    }

    public function __call(string $method, array $arguments)
    {
        return $this->forwardCallTo($this->value, $method, $arguments);
    }
}
