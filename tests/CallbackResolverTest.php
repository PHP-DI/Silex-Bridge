<?php

namespace DI\Bridge\Silex\Test;

use DI\Bridge\Silex\Application;
use DI\Bridge\Silex\Test\Fixture\InvokableController;

class CallbackResolverTest extends BaseTestCase
{
    /**
     * @test
     */
    public function resolver_must_able_to_resolve_original_callback_format()
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
    public function resolver_must_throw_exeption_when_callback_not_found_in_container()
    {
        $app = new Application();
        $app['controller'] = function () {
            return new InvokableController();
        };

        $this->setExpectedException('\InvalidArgumentException');
        $callable = $app['callback_resolver']->resolveCallback('some.service');
    }
}
