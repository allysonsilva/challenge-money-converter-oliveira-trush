<?php

namespace App\API\Auth\Http\Controllers\Traits;

trait CookieAuth
{
    /**
     * Realiza o login do usuário utilizando o guard de cookie.
     *
     * @param array $credentials
     *
     * @return array|bool
     */
    protected function login(array $credentials): array|bool
    {
        if (! auth($driver = $this->authGuardName())->attempt($credentials)) {
            return false;
        }

        app('auth')->shouldUse($driver);

        return $this->respondWithUserData();
    }

    /**
     * Atualiza/gerar novos cookies de `token` e `refresh` adicionando-os
     * a resposta e consequentemente atualizando no navegador.
     *
     * @return array<\Symfony\Component\HttpFoundation\Cookie, \Symfony\Component\HttpFoundation\Cookie>
     */
    protected function refreshCookie(): array
    {
        return auth($this->authGuardName())->refresh();
    }

    /**
     * Remove os cookies de `token` e `refresh` do navegador.
     *
     * @return void
     */
    protected function logoutCookie(): void
    {
        auth($this->authGuardName())->logout();
    }

    /**
     * Retorna os dados de resposta do usuário após o login.
     *
     * @return array
     */
    protected function respondWithUserData(): array
    {
        $user = auth()->user();

        if (method_exists($this, 'additionalUserData')) {
            $additionalUserData = $this->additionalUserData($user); // @codeCoverageIgnore
        }

        $dataUser = [
            'name' => $user->name,
        ] + ($additionalUserData ?? []);

        return [
            'user' => $dataUser,
        ];
    }

    /**
     * Nome do guard de autenticação utilizado no `AuthManager` do Laravel.
     * Nome utilizado na config: `auth.guards.<nome-driver>`
     *
     * @return string
     */
    private function authGuardName(): string
    {
        return config('auth.cookies.guard.name');
    }
}
