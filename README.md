# socodo/router
![GitHub](https://img.shields.io/github/license/socodo/router)

Router component for socodo.

## Getting Started

```shell
$ composer require socodo/router
```

```php
use Socodo\Router\RouteCollection;
use Socodo\Router\Route;
use Socodo\Http\Enums\HttpMethods;

$route = new Route(Httpmethods::GET, '/board/{slug}', 'AnyMixedTypedControllerToReceive');
$collection = new RouteCollection();
$collection->add($route);

/** @var \Psr\Http\Message\ServerRequestInterface $request */
$matched = $collection->match($request);
// $matched = [
//     'route' => $route,
//     'method' => HttpMethods::GET,
//     'controller' => 'AnyMixedTypedControllerToReceive',
//     'params' => [ 'slug' => 'foobar' ]
// ]
```

### With AttributeLoader

```php
namespace App\Controllers;

use Socodo\Router\Attributes\Get;

class FooController {
    #[Get('/foo/{bar}')]
    public function foo() { }
} 
```
```php
use Socodo\Router\Router;
use Socodo\Router\Loaders\AttributeLoader;

$loader = new AttributeLoader('App\\Controllers');
$router = new Router($loader);

/** @var \Psr\Http\Message\ServerRequestInterface $request */
$matched = $router->match($request);
// $matched = [
//     'route' => $route, // instance of Socodo\Router\Route
//     'method' => HttpMethods::GET,
//     'controller' => [ App\Controllers\FooController::class, 'foo' ],
//     'params' => [ 'bar' => 'test' ]
// ]
```