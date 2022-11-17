<?php

namespace Socodo\Router\Attributes;

use Attribute;
use Socodo\Http\Enums\HttpMethods;

#[Attribute(Attribute::IS_REPEATABLE|Attribute::TARGET_METHOD)]
class Route
{
    protected \Socodo\Router\Route $route;

    /**
     * Constructor.
     *
     * @param HttpMethods|array<HttpMethods> $methods
     * @param string $path
     * @param string $host
     */
    public function __construct (HttpMethods|array $methods, string $path, string $host = '')
    {
        $this->route = new \Socodo\Router\Route($methods, $path, $host);
    }

    /**
     * Get Route instance.
     *
     * @return \Socodo\Router\Route
     */
    public function getRoute (): \Socodo\Router\Route
    {
        return $this->route;
    }
}