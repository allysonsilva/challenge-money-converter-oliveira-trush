<?php

namespace Tests\Feature\ThirdPartyAPI;

use Tests\TestCase;
use Mockery\MockInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Support\ThirdPartyServices\OpenExchangeRates\Classes\APIClient;
use Support\ThirdPartyServices\OpenExchangeRates\DTO\ConvertedRateDTO;
use Support\ThirdPartyServices\OpenExchangeRates\Facades\ExchangeRate;
use Support\ThirdPartyServices\OpenExchangeRates\Classes\RedisRepository;
use Support\ThirdPartyServices\OpenExchangeRates\Exceptions\InvalidSymbolException;

/**
 * @group 3rdPartyAPI
 */
class ExchangeRateTest extends TestCase
{
    /**
     * @var array<mixed, mixed>
     */
    private array $rates;

    public function setUp(): void
    {
        parent::setUp();

        $this->rates = [
            'base' => 'USD',
            'rates' => [
                'AAA' => 10,
                'BBB' => 20,
                'CCC' => 30,
                'DDD' => 40,
                'EEE' => 50,
                'FFF' => 60,
                'GGG' => 70,
                'HHH' => 80,
            ],
        ];
    }

    /**
     * @testdox Atualizando os valores das TAXAS de CÂMBIO no repositório - REDIS
     *
     * @test
     */
    public function refreshRates(): void
    {
        // Arrange
        $redis = app(RedisRepository::class);
        $rates = [
            'rates' => [
                'A' => 1.0,
                'B' => 2.1,
                'C' => 3.2,
                'E' => 5.3,
            ],
        ];

        ExchangeRate::httpFake('latest.json', Http::response($rates));

        // Act
        ExchangeRate::refresh();

        // Assert
        self::assertEqualsCanonicalizing($rates['rates'], $redis->getFromCache(APIClient::REDIS_KEY_LATEST));
        Http::assertSentCount(1);
    }

    /**
     * @testdox Recuperando as TAXAS de CÂMBIO da API externa para salvar no cache - repositório REDIS
     *
     * @test
     */
    public function latestAndStoreRatesInCache(): void
    {
        $spy = $this->spy(RedisRepository::class);
        ExchangeRate::httpFake('latest.json', Http::response($this->rates));

        $latestExchangeRatesDTO = ExchangeRate::latest();

        self::assertEqualsCanonicalizing($latestExchangeRatesDTO->toArray(), $this->rates['rates']);

        Http::assertSentCount(1);

        $spy->shouldHaveReceived('getFromCache')->with(APIClient::REDIS_KEY_LATEST)->once();
        $spy->shouldHaveReceived('storeHashInCache')->with(APIClient::REDIS_KEY_LATEST, $this->rates['rates'])->once();
    }

    /**
     * @testdox Testando os parâmetros de query string na requisição `latest` da API de TAXA DE CÂMBIO
     *
     * @test
     */
    public function addingParametersInApiRequest(): void
    {
        $this->spy(RedisRepository::class);

        $exchangeRate = ExchangeRate::symbols('ABC', 'DEF')->base('BRL')->showAlternative();
        ExchangeRate::httpFake('latest.json', Http::response($this->rates));

        $exchangeRate->latest();

        Http::assertSent(function (Request $request) {
            parse_str(parse_url($request->url(), PHP_URL_QUERY), $query);

            return 'BRL' === $query['base'] &&
                    'ABC,DEF' === $query['symbols'] &&
                    '1' === $query['show_alternative'];
        });
    }

    /**
     * @testdox Caso as taxas existam no repositório - redis, então, deve ser recuperado imediatamente, sem fazer requisição a API de TAXA DE CÂMBIO
     *
     * @test
     */
    public function returnRatesImmediatelyFromCache(): void
    {
        $expect = ['A' => 1, 'B' => 2, 'C' => 3];

        $this->partialMock(RedisRepository::class, function (MockInterface $mock) use ($expect) {
            $mock->shouldReceive('getFromCache')
                 ->once()
                 ->withAnyArgs()
                 ->andReturn($expect);
        });

        self::assertEqualsCanonicalizing(ExchangeRate::latest()->toArray(), $expect);

        Http::assertNothingSent();
    }

    /**
     * @testdox Conversão de moedas realiada com sucesso
     *
     * @test
     */
    public function currencyConvertedSuccessfully(): void
    {
        $this->partialMock(RedisRepository::class)
             ->shouldReceive('getHashFromCache')
             ->once()
             ->withAnyArgs()
             ->andReturn(['AAA' => '1.00', 'BBB' => '2.00']);

        $convertedDTO = ExchangeRate::convert('AAA', 'BBB', 5.00);

        Http::assertNothingSent();

        static::assertTrue($convertedDTO instanceof ConvertedRateDTO);
        static::assertSame($convertedDTO->convertedValue, 10.00);
    }

    /**
     * @testdox Deve ser lançada a exceção `InvalidSymbolException` quando o código da moeda (FROM) no contexto de conversão não existir no repositório - redis
     *
     * @test
     */
    public function expectExceptionInvalidSymbolInFrom(): void
    {
        $this->expectException(InvalidSymbolException::class);

        $this->partialMock(RedisRepository::class)
             ->shouldReceive('getHashFromCache')
             ->once()
             ->withAnyArgs()
             ->andReturn(['AAA' => '1.00', 'BBB' => '2.00']);

        ExchangeRate::convert('CCC', 'AAA', 5.00);

        Http::assertNothingSent();
    }

    /**
     * @testdox Deve ser lançada a exceção `InvalidSymbolException` quando o código da moeda (TO) no contexto de conversão não existir no repositório - redis
     *
     * @test
     */
    public function expectExceptionInvalidSymbolInTo()
    {
        $this->expectException(InvalidSymbolException::class);

        $this->partialMock(RedisRepository::class)
             ->shouldReceive('getHashFromCache')
             ->once()
             ->withAnyArgs()
             ->andReturn(['AAA' => '1.00', 'BBB' => '2.00']);

        ExchangeRate::convert('AAA', 'CCC', 5.00);

        Http::assertNothingSent();
    }

    /**
     * @testdox Deve ser possível fazer reload, recuperar novamente uma nova versão das taxas de câmbio da API externa e salvar/atualizar o repositório
     *
     * @test
     *
     * @return void
     */
    public function latestReloadShouldBeBusted(): void
    {
        $spy = $this->spy(RedisRepository::class);

        ExchangeRate::httpFake('latest.json', Http::response($this->rates));

        ExchangeRate::shouldBustCache()->latest();

        Http::assertSentCount(1);

        $spy->shouldHaveReceived('forget');
        $spy->shouldNotHaveReceived('getFromCache');
        $spy->shouldHaveReceived('storeHashInCache');
    }

    /**
     * @testdox Deve ser possível manipular exceções na resposta da API
     *
     * @test
     */
    public function handleWithException(): void
    {
        $this->expectException(RequestException::class);

        $this->spy(RedisRepository::class);

        Http::fake([
            '*' => Http::response('Server Error', 500),
        ]);

        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
        ExchangeRate::whenThrowException(function (Response $response, $e) {
            static::assertEquals(500, $response->status());
        })->latest();

        Http::assertSentCount(1);
    }

    /**
     * @testdox Deve ser possível retornar `null` em `latest` quando alguma exceção for lançado e não haver tratamento para essa exceção
     *
     * @test
     */
    public function handleNoException()
    {
        $this->spy(RedisRepository::class);

        Http::fake([
            '*' => Http::response('Server Error', 500),
        ]);

        self::assertNull(ExchangeRate::latest());

        Http::assertSentCount(1);
    }
}
