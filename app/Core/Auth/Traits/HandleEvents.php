<?php

namespace Core\Auth\Traits;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Validated;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

trait HandleEvents
{
    /**
     * Fire the login event if the dispatcher is set.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     *
     * @return void
     */
    private function fireLoginEvent(AuthenticatableContract $user): void
    {
        if (isset($this->events)) {
            $this->events->dispatch(new Login($this->name, $user, false));
        }
    }

    /**
     * Fire the authenticated event if the dispatcher is set.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     *
     * @return void
     */
    private function fireAuthenticatedEvent(AuthenticatableContract $user): void
    {
        if (isset($this->events)) {
            $this->events->dispatch(new Authenticated($this->name, $user));
        }
    }

    /**
     * Fire the attempt event with the arguments.
     *
     * @param  array  $credentials
     *
     * @return void
     */
    private function fireAttemptEvent(array $credentials): void
    {
        if (isset($this->events)) {
            $this->events->dispatch(new Attempting($this->name, $credentials, false));
        }
    }

    /**
     * Fire the failed authentication attempt event with the given arguments.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|null  $user
     * @param  array  $credentials
     *
     * @return void
     */
    private function fireFailedEvent(AuthenticatableContract|null $user, array $credentials): void
    {
        if (isset($this->events)) {
            $this->events->dispatch(new Failed($this->name, $user, $credentials));
        }
    }

    /**
     * Fires the validated event if the dispatcher is set.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     *
     * @return void
     */
    private function fireValidatedEvent(AuthenticatableContract $user): void
    {
        if (isset($this->events)) {
            $this->events->dispatch(new Validated($this->name, $user));
        }
    }
}
