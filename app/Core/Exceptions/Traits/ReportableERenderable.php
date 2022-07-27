<?php

namespace Core\Exceptions\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @see https://laravel.com/docs/9.x/errors#renderable-exceptions
 */
trait ReportableERenderable
{
    /**
     * Report the exception.
     *
     * @return bool|null
     */
    public function report(): ?bool
    {
        return null;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function render(Request $request) // phpcs:ignore
    {
        $payload = [
            'error' => [
                'type' => $this->getType(),
                'message' => $this->getMessage(),
            ],
        ];

        if (! app()->isProduction()) {
            $payload = $this->toArray();
        }

        return new JsonResponse(
            $payload,
            $this->getStatusCode(),
            $this->getHeaders(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }
}
