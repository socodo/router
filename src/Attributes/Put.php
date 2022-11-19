<?php

namespace Socodo\Router\Attributes;

use Attribute;
use Socodo\Http\Enums\HttpMethods;

#[Attribute(Attribute::IS_REPEATABLE|Attribute::TARGET_METHOD)]
class Put extends Route
{
    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct (string $path)
    {
        parent::__construct(HttpMethods::PUT, $path);
    }
}