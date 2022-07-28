<?php

namespace Core\Auth\Traits;

use DateTimeInterface;
use Illuminate\Support\Str;
use Illuminate\Cookie\CookieValuePrefix;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

trait HandleCookie
{
    /**
     * Names of cookies used in login and refresh.
     *
     * @var array<string, string>
     */
    protected array $cookiesName = [
        'token' => 'api_token',
        'refresh' => 'api_refresh',
    ];

    /**
     * Indicates if cookies should be serialized.
     *
     * @var bool
     */
    protected static bool $serialize = false;

    /**
     * Gera os cookies necessários para acesso / autenticação na API.
     *
     * @param string $expiresToken
     * @param string $expiresRefresh
     *
     * @return array<\Symfony\Component\HttpFoundation\Cookie, \Symfony\Component\HttpFoundation\Cookie>
     */
    protected function generateAccessCookies(?string $expiresToken = null, ?string $expiresRefresh = null): array
    {
        $expiresToken = strtotime($expiresToken ?? config('auth.cookies.expires.api_token'));
        $expiresRefresh = strtotime($expiresRefresh ?? config('auth.cookies.expires.api_refresh'));

        $payload = $this->getPayload();

        $payloadToken = array_merge($payload, ['expires' => $expiresToken]);
        $payloadRefresh = array_merge($payload, ['expires' => $expiresRefresh, 'refresh' => (string) Str::uuid()]);

        $cookieTokenName = $this->cookiesName['token'];
        $cookieRefreshName = $this->cookiesName['refresh'];

        $encryptedApiToken = $this->encryptCookie($cookieTokenName, $payloadToken);
        $encryptedRefreshToken = $this->encryptCookie($cookieRefreshName, $payloadRefresh);

        $apiToken = $this->makeCookie($cookieTokenName, $encryptedApiToken, $expiresToken, '/api/v1');
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $apiRefresh = $this->makeCookie($cookieRefreshName, $encryptedRefreshToken, $expiresRefresh, '/api/v1/auth/refresh');

        $this->getCookieJar()->queue($apiToken);
        $this->getCookieJar()->queue($apiRefresh);

        return [$apiToken, $apiRefresh];
    }

    /**
     * @param string $name
     * @param string $value
     * @param int|string|DateTimeInterface $expire
     * @param string $path
     *
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    protected function makeCookie(string $name, string $value, int|string|DateTimeInterface $expire = 0, string $path = '/'): SymfonyCookie // phpcs:ignore Generic.Files.LineLength.TooLong
    {
        $secure = false;
        if (app()->isProduction()) {
            $secure = true; // @codeCoverageIgnore
        }

        // phpcs:ignore Generic.Files.LineLength.TooLong, SlevomatCodingStandard.Functions.RequireMultiLineCall.RequiredMultiLineCall
        return new SymfonyCookie($name, $value, $expire, $path, secure: $secure, httpOnly: true, sameSite: SymfonyCookie::SAMESITE_STRICT);
    }

    /**
     * @param string $cookieName
     * @param mixed $value
     *
     * @return string
     */
    protected function encryptCookie(string $cookieName, mixed $value): string
    {
        $prefix = $this->getCookiePrefix($cookieName);
        $payload = $prefix . json_encode($value);

        return $this->encrypter->encrypt($payload, static::serialized());
    }

    /**
     * @param string $cookieName
     *
     * @return string
     */
    protected function getCookiePrefix(string $cookieName): string
    {
        return CookieValuePrefix::create($cookieName, $this->encrypter->getKey());
    }

    /**
     * Recupera o payload que será utilizado na criação e atualização dos cookies.
     *
     * @return array
     */
    protected function getPayload(): array
    {
        return [
            'user' => [
                'id' => $this->getUser()?->getAuthIdentifier() ?? $this->getLastAttempted()?->getAuthIdentifier(),
                'role' => $this->getUser()?->role ?? $this->getLastAttempted()?->role,
            ],
        ];
    }

    /**
     * Recupera o valor do cookie selecionado na requisição para acesso / login a API.
     *
     * @return array<string, string>|null
     */
    protected function getCookieSelectedInRequest(): ?array
    {
        foreach ($this->cookiesName as $cookieName) {
            if (! empty($cookieValue = $this->request->cookies->get($cookieName))) {
                return [$cookieName, $cookieValue];
            }
        }

        return null;
    }

    /**
     * Determine if the cookie contents should be serialized.
     *
     * @return bool
     */
    protected static function serialized(): bool
    {
        return static::$serialize;
    }
}
