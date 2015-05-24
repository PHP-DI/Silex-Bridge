<?php

namespace DI\Bridge\Silex\Test;

class ContainerTest extends BaseTestCase
{
    /**
     * @test
     */
    public function php_di_use_delegate_lookup()
    {
        $app = $this->createApplication();
        $container = $app->getContainer();

        $container->set('foo', \DI\get('route_class'));

        $this->assertEquals('Silex\Route', $container->get('foo'));
    }
}
