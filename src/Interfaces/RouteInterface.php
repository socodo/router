<?php

namespace Socodo\Router\Interfaces;

use Socodo\Http\Enums\HttpMethods;

interface RouteInterface
{
    /**
     * Get methods.
     *
     * @return array<HttpMethods>
     */
    public function getMethods (): array;

    /**
     * Determine if the method set.
     *
     * @param HttpMethods $method
     * @return bool
     */
    public function hasMethod (HttpMethods $method): bool;

    /**
     * Set methods.
     *
     * @param HttpMethods|array $methods
     * @return void
     */
    public function setMethods (HttpMethods|array $methods): void;

    /**
     * Add method.
     *
     * @param HttpMethods $method
     * @return void
     */
    public function addMethod (HttpMethods $method): void;

    /**
     * Delete method.
     *
     * @param HttpMethods $method
     * @return void
     */
    public function deleteMethod (HttpMethods $method): void;

    /**
     * Get path.
     *
     * @return string
     */
    public function getPath (): string;

    /**
     * Set path.
     *
     * @param string $path
     * @return void
     */
    public function setPath (string $path): void;

    /**
     * Get controller.
     *
     * @return mixed
     */
    public function getController (): mixed;

    /**
     * Set controller.
     *
     * @param mixed $controller
     * @return void
     */
    public function setController (mixed $controller): void;

    /**
     * Compile the route.
     *
     * @return array{
     *              segments: array<int, array{ name: string, type: string }>,
     *              params: array<string, string>
     *          }
     */
    public function compile (): array;
}