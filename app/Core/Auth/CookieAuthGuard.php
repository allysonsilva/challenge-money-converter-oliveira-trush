<?php

namespace Core\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Encryption\Encrypter;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class CookieAuthGuard implements Guard
{
    use Macroable;
    // phpcs:ignore PSR12.Traits.UseDeclaration.MultipleImport
    use Traits\WithDependencies,
        Traits\HandleEvents,
        Traits\HandleCookie;
    use GuardHelpers {
        Traits\WithDependencies::setUser insteadof GuardHelpers;
    }

    /**
     * The name of the guard. Typically "web".
     *
     * Corresponds to guard name in authentication configuration.
     *
     * @var string
     */
    protected string $name = 'api';

    /**
     * The user we last attempted to retrieve.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable|null
     */
    protected AuthenticatableContract|null $lastAttempted;

    /**
     * Indicates if the logout method has been called.
     *
     * @var bool
     */
    protected bool $loggedOut = false;

    /**
     * Create a new guard instance.
     *
     * @param \Illuminate\Encryption\Encrypter $encrypter
     * @param \Illuminate\Contracts\Auth\UserProvider $provider
     *
     * @return void
     */
    public function __construct(protected Encrypter $encrypter, UserProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user(): AuthenticatableContract|null
    {
        if ($this->loggedOut) {
            return null;
        }

        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (! is_null($this->user)) {
            return $this->user;
        }

        if (empty([$cookieName, $cookieValue] = $this->getCookieSelectedInRequest())) {
            return null;
        }

        $decryptedCookieValue = $this->encrypter->decrypt($cookieValue, static::serialized());

        // Validate and remove the cookie value prefix from the value.
        $validatedValue = CookieValuePrefix::validate($cookieName, $decryptedCookieValue, $this->encrypter->getKey());

        $payload = json_decode($validatedValue, true);

        if ($this->user = $this->provider->retrieveById(data_get($payload, 'user.id'))) {
            $this->fireAuthenticatedEvent($this->user);
        }

        return $this->user;
    }

    /**
     * Validate a user's credentials.
     *
     * @param array<string, mixed> $credentials
     *
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        return $this->hasValidCredentials($user, $credentials);
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param array $credentials
     *
     * @return bool
     */
    public function attempt(array $credentials = []): bool
    {
        $this->fireAttemptEvent($credentials);

        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        // If an implementation of UserInterface was returned, we'll ask the provider
        // to validate the user against the given credentials, and if they are in
        // fact valid we'll log the users into the application and return true.
        if ($this->hasValidCredentials($user, $credentials)) {
            $this->login($user);

            return true;
        }

        // If the authentication attempt fails we will fire an event so that the user
        // may be notified of any suspicious attempts to access their account from
        // an unrecognized user. A developer may listen to this event as needed.
        $this->fireFailedEvent($user, $credentials);

        return false;
    }

    /**
     * Log the given user ID into the application.
     *
     * @param string|int $id
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|false
     */
    public function loginUsingId(string|int $id): AuthenticatableContract|bool
    {
        if (! is_null($user = $this->provider->retrieveById($id))) {
            $this->login($user);

            return $user;
        }

        return false;
    }

    /**
     * Log a user into the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     *
     * @return void
     */
    public function login(AuthenticatableContract $user): void
    {
        $this->setUser($user);

        $this->generateAccessCookies();

        $this->fireLoginEvent($user);
    }

    /**
     * Refresh the cookie values.
     *
     * @param string|null $expiresApi
     * @param string|null $expiresRefresh
     *
     * @return array<\Symfony\Component\HttpFoundation\Cookie, \Symfony\Component\HttpFoundation\Cookie>
     */
    public function refresh(?string $expiresApi = null, ?string $expiresRefresh = null): array
    {
        return $this->generateAccessCookies($expiresApi, $expiresRefresh);
    }

    /**
     * Log a user into the application without sessions or cookies.
     *
     * @param  array  $credentials
     *
     * @return bool
     */
    public function once(array $credentials = []): bool
    {
        $this->fireAttemptEvent($credentials);

        if ($this->validate($credentials)) {
            $this->setUser($this->lastAttempted);

            return true;
        }

        return false;
    }

    /**
     * Log the given user ID into the application without sessions or cookies.
     *
     * @param  string|int  $id
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|false
     */
    public function onceUsingId(string|int $id): AuthenticatableContract|bool
    {
        if (! is_null($user = $this->provider->retrieveById($id))) {
            $this->setUser($user);

            return $user;
        }

        return false;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     *
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function id(): int|string|null
    {
        if ($this->loggedOut) {
            return null;
        }

        return $this->user()?->getAuthIdentifier();
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout(): void
    {
        $user = $this->user();

        $cookies = [
            ['name' => $this->cookiesName['token'], 'path' => '/api/v1'],
            ['name' => $this->cookiesName['refresh'], 'path' => '/api/v1/auth/refresh'],
        ];

        foreach ($cookies as $cookie) {
            if (! $this->getCookieJar()->hasQueued($cookieName = $cookie['name'])) {
                $this->getCookieJar()->queue($this->getCookieJar()->forget($cookieName, $cookie['path']));
            }
        }

        // If we have an event dispatcher instance, we can fire off the logout event
        // so any further processing can be done. This allows the developer to be
        // listening for anytime a user signs out of this application manually.
        if (isset($this->events)) {
            $this->events->dispatch(new Logout($this->name, $user));
        }

        // Once we have fired the logout event we will clear the users out of memory
        // so they are no longer available as the user is no longer considered as
        // being signed into this application and should not be available here.
        $this->user = null;

        $this->loggedOut = true;
    }

    /**
     * Get the last user we attempted to authenticate.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function getLastAttempted(): AuthenticatableContract|null
    {
        return $this->lastAttempted ?? null;
    }

    /**
     * Determine if the user matches the credentials.
     *
     * @param  mixed  $user
     * @param  array  $credentials
     *
     * @return bool
     */
    private function hasValidCredentials($user, $credentials): bool
    {
        $validated = ! is_null($user) && $this->provider->validateCredentials($user, $credentials);

        if ($validated) {
            $this->fireValidatedEvent($user);
        }

        return $validated;
    }
}
