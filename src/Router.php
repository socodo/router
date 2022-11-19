<?php

namespace Socodo\Router;

use Psr\Http\Message\RequestInterface;
use Socodo\Http\Enums\HttpMethods;
use Socodo\Router\Interfaces\LoaderInterface;
use Socodo\Router\Interfaces\RouteCollectionInterface;

class Router
{
    /** @var LoaderInterface Loader. */
    protected LoaderInterface $loader;

    /** @var RouteCollectionInterface Collection */
    protected RouteCollectionInterface $collection;

    /**
     * Constructor.
     */
    public function __construct (LoaderInterface $loader)
    {
        $this->loader = $loader;
        $this->collection = $loader->load();
    }

    /**
     * Get route collection.
     *
     * @return RouteCollectionInterface
     */
    public function getCollection (): RouteCollectionInterface
    {
        return $this->collection;
    }

    /**
     * Match the request.
     *
     * @param RequestInterface $request
     * @return ?array{
     *              route: Route,
     *              method: ?HttpMethods,
     *              controller: mixed,
     *              params: array<string, string>
     *          }
     */
    public function match (RequestInterface $request): ?array
    {
        return $this->collection->match($request);
    }
}