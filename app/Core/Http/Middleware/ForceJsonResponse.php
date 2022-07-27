<?php

namespace Core\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource as APIResource;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        if (! $response instanceof JsonResponse &&
            ! $response instanceof APIResource) {
                $response->headers->set('Content-Type', 'application/json');

                // return new JsonResponse($response->content(), $response->status(), $response->headers->all());
        }

        return $response;
    }
}
