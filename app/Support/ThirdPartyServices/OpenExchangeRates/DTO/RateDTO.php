<?php

declare(strict_types=1);

namespace Support\ThirdPartyServices\OpenExchangeRates\DTO;

use Support\DTO\BaseDto;
use Illuminate\Contracts\Support\Arrayable;
use Spatie\DataTransferObject\Attributes\MapTo;
use Spatie\DataTransferObject\Attributes\Strict;

#[Strict]
final class RateDTO extends BaseDto implements Arrayable
{
    #[MapTo('code')]
    public readonly string $symbol;

    #[MapTo('rate')]
    public readonly float $exchangeRate;

    /**
     * Convert the DTO instance to an array.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [$this->symbol => $this->exchangeRate];
    }
}
