<?php

namespace Socodo\Router;

use Socodo\Http\Enums\HttpMethods;
use Socodo\Router\Exceptions\InvalidParameterNameException;
use Socodo\Router\Exceptions\InvalidParameterValidationException;
use Socodo\Router\Interfaces\RouteInterface;
use TypeError;

class Route implements RouteInterface
{
    /** @var array<HttpMethods> Route methods. */
    protected array $methods = [];

    /** @var string Route path. */
    protected string $path = '';

    /** @var array|null Compiled data. */
    protected ?array $compiledData = null;

    /** @var mixed Controller */
    protected mixed $controller = null;

    /**
     * Constructor.
     *
     * @param HttpMethods|array<HttpMethods> $methods
     * @param string $path
     * @param mixed $controller
     */
    public function __construct (HttpMethods|array $methods, string $path, mixed $controller = null)
    {
        $this->setMethods($methods);
        $this->setPath($path);
        $this->setController($controller);
    }

    /**
     * Get methods.
     *
     * @return array<HttpMethods>
     */
    public function getMethods (): array
    {
        return $this->methods;
    }

    /**
     * Determine if the method set.
     *
     * @param HttpMethods $method
     * @return bool
     */
    public function hasMethod (HttpMethods $method): bool
    {
        return in_array($method, $this->methods);
    }

    /**
     * Set methods.
     *
     * @param HttpMethods|array<HttpMethods> $methods
     * @return void
     */
    public function setMethods (HttpMethods|array $methods): void
    {
        if (!is_array($methods))
        {
            $methods = [ $methods ];
        }

        foreach ($methods as $method)
        {
            if (!$method instanceof HttpMethods)
            {
                throw new TypeError('Socodo\Router\Route::setMethods Argument #1 ($methods) must be of type HttpMethods|array<HttpMethods>, array<mixed> given.');
            }
        }

        $this->methods = $methods;
    }

    /**
     * Add method.
     *
     * @param HttpMethods $method
     * @return void
     */
    public function addMethod (HttpMethods $method): void
    {
        $this->methods[] = $method;
        $this->methods = array_unique($this->methods, SORT_REGULAR);
    }

    /**
     * Delete method.
     *
     * @param HttpMethods $method
     * @return void
     */
    public function deleteMethod (HttpMethods $method): void
    {
        if (!in_array($method, $this->methods))
        {
            return;
        }

        $this->methods = array_udiff($this->methods, [ $method ], static fn ($a, $b) => $a == $b ? 0 : -1);
    }

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

    /**
     * Get controller.
     *
     * @return mixed
     */
    public function getController (): mixed
    {
        return $this->controller;
    }

    /**
     * Set controller.
     *
     * @param mixed $controller
     * @return void
     */
    public function setController (mixed $controller): void
    {
        $this->controller = $controller;
    }

    /**
     * Compile the route.
     *
     * @return  array{
     *              segments: array<int, array{ name: string, type: string }>,
     *              params: array<string, string>
     *          }
     * @throws InvalidParameterNameException
     * @throws InvalidParameterValidationException
     */
    public function compile (): array
    {
        if ($this->compiledData === null)
        {
            $segments = [];
            $params = [];

            $pathSegments = explode('/', $this->getPath());
            if ($pathSegments == [''])
            {
                return $this->compiledData = [ 'segments' => [], 'params' => [] ];
            }

            foreach ($pathSegments as $segment)
            {
                if (!str_starts_with($segment, '{'))
                {
                    $segments[] = [ 'name' => $segment, 'type' => 'literal' ];
                    continue;
                }

                $data = explode(':', substr($segment, 1, -1), 2);
                if (preg_match('/[a-zA-Z_]+[a-zA-Z0-9_]*]/', $data[0]) === false)
                {
                    throw new InvalidParameterNameException('Socodo\Router\Route::compile() Route parameter name must be compatible with PHP variable name convention, "' . $data[0] . '" given.');
                }

                if (isset($params[$data[0]]) && $params[$data[0]] !== $data[1])
                {
                    throw new InvalidParameterValidationException('Socodo\Router\Route::compile() Duplicated parameter must have same validation string, name "' . $data[0] . '" expects "' . $params[$data[0]], '", "' . $data[1] . '" given.');
                }

                if (isset($data[1]))
                {
                    set_error_handler(static function(){});
                    $isValidRegex = preg_match('/' . $data[1] . '/', '');
                    restore_error_handler();

                    if ($isValidRegex === false)
                    {
                        throw new InvalidParameterValidationException('Socodo\Router\Route::compile() Validation string of parameter must be a valid regex string, "' . $data[1] . '" given.');
                    }
                }
                else
                {
                    $data[1] = '[a-zA-Z0-9-_~!+,*:@.]+';
                }

                $segments[] = [ 'name' => $data[0], 'type' => 'param' ];
                $params[$data[0]] = $data[1];
            }

            $this->compiledData = [ 'segments' => $segments, 'params' => $params ];
        }

        return $this->compiledData;
    }
}