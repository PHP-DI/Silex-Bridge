<?php

namespace DI\Bridge\Silex\Test;

use DI\Bridge\Silex\Application;
use DI\Bridge\Silex\CallbackResolver;
use DI\Bridge\Silex\Controller\ControllerResolver;
use DI\Bridge\Silex\EventDispatcher\EventDispatcher;
use DI\Container;
use DI\ContainerBuilder;
use Interop\Container\ContainerInterface;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function can_take_our_own_container_builder()
    {
        $builder = new ContainerBuilder;
        $builder->addDefinitions([
            'foo' => 'bar',
        ]);

        $app = new Application($builder);

        $container = $app->getContainer();
        $this->assertEquals('bar', $container->get('foo'));
    }

    /**
     * @test
     */
    public function the_application_container_should_be_container_interface()
    {
        $app = new Application();

        $this->assertInstanceOf(ContainerInterface::class, $app->getContainer());
    }

    /**
     * @test
     */
    public function the_application_should_expose_phpdi_container()
    {
        $app = new Application();

        $this->assertInstanceOf(Container::class, $app->getPhpDi());
    }

    /**
     * @test
     */
    public function the_controller_resolver_should_be_registered_as_a_service()
    {
        $app = new Application();

        $this->assertInstanceOf(\Closure::class, $app->raw('resolver'));
        $this->assertInstanceOf(ControllerResolver::class, $app['resolver']);
    }

    /**
     * @test
     */
    public function the_callback_resolver_should_be_registered_as_a_service()
    {
        $app = new Application();

        $this->assertInstanceOf(\Closure::class, $app->raw('callback_resolver'));
        $this->assertInstanceOf(CallbackResolver::class, $app['callback_resolver']);
    }

    /**
     * @test
     */
    public function the_event_dispatcher_should_be_ours()
    {
        $app = new Application();
        
        $this->assertInstanceOf(EventDispatcher::class, $app['dispatcher']);
    }
}
