<?php

namespace Support\ThirdPartyServices\OpenExchangeRates\Classes;

use Support\ThirdPartyServices\OpenExchangeRates\Traits\HandleCache;
use Support\ThirdPartyServices\OpenExchangeRates\DTO\ConvertedRateDTO;
use Support\ThirdPartyServices\OpenExchangeRates\Traits\HandleRequest;
use Support\ThirdPartyServices\OpenExchangeRates\DTO\LatestExchangeRatesDTO;
use Support\ThirdPartyServices\OpenExchangeRates\Contracts\APIClientConstants;
use Support\ThirdPartyServices\OpenExchangeRates\Exceptions\InvalidSymbolException;
use Support\ThirdPartyServices\OpenExchangeRates\Contracts\APIClient as APIClientContract;

class APIClient implements APIClientContract, APIClientConstants
{
    use HandleCache;
    use HandleRequest;

    /**
     * The base URL for the Exchange Rates API.
     *
     * @var string
     */
    public readonly string $baseUrl;

    /**
     * The API key for the Exchange Rates API.
     *
     * @var string
     */
    public readonly string $apiKey;

    /**
     * Parameters that will be used in the request.
     *
     * @var array<string, mixed>
     */
    protected array $queryParams = [];

    public function __construct(RedisRepository $redisRepository, string $appId, string $baseUrl)
    {
        $this->apiKey = $appId;
        $this->baseUrl = $baseUrl;
        $this->cache = $redisRepository;

        $this->queryParams['base'] = static::BASE_CURRENCY;
    }

    /**
     * @inheritDoc
     */
    public function base(string $base): static
    {
        $this->queryParams['base'] = strtoupper($base);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function symbols(string ...$symbols): static
    {
        $this->queryParams['symbols'] = implode(',', $symbols);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function showAlternative(): static
    {
        $this->queryParams['show_alternative'] = true;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function latest(): ?LatestExchangeRatesDTO
    {
        if ($cachedExchangeRate = $this->attemptToResolveFromCache(static::REDIS_KEY_LATEST)) {
            return new LatestExchangeRatesDTO(isCached: true, rates: $cachedExchangeRate);
        }

        /** @var array{base: string, rates: array<string, float>}|null */
        $exchangeRates = $this->makeRequest('latest.json', $this->queryParams);

        if (is_null($exchangeRates)) {
            return null;
        }

        $exchangeRatesDTO = new LatestExchangeRatesDTO(
            base: ($exchangeRates['base'] ?? static::BASE_CURRENCY),
            rates: $exchangeRates['rates']
        );

        if ($this->shouldCache) {
            $this->cache->storeHashInCache(static::REDIS_KEY_LATEST, $exchangeRatesDTO->rates->toArray());
        }

        return $exchangeRatesDTO;
    }

    /**
     * @inheritDoc
     *
     * @throws \Support\ThirdPartyServices\OpenExchangeRates\Exceptions\InvalidSymbolException
     */
    public function convert(string $from, string $to, float $amount): ConvertedRateDTO
    {
        $exchangeRates = $this->cache->getHashFromCache(static::REDIS_KEY_LATEST, [$from, $to]);

        $fromRate = $exchangeRates[$from] ?? throw new InvalidSymbolException($from);
        $toRate = $exchangeRates[$to] ?? throw new InvalidSymbolException($to);

        // Limiting float number to {static::DIGITS_AFTER_DECIMAL_PLACE} number of decimal places!
        // $fromRate = sprintf('%.'.static::DIGITS_AFTER_DECIMAL_PLACE.'F', $fromRate);
        // $toRate = sprintf('%.'.static::DIGITS_AFTER_DECIMAL_PLACE.'F', $toRate);

        $rateToFrom = bcdiv($toRate, $fromRate, static::DIGITS_AFTER_DECIMAL_PLACE);
        $rateFromTo = bcdiv($fromRate, $toRate, static::DIGITS_AFTER_DECIMAL_PLACE);

        $convertedValue = bcmul($rateToFrom, $amount, static::DIGITS_AFTER_DECIMAL_PLACE);

        return new ConvertedRateDTO(
            valueToRate: floatval($toRate),
            valueFromRate: floatval($fromRate),
            convertedValue: floatval($convertedValue),
            rateToFrom: floatval($rateToFrom),
            rateFromTo: floatval($rateFromTo),
            fromSymbol: $from,
            toSymbol: $to,
            baseCurrency: static::BASE_CURRENCY
        );
    }

    /**
     * @inheritDoc
     */
    public function refresh(): void
    {
        $exchangeRates = json_encode($this->dontCache()->latest()->toArray());

        $this->cache
             ->runLUAScript(
                 <<<'LUA'
                    -- we decode the json string
                    local rates = cjson.decode(ARGV[1])

                    if next(rates) == nil then return nil end

                    local bulk = {}

                    for symbol, rate in pairs(rates) do
                        table.insert(bulk, tostring(symbol))
                        table.insert(bulk, tostring(rate))
                    end

                    -- Delete all the keys of the currently selected DB.
                    redis.call('FLUSHDB', 'SYNC')

                    -- hash set to redis according key and filed/value pair
                    local numberFieldsAdded = redis.call('HSET', KEYS[1], unpack(bulk))

                    return numberFieldsAdded
                 LUA,
                 [static::REDIS_KEY_LATEST],
                 [$exchangeRates],
             );
    }
}
