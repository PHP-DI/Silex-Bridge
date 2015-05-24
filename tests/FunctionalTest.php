<?php

namespace DI\Bridge\Silex\Test;

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

        $app->get('/foo', array('DI\Bridge\Silex\Test\Fixture\Controller', 'home'));

        $response = $app->handle(Request::create('/foo'));
        $this->assertEquals('Hello world', $response->getContent());
    }

    /**
     * @test
     */
    public function should_pass_url_placeholders()
    {
        $app = $this->createApplication();

        $app->get('/{name}', array('DI\Bridge\Silex\Test\Fixture\Controller', 'hello'));

        $response = $app->handle(Request::create('/john'));
        $this->assertEquals('Hello john', $response->getContent());
    }

    /**
     * @test
     */
    public function should_pass_query_parameters()
    {
        $app = $this->createApplication();

        $app->get('/', array('DI\Bridge\Silex\Test\Fixture\Controller', 'hello'));

        $response = $app->handle(Request::create('/?name=john'));
        $this->assertEquals('Hello john', $response->getContent());
    }

    /**
     * @test
     */
    public function should_pass_post_data()
    {
        $app = $this->createApplication();

        $app->post('/', ['DI\Bridge\Silex\Test\Fixture\Controller', 'hello']);

        $response = $app->handle(Request::create('/', 'POST', [
            'name' => 'john',
        ]));
        $this->assertEquals('Hello john', $response->getContent());
    }
}
