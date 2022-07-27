<?php

namespace Support\ThirdPartyServices\OpenExchangeRates\Traits;

use Closure;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Promise\FulfilledPromise;
use Illuminate\Http\Client\PendingRequest;

/**
 * phpcs:disable SlevomatCodingStandard.Classes.ClassStructure.IncorrectGroupOrder
 */
trait HandleRequest
{
    /**
     * Additional logic that will run before the exception is thrown.
     * `false` to disable throwing exceptions.
     *
     * @var \Closure|bool
     */
    protected Closure|bool $throw = false;

    /**
     * Options used in the guzzle client.
     *
     * @var array<string, mixed>
     */
    protected array $options = [
        'connect_timeout' => 5,
        'http_errors' => false,
        'timeout' => 10,
    ];

    /**
     * Laravel's wrapper around Guzzle.
     *
     * @var \Illuminate\Http\Client\PendingRequest
     */
    protected PendingRequest $client;

    /**
     * Response wrapper.
     *
     * @var \Illuminate\Http\Client\Response
     */
    protected Response $response;

    /**
     * Make an API request.
     *
     * @param string $path
     * @param array|string|null $query
     *
     * @return \Illuminate\Http\Client\Response|array|null
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    protected function makeRequest(string $path, array|string|null $query = null): Response|array|null
    {
        try {
            $this->initClient();

            $this->response = $this->client->get($path, $query);

            $this->response->throw($this->throw);

            return $this->response->json();
        } catch (Exception $exception) {
            if ($this->throw) {
                throw $exception;
            }
        }

        return null;
    }

    protected function initClient(): void
    {
        $this->client = Http::withOptions($this->options)
                            ->withToken($this->apiKey, 'Token')
                            ->acceptJson()
                            ->baseUrl($this->baseUrl);
    }

    /**
     * Additional Guzzle request options.
     *
     * @param array $options
     *
     * @return $this
     *
     * @codeCoverageIgnore
     */
    public function withOptions(array $options): static
    {
        $this->options = array_replace_recursive($this->options, $options);

        return $this;
    }

    /**
     * If you would like to perform some additional logic before the exception is thrown,
     * you may pass a closure to the throw method.
     *
     * @param callable(\Illuminate\Http\Client\Response $response, \Exception $e): void $callback
     */
    public function whenThrowException(?callable $callback = null)
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
        $this->throw = $callback ?: fn ($response, $e) => null;

        return $this;
    }

    /**
     * Proxies a fake call to Illuminate\Http\Client\Factory::fake()
     *
     * @param string $uri
     * @param \GuzzleHttp\Promise\FulfilledPromise $response
     * @param array<string, string> $query
     *
     * @return void
     */
    public function httpFake(string $uri, FulfilledPromise $response, array $query = []): void
    {
        $queryParams = http_build_query(array_merge($this->queryParams, $query));

        Http::preventStrayRequests();

        Http::fake([
            $this->baseUrl . $uri . '?' . $queryParams => $response,
        ]);
    }
}
