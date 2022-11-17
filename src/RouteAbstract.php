<?php

namespace Socodo\Router;

abstract class RouteAbstract
{
    /** @var string Route path. */
    protected string $path = '';

    /**
     * Get path.
     *
     * @return string
     */
    public function getPath (): string
    {
        return $this->path;
    }

    /**
     * Set path.
     * @param string $path
     * @return void
     */
    public function setPath (string $path): void
    {
        $this->path = trim($path, '/');
    }
}