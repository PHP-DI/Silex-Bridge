<?php

namespace DI\Bridge\Silex\Test;

use DI\Bridge\Silex\Application;

/**
 * Check that the Silex application can still behave like Pimple.
 *
 * This is necessary so that Silex providers can still be used.
 */
class PimpleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_behave_like_an_array()
    {
        $app = new Application();

        $this->assertFalse(isset($app['foo']));
        $app['foo'] = 'Hello';
        $this->assertTrue(isset($app['foo']));
        $this->assertEquals('Hello', $app['foo']);
        unset($app['foo']);
        $this->assertFalse(isset($app['foo']));
    }

    /**
     * @test
     */
    public function closures_results_should_be_shared()
    {
        $app = new Application();

        $app['foo'] = function () {
            return new \stdClass();
        };

        $this->assertInstanceOf('stdClass', $app['foo']);
        $this->assertSame($app['foo'], $app['foo']);
    }

    /**
     * @test
     */
    public function factory_should_create_factory_services()
    {
        $app = new Application();

        $app['foo'] = $app->factory(function () {
            return new \stdClass();
        });

        $this->assertInstanceOf('stdClass', $app['foo']);
        $this->assertNotSame($app['foo'], $app['foo']);
    }

    /**
     * @test
     */
    public function protect_should_mark_factory_as_value()
    {
        $app = new Application();

        $app['foo'] = $app->protect(function () {});

        $this->assertInstanceOf('Closure', $app['foo']);
    }

    /**
     * @test
     */
    public function raw_should_return_factory()
    {
        $app = new Application();

        $app['foo'] = function () {};

        $this->assertInstanceOf('Closure', $app->raw('foo'));
    }

    /**
     * @test
     */
    public function extend_should_extend_the_pimple_entry()
    {
        $app = new Application();

        $app['foo'] = function () {
            return new \stdClass();
        };
        $app->extend('foo', function (\stdClass $previous) {
            $previous->hello = 'world';
            return $previous;
        });

        $this->assertInstanceOf('stdClass', $app['foo']);
        $this->assertEquals('world', $app['foo']->hello);
    }
}
