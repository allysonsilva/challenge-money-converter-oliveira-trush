<?php

declare(strict_types=1);

namespace Support\ThirdPartyServices\OpenExchangeRates\DTO;

use ArrayAccess;
use Support\DTO\BaseDto;
use Illuminate\Contracts\Support\Arrayable;
use Spatie\DataTransferObject\Attributes\MapTo;
use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\Attributes\CastWith;
use Support\ThirdPartyServices\OpenExchangeRates\DTO\Casters\RateCollectionCaster;
use Support\ThirdPartyServices\OpenExchangeRates\DTO\Collections\CollectionOfCurrency;

#[Strict]
final class LatestExchangeRatesDTO extends BaseDto implements ArrayAccess, Arrayable
{
    public readonly string|null $base;

     #[MapTo('is_cached')]
    public readonly bool|null $isCached;

    #[CastWith(RateCollectionCaster::class)]
    public readonly CollectionOfCurrency $rates;

    /**
     * Convert the DTO instance to an array.
     *
     * @return array<string, float>
     */
    public function toArray(): array
    {
        return $this->rates->toArray();
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function offsetExists(mixed $key): bool
    {
        return isset($this->rates[$key]);
    }

    /**
     * Get an item at a given offset.
     *
     * @param mixed $key
     *
     * @return \Support\ThirdPartyServices\OpenExchangeRates\DTO\RateDTO
     */
    public function offsetGet(mixed $key): RateDTO
    {
        return $this->rates[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->rates[$key] = $value;
    }

    /**
     * Unset the item at a given offset.
     *
     * @param mixed $key
     *
     * @return void
     */
    public function offsetUnset(mixed $key): void
    {
        unset($this->rates[$key]);
    }
}
