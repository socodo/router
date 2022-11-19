<?php

namespace Socodo\Router\Loaders;

use Exception;
use Socodo\Http\Enums\HttpMethods;
use Socodo\Router\Exceptions\LoaderResolutionException;
use Socodo\Router\Interfaces\LoaderInterface;
use Socodo\Router\Interfaces\RouteCollectionInterface;
use Socodo\Router\Route;
use Socodo\Router\RouteCollection;

class JsonLoader implements LoaderInterface
{
    /** @var string JSON file path. */
    protected string $jsonPath;

    /** @var array JSON data. */
    protected array $json;

    /**
     * Constructor.
     *
     * @param string $jsonPath
     */
    public function __construct (string $jsonPath)
    {
        $this->jsonPath = $jsonPath;
        if (!file_exists($jsonPath) || !is_file($jsonPath))
        {
            throw new LoaderResolutionException('Socodo\\Router\\Loaders\\JsonLoader::__construct() Cannot locate JSON file "' . $jsonPath . '".');
        }

        $json = json_decode($jsonPath);
        if (json_last_error() !== JSON_ERROR_NONE)
        {
            throw new LoaderResolutionException('Socodo\\Router\\Loaders\\JsonLoader::__construct() Cannot parse JSON file "' . $jsonPath . '", throws ' . var_export(json_last_error()) . '.');
        }

        try
        {
            if (!is_array($json))
            {
                throw new Exception();
            }

            foreach ($json as $value)
            {
                if (!is_object($value))
                {
                    throw new Exception();
                }

                if (!isset($value->method) || !isset($value->path) || !isset($value->controller))
                {
                    throw new Exception();
                }
            }
        }
        catch (Exception)
        {
            throw new LoaderResolutionException('Socodo\\Router\\Loaders\\JsonLoader::__construct() Invalid JSON formatted route definitions.');
        }

        $this->json = $json;
    }

    /**
     * Load routes from JSON data.
     *
     * @return RouteCollectionInterface
     */
    public function load (): RouteCollectionInterface
    {
        $collection = new RouteCollection();
        foreach ($this->json as $value)
        {
            $route = new Route(HttpMethods::tryFrom($value->method), $value->path, $value->controller);
            $collection->add($route);
        }

        return $collection;
    }
}