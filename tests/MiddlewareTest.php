<?php

namespace DI\Bridge\Silex\Test;

use DI\ContainerBuilder;

use stdClass;
use DI\Bridge\Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MiddlewareTest extends BaseTestCase
{
    /**
     * @test
     */
    public function should_allow_arbitrary_injection_in_middleware()
    {
        $builder = new ContainerBuilder;
        $builder->addDefinitions([
            'stdClass' => function () {
                $service = new stdClass;
                $service->foo = 'bar';
                return $service;
            },
        ]);

        $application = $this->createApplication($builder);

        $request = Request::create('/?name=john');

        $afterMiddleware = function (Application $app, Response $res, Request $req, stdClass $someService) use ($application) {
            $this->assertEquals($application, $app);
            $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $res);
            $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Request', $req);
            $this->assertEquals('john', $req->query->get('name'));
            $this->assertInstanceOf('stdClass', $someService);
            $this->assertAttributeEquals('bar', 'foo', $someService);
        };

        $beforeMiddleware = function (Application $app, Request $req, stdClass $someService) use ($application) {
            $this->assertEquals($application, $app);
            $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Request', $req);
            $this->assertEquals('john', $req->query->get('name'));
            $this->assertInstanceOf('stdClass', $someService);
            $this->assertAttributeEquals('bar', 'foo', $someService);
        };

        $handler = function (Request $r) {
            return 'Hello ' . $r->get('name');
        };

        // route middleware
        $application->get('/', $handler)->before($beforeMiddleware)->after($afterMiddleware);

        // application middleware
        $application->before($beforeMiddleware);
        $application->after($afterMiddleware);
        $application->finish($afterMiddleware);

        $response = $application->handle($request);
        $this->assertEquals('Hello john', $response->getContent());
    }

    /**
     * @test
     */
    public function should_fall_back_to_silex_default_behaviour()
    {
        $application = $this->createApplication();

        $request = Request::create('/?name=john');

        $beforeMiddleware = function ($req, $app) use ($application) {
            $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Request', $req);
            $this->assertEquals('john', $req->query->get('name'));
            $this->assertEquals($application, $app);
        };

        $afterMiddleware = function ($req, $res, $app) use ($application) {
            $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Request', $req);
            $this->assertEquals('john', $req->query->get('name'));
            $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $res);
            $this->assertEquals($application, $app);
        };

        $handler = function (Request $req) {
            return 'Hello ' . $req->get('name');
        };

        // route middleware
        $application->get('/', $handler)->before($beforeMiddleware)->after($afterMiddleware);

        // application middleware
        $application->before($beforeMiddleware);
        $application->after($afterMiddleware);
        $application->finish($afterMiddleware);

        $response = $application->handle($request);
        $this->assertEquals('Hello john', $response->getContent());
    }
}
