<?php

namespace Socodo\Router\Attributes;

use Attribute;
use Socodo\Router\RoutePrefix;

#[Attribute(Attribute::IS_REPEATABLE|Attribute::TARGET_CLASS)]
class Prefix
{
    protected RoutePrefix $prefix;

    /**
     * Constructor.
     *
     * @param string $path
     * @param string $host
     */
    public function __construct (string $path = '/', string $host = '')
    {
        $this->prefix = new RoutePrefix($path, $host);
    }

    /**
     * Get RoutePrefix instance.
     *
     * @return RoutePrefix
     */
    public function getRoutePrefix (): RoutePrefix
    {
        return $this->prefix;
    }
}