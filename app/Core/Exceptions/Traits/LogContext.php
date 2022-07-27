<?php

namespace Core\Exceptions\Traits;

/**
 * @see https://laravel.com/docs/9.x/errors#exception-log-context
 */
trait LogContext
{
    /**
     * @var array<mixed, mixed>
     */
    protected array $context = [];

    /**
     * Get the exception's context information.
     *
     * @return array
     */
    public function context(): array
    {
        return $this->context;
    }

    /**
     * @param array<string, mixed> $contextInformation
     *
     * @example
     *      (new InvalidOrderException("Message"))
     *      ->withContext([
     *          'order_id' => $this->orderId,
     *      ])->toss();
     */
    public function withContext(array $contextInformation): static
    {
        $this->context = $contextInformation;

        return $this;
    }
}
