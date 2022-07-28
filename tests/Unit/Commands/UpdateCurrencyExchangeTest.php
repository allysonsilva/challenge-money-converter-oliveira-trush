<?php

namespace Tests\Unit\Commands;

use Tests\TestCase;
use Support\ThirdPartyServices\OpenExchangeRates\Facades\ExchangeRate;

/**
 * @group Console
 *
 * @testdox UpdateCurrencyExchangeTest - (Tests\Unit\Commands\UpdateCurrencyExchangeTest)
 */
class UpdateCurrencyExchangeTest extends TestCase
{
    /**
     * @testdox Testando o comando do artisan "currency-exchange:update"
     *
     * @test
     */
    public function testConsoleCommand(): void
    {
        ExchangeRate::spy();

        $this->artisan('currency-exchange:update')->assertExitCode(0);

        ExchangeRate::shouldHaveReceived('refresh')->once();
    }
}
