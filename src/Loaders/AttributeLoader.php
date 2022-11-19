<?php

namespace Socodo\Router\Loaders;

use ReflectionAttribute;
use ReflectionException;
use ReflectionMethod;
use Socodo\Router\Attributes\Prefix;
use Socodo\Router\Attributes\Route;
use Socodo\Router\Exceptions\LoaderResolutionException;
use Socodo\Router\Interfaces\LoaderInterface;
use Socodo\Router\Interfaces\RouteCollectionInterface;
use Socodo\Router\RouteCollection;

class AttributeLoader implements LoaderInterface
{
    /** @var string Namespace prefix. */
    protected string $prefix;

    /** @var string|null App root path. */
    protected ?string $appRoot = null;

    /**
     * Construct.
     *
     * @param string $prefix
     */
    public function __construct (string $prefix)
    {
        $this->prefix = trim($prefix, '\\') . '\\';
    }

    /**
     * Load routes from PSR-4 classes which has Route attributes.
     *
     * @return RouteCollectionInterface
     * @throws ReflectionException
     */
    public function load (): RouteCollectionInterface
    {
        $collection = new RouteCollection();
        $classes = $this->findClasses();
        foreach ($classes as $className)
        {
            $class = new \ReflectionClass($className);
            if ($class->isAbstract())
            {
                continue;
            }

            $prefixPath = '';
            $prefixAttr = $class->getAttributes(Prefix::class, ReflectionAttribute::IS_INSTANCEOF);
            if (!empty($prefixAttr))
            {
                /** @var Prefix $prefixAttr */
                $prefixAttr = $prefixAttr[0]->newInstance();
                $prefixPath = $prefixAttr->getPrefix();
            }

            $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method)
            {
                $routeAttrs = $method->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF);
                foreach ($routeAttrs as $routeAttr)
                {
                    /** @var Route $attr */
                    $attr = $routeAttr->newInstance();
                    $route = $attr->getRoute();
                    $route->setController([ $className, $method->getName() ]);
                    $route->setPath($prefixPath . '/' . $route->getPath());

                    $collection->add($route);
                }
            }
        }

        return $collection;
    }

    /**
     * Get app root.
     *
     * @return string
     */
    public function getAppRoot (): string
    {
        if ($this->appRoot === null)
        {
            $this->appRoot = $this->findAppRoot();
        }

        return $this->appRoot;
    }

    /**
     * Set app root.
     *
     * @param string $appRoot
     * @return void
     */
    public function setAppRoot (string $appRoot): void
    {
        $this->appRoot = $appRoot;
    }

    /**
     * Find app root.
     *
     * @return string
     */
    protected function findAppRoot (): string
    {
        $dir = str_replace(DIRECTORY_SEPARATOR, '/', __DIR__);
        $dir = str_replace('/vendor/socodo/router/src/Loaders', '', $dir);
        $segments = explode('/', $dir);

        $appRoot = null;
        while ($appRoot === null && count ($segments) > 0)
        {
            $path = implode('/', $segments) . '/composer.json';
            if (file_exists($path))
            {
                $appRoot = implode('/', $segments) . '/';
            }
            else
            {
                array_pop($segments);
            }
        }

        if ($appRoot === null || !file_exists($appRoot . '/composer.json'))
        {
            throw new LoaderResolutionException('Socodo\\Router\\Loaders\\AttributeLoader::findAppRoot() Could not locate composer.json. You can manually set $appRoot with setAppRoot().');
        }

        return $appRoot;
    }

    /**
     * Find all classes.
     *
     * @return array
     */
    protected function findClasses (): array
    {
        $namespaces = $this->findAllApplicableNamespaces();
        if (empty($namespaces))
        {
            $best = $this->findBestPSR4Namespace();
            if ($best === null)
            {
                throw new LoaderResolutionException('Socodo\\Router\\Loaders\\AttributeLoader::findClasses() Could not find proper namespaces to search on.');
            }

            $namespaces = [ $best ];
        }

        $self = $this;
        return array_reduce($namespaces, static function (array $carry, array $namespace) use ($self) {
            $classes = $self->findChildClasses($namespace);
            return array_merge($carry, $classes);
        }, []);
    }

    /**
     * Get applicable namespaces from PSR-4.
     *
     * @return array{namespace: string, directories: array<string>}
     */
    protected function findAllApplicableNamespaces (): array
    {
        $prefix = $this->prefix;
        $namespaces = $this->getPSR4Namespaces();

        return array_filter($namespaces, static function (array $namespace) use ($prefix) {
            $ns = trim($namespace['namespace'], '\\');
            return str_starts_with($ns, $prefix);
        });
    }

    /**
     * Find the closest namespace from PSR-4.
     *
     * @return array{namespace: string, directories: array<string>}|null
     */
    protected function findBestPSR4Namespace(): ?array
    {
        $prefix = $this->prefix;
        $namespaces = $this->getPSR4Namespaces();
        $namespaces = array_filter($namespaces, static function (array $namespace) use ($prefix) {
            return str_starts_with($prefix, $namespace['namespace']);
        });

        $highestMatchingSegments = 0;
        return array_reduce($namespaces, static function (?array $carry, array $namespace) use ($prefix, &$highestMatchingSegments) {
            $matchedCount = 0;
            $segments = explode('\\', $namespace['namespace']);
            while (!empty($segments))
            {
                $imploded = implode('\\', $segments) . '\\';
                if (str_starts_with($prefix, $imploded))
                {
                    $matchedCount = count(explode('\\', $imploded)) - 1;
                    break;
                }
                array_pop($segments);
            }

            if ($matchedCount > $highestMatchingSegments)
            {
                $highestMatchingSegments = $matchedCount;
                $carry = $namespace;
            }

            return $carry;
        }, null);
    }

    /**
     * Get PSR-4 namespaces from composer autoload.
     *
     * @return array<array{namespace: string, directories: array<string>}>
     */
    protected function getPSR4Namespaces (): array
    {
        $appRoot = $this->getAppRoot();

        $autoloadPath = $appRoot . 'vendor/composer/autoload_psr4.php';
        $namespaces = require $autoloadPath;

        $output = [];
        foreach ($namespaces as $key => $value)
        {
            if (!is_array($value))
            {
                $value = [ $value ];
            }

            $output[] = [
                'namespace' => $key,
                'directories' => $value
            ];
        }
        return $output;
    }

    /**
     * Find child classes from namespace.
     *
     * @param array{namespace: string, directories: array<string>} $namespace
     * @return array
     */
    protected function findChildClasses (array $namespace): array
    {
        $directories = array_reduce($namespace['directories'], static function (array $carry, string $directory) {
            $path = array_diff(scandir($directory), [ '.', '..' ]);
            $path = array_map(static function (string $path) use ($directory) {
                return $directory. '/' . $path;
            }, $path);

            return array_merge($carry, $path);
        }, []);

        $self = $this;
        $classes = array_reduce($directories, static function (array $carry, string $path) use ($self, $namespace) {
            $segments = explode('/', $path);
            $lastSegment = array_pop($segments);

            $array = [];
            if (is_dir($path))
            {
                $subNamespace = [ 'namespace' => $namespace['namespace'] . $lastSegment . '\\', 'directories' => [ $path ]];
                $array = $self->findChildClasses($subNamespace);
            }

            elseif (str_ends_with($lastSegment, '.php'))
            {
                $class = $namespace['namespace'] . substr($lastSegment, 0, -4);
                if (!function_exists($class) && class_exists($class))
                {
                    $array = [ $class ];
                }
            }

            return array_merge($carry, $array);
        }, []);

        $prefix = $this->prefix
;       return array_filter($classes, static function (string $class) use ($prefix) {
            return str_starts_with($class, $prefix);
        });
    }
}