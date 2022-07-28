<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

/**
 * @group Auth
 */
class RefreshTest extends TestCase
{
    /**
     * @testdox Atualização da sessão / cookie foi realizada com sucesso
     *
     * @test
     */
    public function refreshSuccessfully(): void
    {
        $this->laravelEncrypterToCookieAuth();

        $response = $this->withSameDomain()
                         ->withCookie('api_refresh', json_encode(['user' => ['id' => 1]]))
                         ->putJson(route('api.v1.auth.logged.refresh'));

        $response->assertOk()
                ->assertCookieNotExpired('api_token')
                ->assertCookieNotExpired('api_refresh');
    }
}
