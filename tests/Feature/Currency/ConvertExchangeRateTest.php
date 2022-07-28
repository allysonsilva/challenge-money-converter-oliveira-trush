<?php

namespace Tests\Feature\Currency;

use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use App\API\Currency\Mail\ConvertedCurrency;
use CurrencyDomain\DTO\ConvertedCurrencyResultDTO;
use CurrencyDomain\Exceptions\NoPaymentMethodChosen;
use CurrencyDomain\Exceptions\NoConversionRateChosen;
use App\Http\Middleware\Authenticate as AuthenticateMiddleware;
use Illuminate\Auth\Middleware\Authorize as AuthorizeMiddleware;

class ConvertExchangeRateTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([AuthenticateMiddleware::class, AuthorizeMiddleware::class]);
    }

    /**
     * @dataProvider getFixtures
     */
    public function testConvertExchangeRate(array $queryRequest, int $statusCode, array $responseBody)
    {
        $response = $this->withSameDomain()->getJson(route('api.v1.currency.convert', $queryRequest));

        $response
            ->assertStatus($statusCode)
            ->assertJson($responseBody);
    }

    /**
     * @testdox Deve ser enviado um email sobre o resultado da conversão
     *
     * @test
     */
    public function withMail(): void
    {
        Mail::fake();

        $query = [
            'amount' => '5000.00',
            'payment_method' => 'BOLETO',
            'currency_symbol' => 'USD',
        ];

        $response = $this->logged()->getJson(route('api.v1.currency.convert', $query));

        Mail::assertQueued(function (ConvertedCurrency $mail) {
            return $mail->details instanceof ConvertedCurrencyResultDTO &&
                    $mail->hasSubject('Valor convertido de BRL => USD');
        });

        $response->assertOk();
    }

    /**
     * @testdox Deve ser lançado uma exceção quando não tiver nenhum método de pagamento configurado
     *
     * @test
     */
    public function exceptionWhenDontHavePaymentMethod(): void
    {
        $this->expectException(NoPaymentMethodChosen::class);

        config(['currency.payment_methods' => []]);

        $query = [
            'amount' => '5000.00',
            'payment_method' => 'BOLETO',
            'currency_symbol' => 'USD',
        ];

        $response = $this->logged()
                         ->withoutExceptionHandling()
                         ->getJson(route('api.v1.currency.convert', $query));

        $response->assertStatus(400);
    }

    /**
     * @testdox Deve ser lançado uma exceção quando não tiver nenhuma taxa de conversão configurado
     *
     * @test
     */
    public function exceptionWhenDontHaveConversionRate(): void
    {
        $this->expectException(NoConversionRateChosen::class);

        config(['currency.conversion_rates' => []]);

        $query = [
            'amount' => '5000.00',
            'payment_method' => 'BOLETO',
            'currency_symbol' => 'USD',
        ];

        $response = $this->logged()
                         ->withoutExceptionHandling()
                         ->getJson(route('api.v1.currency.convert', $query));

        $response->assertStatus(400);
    }

    public function getFixtures(): iterable
    {
        $fixtures = glob(__DIR__ . '/Fixtures/*.json');

        foreach ($fixtures as $filename) {
            $fixture = json_decode(file_get_contents($filename) ?: '', true);

            yield $fixture['name'] => [
                'query' => data_get($fixture, 'request.query'),
                'statusCode' => data_get($fixture, 'response.statusCode'),
                'responseBody' => data_get($fixture, 'response.body'),
            ];
        }
    }
}
