<?php

namespace Socodo\Router\Interfaces;

use Psr\Http\Message\RequestInterface;

interface RouteCollectionInterface
{
    /**
     * Add a route.
     *
     * @param RouteInterface $route
     * @param int|null $priority
     * @return void
     */
    public function add (RouteInterface $route, ?int $priority = null): void;

    /**
     * Get all registered routes.
     *
     * @return array<RouteInterface>
     */
    public function getRoutes (): array;

    /**
     * Match the request.
     *
     * @param RequestInterface $request
     * @return array|null
     */
    public function match (RequestInterface $request): ?array;
}