<?php

namespace Tests\Unit\Support\DTO;

use Tests\TestCase;
use Support\ThirdPartyServices\OpenExchangeRates\DTO\RateDTO;
use Support\ThirdPartyServices\OpenExchangeRates\DTO\LatestExchangeRatesDTO;

/**
 * @group Support
 */
class LatestExchangeRatesDTOTest extends TestCase
{
    /**
     * @testdox Testando a classe de DTO LatestExchangeRatesDTO
     *
     * @test
     */
    public function testDTO(): void
    {
        $rates = [
            'A' => 1.0,
            'B' => 2.0,
            'C' => 3.0,
        ];

        $dto = new LatestExchangeRatesDTO(rates: $rates);

        self::assertTrue(empty($dto['D']));
        self::assertFalse(empty($dto['A']));
        self::assertTrue($dto['A'] instanceof RateDTO);

        $dto['D'] = new RateDTO(symbol: 'D', exchangeRate: 4.0);
        self::assertFalse(empty($dto['D']));

        unset($dto['A']);
        self::assertTrue(empty($dto['A']));
    }
}
