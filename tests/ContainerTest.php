<?php

namespace DI\Bridge\Silex\Test;

use Silex\Route;

class ContainerTest extends BaseTestCase
{
    /**
     * @test
     */
    public function application_container_should_use_both_containers()
    {
        $app = $this->createApplication();

        // Pimple
        $app['foo'] = 'bar';
        // PHP-DI
        $phpdi = $app->getPhpDi();
        $phpdi->set('baz', 'bam');

        $container = $app->getContainer();
        // Pimple
        $this->assertEquals('bar', $container->get('foo'));
        // PHP-DI
        $this->assertEquals('bam', $container->get('baz'));
    }

    /**
     * @test
     */
    public function php_di_use_delegate_lookup()
    {
        $app = $this->createApplication();
        $container = $app->getPhpDi();

        // Get from PHP-DI into Pimple
        $container->set('foo', \DI\get('route_class'));

        $this->assertEquals(Route::class, $container->get('foo'));
    }
}
