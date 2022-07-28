<?php

namespace Core\Auth\Traits;

use RuntimeException;
use Illuminate\Http\Request;
use Illuminate\Cookie\CookieJar;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

trait WithDependencies
{
    /**
     * The Illuminate cookie creator service.
     *
     * @var \Illuminate\Cookie\CookieJar
     */
    protected CookieJar $cookie;

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected Dispatcher $events;

    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected Request $request;

    /**
     * Return the currently cached user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function getUser(): AuthenticatableContract|null
    {
        return $this->user();
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     *
     * @return $this
     */
    public function setUser(AuthenticatableContract $user)
    {
        $this->user = $user;

        $this->loggedOut = false;

        $this->fireAuthenticatedEvent($user);

        return $this;
    }

    /**
     * Get the cookie creator instance used by the guard.
     *
     * @return \Illuminate\Cookie\CookieJar
     *
     * @throws \RuntimeException
     *
     * @codeCoverageIgnore
     */
    public function getCookieJar(): CookieJar
    {
        if (! isset($this->cookie)) {
            throw new RuntimeException('Cookie jar has not been set.');
        }

        return $this->cookie;
    }

    /**
     * Set the cookie creator instance used by the guard.
     *
     * @param \Illuminate\Cookie\CookieJar $cookie
     *
     * @return void
     */
    public function setCookieJar(CookieJar $cookie): void
    {
        $this->cookie = $cookie;
    }

    /**
     * Get the event dispatcher instance.
     *
     * @return \Illuminate\Contracts\Events\Dispatcher
     */
    public function getDispatcher(): Dispatcher
    {
        return $this->events;
    }

    /**
     * Set the event dispatcher instance.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     *
     * @return void
     */
    public function setDispatcher(Dispatcher $events): void
    {
        $this->events = $events;
    }

    /**
     * Get the current request instance.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest(): Request
    {
        return $this->request ?: Request::createFromGlobals();
    }

    /**
     * Set the current request instance.
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     *
     * @return $this
     *
     * @codeCoverageIgnore
     */
    public function setRequest(Request $request): static
    {
        $this->request = $request;

        return $this;
    }
}
