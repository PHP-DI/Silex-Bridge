<?php

namespace DI\Bridge\Silex\Test;

use DI\ContainerBuilder;
use Interop\Container\ContainerInterface;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

class FunctionalTest extends BaseTestCase
{
    /**
     * @test
     */
    public function should_dispatch_get()
    {
        $app = $this->createApplication();

        $app->get('/foo', function () {
            return 'Hello';
        });

        $response = $app->handle(Request::create('/foo'));
        $this->assertEquals('Hello', $response->getContent());
    }

    /**
     * @test
     */
    public function should_resolve_controllers_from_the_container()
    {
        $app = $this->createApplication();

        $app->get('/foo', 'DI\Bridge\Silex\Test\Fixture\InvokableController');

        $response = $app->handle(Request::create('/foo'));
        $this->assertEquals('Hello world', $response->getContent());
    }

    /**
     * @test
     */
    public function should_resolve_array_controllers()
    {
        $app = $this->createApplication();

        $app->get('/foo', ['DI\Bridge\Silex\Test\Fixture\Controller', 'home']);

        $response = $app->handle(Request::create('/foo'));
        $this->assertEquals('Hello world', $response->getContent());
    }

    /**
     * @test
     */
    public function should_pass_url_placeholders()
    {
        $app = $this->createApplication();

        $app->get('/{name}', ['DI\Bridge\Silex\Test\Fixture\Controller', 'hello']);

        $response = $app->handle(Request::create('/john'));
        $this->assertEquals('Hello john', $response->getContent());
    }

    /**
     * @test
     */
    public function should_pass_request_object_by_parameter_name()
    {
        $app = $this->createApplication();

        $app->get('/', function ($request) {
            return 'Hello ' . $request->get('name');
        });

        $response = $app->handle(Request::create('/?name=john'));
        $this->assertEquals('Hello john', $response->getContent());
    }

    /**
     * @test
     */
    public function should_pass_phpdi_service_based_on_type_hint()
    {
        $builder = new ContainerBuilder;
        $builder->addDefinitions([
            'stdClass' => function () {
                $service = new stdClass;
                $service->foo = 'bar';
                return $service;
            },
        ]);

        $app = $this->createApplication($builder);

        $app->get('/', function (stdClass $param) {
            return $param->foo;
        });

        $response = $app->handle(Request::create('/'));
        $this->assertEquals('bar', $response->getContent());
    }

    /**
     * @test
     */
    public function should_pass_pimple_service_based_on_type_hint()
    {
        $app = $this->createApplication();

        $service = new stdClass;
        $service->foo = 'bar';
        $app['stdClass'] = $service;

        $app->get('/', function (stdClass $param) {
            return $param->foo;
        });

        $response = $app->handle(Request::create('/'));
        $this->assertEquals('bar', $response->getContent());
    }

    /**
     * @test
     */
    public function should_pass_the_container_based_on_type_hint()
    {
        $builder = new ContainerBuilder;
        $builder->addDefinitions([
            'foo' => 'bar',
        ]);

        $app = $this->createApplication($builder);

        $app->get('/', function (ContainerInterface $container) {
            return $container->get('foo');
        });

        $response = $app->handle(Request::create('/'));
        $this->assertEquals('bar', $response->getContent());
    }

    /**
     * @test
     */
    public function should_pass_the_silex_application_based_on_type_hint()
    {
        $app = $this->createApplication();
        $app['foo'] = 'bar';

        $app->get('/', function (\Silex\Application $a) {
            return $a['foo'];
        });

        $response = $app->handle(Request::create('/'));
        $this->assertEquals('bar', $response->getContent());
    }

    /**
     * @test
     */
    public function should_pass_the_own_application_based_on_type_hint()
    {
        $app = new \DI\Bridge\Silex\Test\Fixture\Application(null, [
            'foo' => 'bar',
        ]);

        $app->get('/', function (\DI\Bridge\Silex\Test\Fixture\Application $a) {
            return $a['foo'];
        });

        $response = $app->handle(Request::create('/'));
        $this->assertEquals('bar', $response->getContent());
    }

    /**
     * @test
     */
    public function should_pass_request_object_based_on_type_hint()
    {
        $app = $this->createApplication();

        $app->get('/', function (Request $r) {
            return 'Hello ' . $r->get('name');
        });

        $response = $app->handle(Request::create('/?name=john'));
        $this->assertEquals('Hello john', $response->getContent());
    }
}
