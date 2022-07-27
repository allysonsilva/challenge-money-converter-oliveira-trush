<?php

namespace Core\Exceptions;

use Throwable;
use ReflectionClass;
use JsonSerializable;
use RuntimeException;
use Illuminate\Support\Arr;
use Core\Exceptions\Traits\LogContext;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Core\Exceptions\Traits\ReportableERenderable;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

abstract class HttpException extends RuntimeException implements Jsonable, JsonSerializable, Arrayable, HttpExceptionInterface
{
    use LogContext;
    use ReportableERenderable;

    protected readonly int $statusCode;

    /**
     * @var array<string, mixed>
     */
    protected array $headers;

    /**
     * Type, exception identity code.
     *
     * @return string
     */
    abstract protected function getType(): string;

    public function __construct(int $statusCode, string $message = '', array $headers = [], int $code = 0, ?Throwable $previous = null)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Headers that will be added in the response.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Response HTTP status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Fails with the current exception object.
     *
     * @throws \Core\Exceptions\BaseException
     */
    public function toss(): void
    {
        throw $this;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0): string // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint
    {
        return json_encode($this->jsonSerialize(), $options | JSON_THROW_ON_ERROR) ?: '';
    }

    /**
     * Return the Exception as an array.
     *
     * @return array{
     *              status: int, 'message': string, 'type': string,
     *              line: int, file: string, 'exception': class-string<\Throwable>,
     *              context: array<mixed>, trace?: array<mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'error' => [
                'type' => $this->getType(),
                'message' => $this->getMessage(),
                'status' => $this->getStatusCode(),
                'context' => $this->context(),
            ] + (function (): array {
                if (config('app.debug')) {
                    return [
                        'exception' => (new ReflectionClass($this))->getName(),
                        'file' => $this->getFile(),
                        'line' => $this->getLine(),
                        'trace' => collect($this->getTrace())->map(fn ($trace) => Arr::except($trace, ['args']))->all(),
                    ];
                }

                return [];
            })(),
        ];
    }
}
