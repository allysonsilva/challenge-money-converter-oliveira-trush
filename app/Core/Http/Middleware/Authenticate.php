<?php

namespace Core\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return string|null
     */
    protected function redirectTo($request): ?string // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint
    {
        if (! $request->expectsJson()) {
            return url('/');
        }

        return null;
    }
}
