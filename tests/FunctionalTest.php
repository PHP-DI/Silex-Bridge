<?php

namespace DI\Bridge\Silex\Test;

use DI\Bridge\Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class FunctionalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_run()
    {
        $app = new Application();

        $app->handle(new Request());
    }

    /**
     * @test
     */
    public function should_dispatch_get()
    {
        $app = new Application();

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
        $app = new Application();

        $app->get('/foo', 'DI\Bridge\Silex\Test\Fixture\InvokableController');

        $response = $app->handle(Request::create('/foo'));
        $this->assertEquals('Hello world', $response->getContent());
    }

    /**
     * @test
     */
    public function should_resolve_array_controllers()
    {
        $app = new Application();

        $app->get('/foo', array('DI\Bridge\Silex\Test\Fixture\Controller', 'home'));

        $response = $app->handle(Request::create('/foo'));
        $this->assertEquals('Hello world', $response->getContent());
    }

    /**
     * @test
     */
    public function should_pass_url_placeholders()
    {
        $app = new Application();

        $app->get('/{name}', array('DI\Bridge\Silex\Test\Fixture\Controller', 'hello'));

        $response = $app->handle(Request::create('/john'));
        $this->assertEquals('Hello john', $response->getContent());
    }

    /**
     * @test
     */
    public function should_pass_query_parameters()
    {
        $app = new Application();

        $app->get('/', array('DI\Bridge\Silex\Test\Fixture\Controller', 'hello'));

        $response = $app->handle(Request::create('/?name=john'));
        $this->assertEquals('Hello john', $response->getContent());
    }

    /**
     * @test
     */
    public function should_pass_post_data()
    {
        $app = new Application();

        $app->post('/', ['DI\Bridge\Silex\Test\Fixture\Controller', 'hello']);

        $response = $app->handle(Request::create('/', 'POST', [
            'name' => 'john',
        ]));
        $this->assertEquals('Hello john', $response->getContent());
    }
}
