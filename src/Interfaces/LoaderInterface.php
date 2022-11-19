<?php

namespace Socodo\Router\Interfaces;

interface LoaderInterface
{
    public function load (): RouteCollectionInterface;
}