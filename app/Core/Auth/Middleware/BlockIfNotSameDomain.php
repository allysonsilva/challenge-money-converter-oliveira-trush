<?php

namespace Core\Auth\Middleware;

use Closure;
use Core\Auth\HandleDomain;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class BlockIfNotSameDomain
{
    /**
     * Create a new middleware instance.
     *
     * @param  \App\Auth\HandleDomain  $handleDomain
     *
     * @return void
     */
    public function __construct(private HandleDomain $handleDomain)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     *
     * @throws \Illuminate\Auth\AuthenticationException
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $urlDomain = $request->headers->get('referer') ?: $request->headers->get('origin');

        if (! $this->handleDomain->isSameDomain($urlDomain)) {
            $message = 'This action is unauthorized.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], SymfonyResponse::HTTP_UNAUTHORIZED);
            }

            throw new AuthenticationException($message);
        }

        return $next($request);
    }
}
