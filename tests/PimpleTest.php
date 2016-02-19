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
    public function closures_should_be_factories_in_silex1()
    {
        $app = new Application();

        if (BaseTestCase::isSilex2($app)) {
            $this->markTestSkipped('Factory services are registered differently in Silex 2.');
        }

        $app['foo'] = function () {
            return new \stdClass();
        };

        $this->assertInstanceOf('stdClass', $app['foo']);
        $this->assertNotSame($app['foo'], $app['foo']);
    }

    /**
     * @test
     */
    public function share_should_share_the_closure_result_in_silex1()
    {
        $app = new Application();

        if (BaseTestCase::isSilex2($app)) {
            $this->markTestSkipped('Shared services are registered differently in Silex 2.');
        }

        $app['foo'] = $app->share(function () {
            return new \stdClass();
        });

        $this->assertInstanceOf('stdClass', $app['foo']);
        $this->assertSame($app['foo'], $app['foo']);
    }

    /**
     * @test
     */
    public function factory_should_create_factory_services_in_silex2()
    {
        $app = new Application();

        if (BaseTestCase::isSilex1($app)) {
            $this->markTestSkipped('Factory services are registered differently in Silex 1.');
        }

        $app['foo'] = $app->factory(function () {
            return new \stdClass();
        });

        $this->assertInstanceOf('stdClass', $app['foo']);
        $this->assertNotSame($app['foo'], $app['foo']);
    }

    /**
     * @test
     */
    public function closures_should_be_shared_in_silex2()
    {
        $app = new Application();

        if (BaseTestCase::isSilex1($app)) {
            $this->markTestSkipped('Shared services are registered differently in Silex 1.');
        }

        $app['foo'] = function () {
            return new \stdClass();
        };

        $this->assertInstanceOf('stdClass', $app['foo']);
        $this->assertSame($app['foo'], $app['foo']);
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
        $service = function () {
            return new \stdClass();
        };

        if (BaseTestCase::isSilex1($app)) {
            $service = $app->share($service);
        }

        $app['foo'] = $service;
        $app['foo'] = $app->extend('foo', function (\stdClass $previous) {
            $previous->hello = 'world';
            return $previous;
        });

        $this->assertInstanceOf('stdClass', $app['foo']);
        $this->assertEquals('world', $app['foo']->hello);
    }
}
