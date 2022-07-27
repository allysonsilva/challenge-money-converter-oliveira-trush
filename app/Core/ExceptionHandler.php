<?php

declare(strict_types=1);

namespace Core;

use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as FoundationBaseHandler;

/**
 * phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint
 */
class ExceptionHandler extends FoundationBaseHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     *
     * @phpstan-var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array
     *
     * @phpstan-var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        // phpcs:ignore
        $this->reportable(function (Throwable $e) {
        });
    }

    /**
     * Convert the given exception to an array.
     *
     * @param  \Throwable  $e
     *
     * @return array{'error': array<mixed>}
     *
     * @codeCoverageIgnore
     *
     * @phpstan-ignore-next-line
     */
    protected function convertExceptionToArray(Throwable $e): array
    {
        return [
            'error' => parent::convertExceptionToArray($e),
        ];
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Validation\ValidationException  $exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception) // phpcs:ignore
    {
        return response()->json([
            'error' => [
                'message' => $exception->getMessage(),
                'type' => 'VALIDATION_EXCEPTION',
                'errors' => $exception->errors(),
            ],
        ], $exception->status);
    }
}
