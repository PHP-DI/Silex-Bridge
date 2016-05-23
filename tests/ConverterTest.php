<?php

namespace DI\Bridge\Silex\Test;

use DI\ContainerBuilder;

use stdClass;
use DI\Bridge\Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConverterTest extends BaseTestCase
{
    /**
     * @test
     */
    public function should_allow_arbitrary_injection_in_converter()
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

        $request = Request::create('/john?some=param');

        $converter = function (Request $req, Application $app, stdClass $someService, $user) use ($application) {
            $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Request', $req);
            $this->assertEquals('param', $req->query->get('some'));
            $this->assertEquals($application, $app);
            $this->assertInstanceOf('stdClass', $someService);
            $this->assertAttributeEquals('bar', 'foo', $someService);
            $this->assertEquals($user, 'john');
            return ['name' => $user];
        };

        $handler = function (Request $r, array $user) {
            return 'Hello ' . $user['name'];
        };

        $application->get('/{user}', $handler)->convert('user', $converter);

        $response = $application->handle($request);
        $this->assertEquals('Hello john', $response->getContent());
    }

    /**
     * @test
     */
    public function should_fall_back_to_silex_default_behaviour()
    {
        $application = $this->createApplication();

        $request = Request::create('/john?some=param');

        $converter = function ($user, $req) use ($application) {
            $this->assertEquals($user, 'john');
            $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Request', $req);
            $this->assertEquals('param', $req->query->get('some'));
            return ['name' => $user];
        };

        $handler = function (Request $r, array $user) {
            return 'Hello ' . $user['name'];
        };

        $application->get('/{user}', $handler)->convert('user', $converter);

        $response = $application->handle($request);
        $this->assertEquals('Hello john', $response->getContent());
    }
}
