<?php

namespace Tests\Unit\Auth\Middleware;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Core\Http\Middleware\ForceJsonResponse;
use Illuminate\Auth\AuthenticationException;
use Core\Auth\Middleware\BlockIfNotSameDomain;

/**
 * @small
 *
 * @group Auth
 * @group Middleware
 */
class BlockIfNotSameDomainTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Route::get('dummy-test-route', fn () => json_encode(['nice']))->middleware(
            ['api', BlockIfNotSameDomain::class]
        );
    }

    /**
     * @testdox Quando o domínio/URL da origem não for o mesmo da aplicação, então, a solicitação não deve ser processada
     */
    public function testFailWhenDomainIsNotSameAsOrigin()
    {
        // Arrange
        $headers = [
            'origin' => 'http://otherdomain.com',
        ];

        // Act
        $response = $this->withHeaders($headers)
                         ->getJson('dummy-test-route');

        // Assert
        $response->assertUnauthorized()
                 ->assertJsonStructure([
                     'message',
                 ]);
    }

    /**
     * @testdox Quando não for uma solicitação que espera JSON (com header de Accept: application/json) na requisição, então, uma exceção deve ser lançada
     */
    public function testAnExceptionShouldBeThrownWhenNotAJsonRequest()
    {
        // Arrange
        $headers = [
            'referer' => 'http://otherdomain.com',
        ];
        $this->withoutMiddleware([ForceJsonResponse::class]);

        // Assert
        $this->expectException(AuthenticationException::class);

        // Act
        $this->withoutExceptionHandling();
        $this->withHeaders($headers)->get('dummy-test-route');
    }

    /**
     * @testdox Quando não existir o header pra ser processado o middleware na requisição, então, uma exceção deve ser lançada
     */
    public function testAnExceptionShouldBeThrownWhenDontHaveHeaderInRequest()
    {
        // Arrange
        $this->withoutMiddleware([ForceJsonResponse::class]);

        // Assert
        $this->expectException(AuthenticationException::class);

        // Act
        $this->withoutExceptionHandling();
        $this->get('dummy-test-route');
    }

    /**
     * @testdox Quando a solicitação de origem se iniciar no mesmo domínio da aplicação, então, deve ser permitido e processado
     */
    public function testOriginRequestIsTheSameAsTheApplicationMustBeAllowed()
    {
        // Arrange
        $configKey = 'auth.cookies.stateful';
        $newDomainAllowed = 'otherdomain.com';
        $allowedDomains = config($configKey, []);

        // Act
        config([$configKey => array_merge($allowedDomains, [$newDomainAllowed])]);

        $response = $this->withHeaders(['referer' => $newDomainAllowed])
                         ->getJson('dummy-test-route');

        // Assert
        $response->assertOk()->assertJson(['nice']);
    }
}
