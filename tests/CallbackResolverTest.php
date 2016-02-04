<?php

namespace DI\Bridge\Silex\Test;

use DI\Bridge\Silex\Application;
use DI\Bridge\Silex\Test\Fixture\InvokableController;

class CallbackResolverTest extends BaseTestCase
{
    /**
     * @test
     */
    public function resolver_must_be_able_to_resolve_original_callback_format()
    {
        $app = new Application();
        $app['controller'] = function () {
            return new InvokableController();
        };

        $callable = $app['callback_resolver']->resolveCallback('controller:__invoke');

        $this->assertEquals('Hello world', $callable());
    }

    /**
     * @test
     */
    public function resolver_must_be_able_to_resolve_custom_callback_format()
    {
        $app = new Application();
        $app['controller'] = function () {
            return new InvokableController();
        };

        $callable = $app['callback_resolver']->resolveCallback('controller');

        $this->assertEquals('Hello world', $callable());
    }

    /**
     * @test
     */
    public function resolver_must_be_able_to_resolve_callable_callback()
    {
        $app = new Application();

        $callable = $app['callback_resolver']->resolveCallback(function () {
            return 'Hello world';
        });

        $this->assertEquals('Hello world', $callable());
    }

    /**
     * @test
     */
    public function resolver_must_throw_exception_when_callback_not_found_in_container()
    {
        $app = new Application();

        $this->setExpectedException('\InvalidArgumentException');
        $app['callback_resolver']->resolveCallback('some.service');
    }
}
