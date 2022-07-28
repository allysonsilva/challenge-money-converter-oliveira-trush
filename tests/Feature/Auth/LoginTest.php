<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;
// Run your tests in parallel - Not use [RefreshDatabase]
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group Auth
 */
class LoginTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @testdox Login realizado com sucesso
     *
     * @test
     */
    public function loginSuccessfully(): void
    {
        $this->laravelEncrypterToCookieAuth();

        $response = $this->withSameDomain()->postJson(route('api.v1.auth.login'), [
            'email' => 'alejandrin@example.com',
            'password' => 'password',
        ]);

        /** @var \Symfony\Component\HttpFoundation\Cookie */
        $apiTokenCookie = $response->getCookie('api_token');

        $response->assertOk()
                 ->assertCookieNotExpired('api_token')
                 ->assertJsonStructure(
                     ['user' => ['id', 'role'], 'expires'],
                     json_decode($apiTokenCookie->getValue(), true)
                 );
    }

    /**
     * @testdox Validação / FormRequest com os campos do login (email e password)
     *
     * @test
     */
    public function validateCredentials(): void
    {
        $response = $this->withSameDomain()->postJson(route('api.v1.auth.login'), [
            'email' => 'mail@mail.com',
            'password' => 'password',
        ]);

        /** @var \Illuminate\Validation\ValidationException */
        $exception = $response->exception;

        /** @var \Illuminate\Validation\Validator */
        $validator = $exception->validator;

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                 ->assertJsonValidationErrors(['email'], 'error.errors')
                 ->assertJsonStructure(['email' => ['Exists']], $validator->failed());

        $response = $this->withSameDomain()->postJson(route('api.v1.auth.login'), [
            'email' => 'alejandrin@example.com', // valid
            'password' => 'invalid', // invalid
        ]);

        $response->assertUnauthorized()
                 ->assertExactJson([
                     'error' => __('auth.failed'),
                 ]);
    }
}
