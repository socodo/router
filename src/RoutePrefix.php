<?php

namespace Socodo\Router;

class RoutePrefix extends RouteAbstract
{
    /** @var array<Route> Children routes. */
    protected array $children = [];

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct (string $path = '')
    {
        $this->setPath($path);
    }

    /**
     * Get all children.
     *
     * @return array<Route>
     */
    public function getChildren (): array
    {
        return $this->children;
    }

    /**
     * Add child.
     *
     * @param Route $child
     * @return void
     */
    public function addChild (Route $child): void
    {
        $this->children[] = $child;
    }
}