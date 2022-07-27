<?php

namespace Support\Http\Routing;

use Illuminate\Routing\Router;
use Illuminate\Routing\RouteRegistrar;

abstract class RouteFile
{
    /**
     * @var array<mixed>
     */
    private array $options;

    /**
     * @var \Illuminate\Routing\Router|\Illuminate\Routing\RouteRegistrar
     */
    private Router|RouteRegistrar $router;

    abstract protected function routes(Router $router): void;

    /**
     * @param array<mixed> $options
     */
    public function __construct(array $options = [])
    {
        $this->router = app(Router::class);

        $this->options = $options;
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     *
     * @return void
     *
     * @phpstan-ignore-next-line
     */
    public function registerRouteGroups(): void
    {
        if ($this->router instanceof RouteRegistrar) {
            $this->router->group(function ($router): void {
                $this->routes($router);
            });
        // @phpstan-ignore-next-line
        } else {
            // @codeCoverageIgnoreStart
            $this->router->group($this->options, function ($router): void {
                $this->routes($router);
            });
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Dynamically handle calls into the router instance.
     *
     * @param string $method
     * @param array<mixed> $parameters
     *
     * @return $this
     */
    public function __call(string $method, array $parameters): self
    {
        $this->router = call_user_func_array([$this->router, $method], $parameters);

        return $this;
    }
}
