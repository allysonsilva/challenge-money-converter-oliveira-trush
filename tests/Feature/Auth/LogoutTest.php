<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

/**
 * @group Auth
 */
class LogoutTest extends TestCase
{
    /**
     * @testdox Logout da API foi realizado com sucesso
     *
     * @test
     */
    public function logoutSuccessfully(): void
    {
        $this->laravelEncrypterToCookieAuth();

        $response = $this->withSameDomain()
                         ->withCookie('api_token', json_encode(['user' => ['id' => 1]]))
                         ->deleteJson(route('api.v1.auth.logged.logout'));

        $response->assertNoContent()
                 ->assertCookieExpired('api_token')
                 ->assertCookieExpired('api_refresh');
    }
}
