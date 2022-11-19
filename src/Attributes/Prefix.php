<?php

namespace Socodo\Router\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Prefix
{
    protected string $prefix;

    /**
     * Constructor.
     *
     * @param string $prefix
     */
    public function __construct (string $prefix = '')
    {
        $this->prefix = trim($prefix, '/');
    }

    /**
     * Get prefix.
     *
     * @return string
     */
    public function getPrefix (): string
    {
        return $this->prefix;
    }
}