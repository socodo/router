<?php

namespace Socodo\Router;

use Psr\Http\Message\RequestInterface;
use Socodo\Http\Enums\HttpMethods;

class RouteCollection
{
    /** @var int Index. */
    protected int $index = 0;

    /** @var array<Route> Route instances. */
    protected array $routes = [];

    /** @var array<string, int> Priority data. */
    protected array $priorities = [];

    /**
     * Add a route.
     *
     * @param RouteAbstract $route
     * @param ?int $priority
     * @return void
     */
    public function add (RouteAbstract $route, ?int $priority = null): void
    {
        if ($route instanceof RoutePrefix)
        {
            foreach ($route->getChildren() as $child)
            {
                $child->setPath($route->getPath() . '/' . $child->getPath());
                $this->add($child, $priority);
            }
            return;
        }

        $index = $this->index++;
        $this->routes[$index] = $route;

        if ($priority !== null)
        {
            $this->priorities[$index] = $priority;
        }
    }

    /**
     * Get all registered routes.
     *
     * @return array<Route>
     */
    public function getRoutes (): array
    {
        if (!empty($this->priorities))
        {
            $priorities = $this->priorities;
            $keyOrders = array_flip(array_keys($this->routes));
            uksort($this->routes, static function ($a, $b) use ($priorities, $keyOrders) {
                return (
                    ($priorities[$b] ?? 0) <=> ($priorities[$a] ?? 0) ?: ($keyOrders[$a] <=> $keyOrders[$b])
                );
            });
        }

        return $this->routes;
    }

    /**
     * Match the request.
     *
     * @param RequestInterface $request
     * @return array|null
     */
    public function match (RequestInterface $request): ?array
    {
        return $this->matchPath($request->getMethod(), $request->getRequestTarget());
    }

    /**
     * Match the request with raw string data.
     *
     * @param string $method
     * @param string $path
     * @return array|null
     */
    protected function matchPath (string $method, string $path): ?array
    {
        $method = strtoupper($method);
        $path = trim($path, '/');
        $segments = array_filter(explode('/', $path), static fn (string $item): bool => ($item !== ''));
        $segmentCount = count($segments);

        $routes = $this->getRoutes();
        foreach ($routes as $route)
        {
            if (!in_array(HttpMethods::tryFrom($method), $route->getMethods()) && !($method == 'HEAD' && in_array(HttpMethods::GET, $route->getMethods())))
            {
                continue;
            }

            $compiled = $route->compile();
            if (count($compiled['segments']) != $segmentCount)
            {
                continue;
            }

            $parameters = [];
            foreach ($segments as $i => $segment)
            {
                $segmentValue = $compiled['segments'][$i]['name'];
                $segmentType = $compiled['segments'][$i]['type'];

                if ($segmentType == 'literal')
                {
                    if ($segment != $segmentValue)
                    {
                        $parameters = null;
                        break;
                    }
                    continue;
                }

                if ($segmentType == 'param')
                {
                    if (isset($parameters[$segmentValue]) && $parameters[$segmentValue] != $segment)
                    {
                        $parameters = null;
                        break;
                    }

                    $validation = $compiled['params'][$segmentValue];
                    if (preg_match('/^' . $validation . '$/', $segment) !== 1)
                    {
                        $parameters = null;
                        break;
                    }

                    $parameters[$segmentValue] = $segment;
                }
            }

            if ($parameters !== null)
            {
                return [
                    'route' => $route,
                    'method' => $method,
                    'controller' => $route->getController(),
                    'params' => $parameters
                ];
            }
        }

        return null;
    }
}