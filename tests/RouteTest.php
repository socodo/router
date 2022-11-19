<?php /** @noinspection PhpUndefinedClassInspection, PhpUndefinedNamespaceInspection */

namespace Tests\Unit;

use Codeception\Test\Unit;
use Socodo\Http\Enums\HttpMethods;
use Socodo\Router\Route;
use Tests\Support\UnitTester;

class RouteTest extends Unit
{
    /**
     * Route::getMethods()
     *
     * @return void
     */
    public function testGetMethods (): void
    {
        $route = new Route(HttpMethods::GET, '');
        $this->assertEquals([ HttpMethods::GET ], $route->getMethods());

        $route = new Route([ HttpMethods::GET, HttpMethods::POST ], '');
        $this->assertTrue(in_array(HttpMethods::GET, $route->getMethods()));
        $this->assertTrue(in_array(HttpMethods::POST, $route->getMethods()));
    }

    /**
     * Route::hasMethod()
     *
     * @return void
     */
    public function testHasMethod (): void
    {
        $route = new Route(HttpMethods::GET, '');
        $this->assertTrue($route->hasMethod(HttpMethods::GET));
        $this->assertFalse($route->hasMethod(HttpMethods::POST));

        $route = new Route([ HttpMethods::GET, HttpMethods::POST ], '');
        $this->assertTrue($route->hasMethod(HttpMethods::GET));
        $this->assertTrue($route->hasMethod(HttpMethods::POST));
        $this->assertFalse($route->hasMethod(HttpMethods::DEL));
    }

    /**
     * Route::setMethods()
     *
     * @return void
     */
    public function testSetMethods (): void
    {
        $route = new Route(HttpMethods::GET, '');
        $route->setMethods(HttpMethods::POST);
        $this->assertFalse(in_array(HttpMethods::GET, $route->getMethods()));
        $this->assertTrue(in_array(HttpMethods::POST, $route->getMethods()));

        $route->setMethods([ HttpMethods::GET, HttpMethods::POST ]);
        $this->assertTrue(in_array(HttpMethods::GET, $route->getMethods()));
        $this->assertTrue(in_array(HttpMethods::POST, $route->getMethods()));
    }

    /**
     * Route::addMethod()
     *
     * @return void
     */
    public function testAddMethod (): void
    {
        $route = new Route(HttpMethods::GET, '');
        $route->addMethod(HttpMethods::POST);
        $this->assertTrue(in_array(HttpMethods::POST, $route->getMethods()));

        $route->addMethod(HttpMethods::DEL);
        $this->assertTrue(in_array(HttpMethods::DEL, $route->getMethods()));
    }

    /**
     * Route::deleteMethod()
     *
     * @return void
     */
    public function testDeleteMethod (): void
    {
        $route = new Route([ HttpMethods::GET, HttpMethods::POST ], '');
        $route->deleteMethod(HttpMethods::GET);
        $this->assertFalse(in_array(HttpMethods::GET, $route->getMethods()));
        $this->assertTrue(in_array(HttpMethods::POST, $route->getMethods()));

        $route->deleteMethod(HttpMethods::POST);
        $this->assertFalse(in_array(HttpMethods::POST, $route->getMethods()));
    }

    /**
     * Route::getPath()
     *
     * @return void
     */
    public function testGetPath (): void
    {
        $route = new Route(HttpMethods::GET, '');
        $this->assertEquals('', $route->getPath());

        $route = new Route(HttpMethods::GET, '/');
        $this->assertEquals('', $route->getPath());

        $route = new Route(HttpMethods::GET, '/hello');
        $this->assertEquals('hello', $route->getPath());
    }

    /**
     * Route::setPath()
     *
     * @return void
     */
    public function testSetPath (): void
    {
        $route = new Route(HttpMethods::GET, '/hello');
        $route->setPath('/world');
        $this->assertEquals('world', $route->getPath());

        $route->setPath('');
        $this->assertEquals('', $route->getPath());

        $route->setPath('/');
        $this->assertEquals('', $route->getPath());
    }

    /**
     * Route::getController()
     *
     * @return void
     */
    public function testGetController (): void
    {
        $route = new Route(HttpMethods::GET, '', null);
        $this->assertNull($route->getController());

        $route = new Route(HttpMethods::GET, '', 'foobar');
        $this->assertEquals('foobar', $route->getController());

        $controller = (object) [ 'foo' => 'bar' ];
        $route = new Route(HttpMethods::GET, '', $controller);
        $this->assertSame($controller, $route->getController());
    }

    /**
     * Route::setController()
     *
     * @return void
     */
    public function testSetController (): void
    {
        $route = new Route(HttpMethods::GET, '');
        $route->setController('foobar');
        $this->assertEquals('foobar', $route->getController());

        $route->setController(null);
        $this->assertNull($route->getController());

        $route->setController([ 'foo' => 'bar' ]);
        $this->assertIsArray($route->getController());
        $this->assertArrayHasKey('foo', $route->getController());
    }

    /**
     * Route::compile()
     *
     * @return void
     */
    public function testCompile (): void
    {
        $route = new Route(HttpMethods::GET, '');
        $compile = $route->compile();
        $this->assertArrayHasKey('segments', $compile);
        $this->assertArrayHasKey('params', $compile);;

        $route->setPath('/welcome');
        $compile = $route->compile();
        $this->assertEquals([
            [ 'name' => 'welcome', 'type' => 'literal' ]
        ], $compile['segments']);

        $defaultRegex = '[a-zA-Z0-9-_~!+,*:@.]+';

        $route->setPath('/welcome/{back}');
        $compile = $route->compile();
        $this->assertEquals([
            'segments' => [
                [ 'name' => 'welcome', 'type' => 'literal' ],
                [ 'name' => 'back', 'type' => 'param' ]
            ],
            'params' => [
                'back' => $defaultRegex
            ]
        ], $compile);

        $route->setPath('/welcome/back/{user}');
        $compile = $route->compile();
        $this->assertEquals([
            'segments' => [
                [ 'name' => 'welcome', 'type' => 'literal' ],
                [ 'name' => 'back', 'type' => 'literal' ],
                [ 'name' => 'user', 'type' => 'param' ]
            ],
            'params' => [
                'user' => $defaultRegex
            ]
        ], $compile);

        $route->setPath('/welcome/{back}/{user}');
        $compile = $route->compile();
        $this->assertEquals([
            'segments' => [
                [ 'name' => 'welcome', 'type' => 'literal' ],
                [ 'name' => 'back', 'type' => 'param' ],
                [ 'name' => 'user', 'type' => 'param' ]
            ],
            'params' => [
                'back' => $defaultRegex,
                'user' => $defaultRegex
            ]
        ], $compile);

        $route->setPath('/welcome/{back:\\d+}');
        $compile = $route->compile();
        $this->assertEquals([
            'segments' => [
                [ 'name' => 'welcome', 'type' => 'literal' ],
                [ 'name' => 'back', 'type' => 'param' ],
            ],
            'params' => [
                'back' => '\\d+'
            ]
        ], $compile);
    }
}
