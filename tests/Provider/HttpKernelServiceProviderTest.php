<?php

namespace DI\Bridge\Silex\Test\Provider;

use DI\Bridge\Silex\Application;
use DI\Bridge\Silex\CallbackInvoker;
use DI\Bridge\Silex\CallbackResolver;
use DI\Bridge\Silex\Controller\ArgumentResolver;
use DI\Bridge\Silex\Controller\ControllerResolver;
use DI\Bridge\Silex\Provider\HttpKernelServiceProvider;
use Interop\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Kernel;

class HttpKernelServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var CallbackInvoker
     */
    private $callbackInvoker;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var HttpKernelServiceProvider
     */
    private $httpKernelProvider;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->callbackInvoker = $this->prophesize(CallbackInvoker::class);
        $this->httpKernelProvider = new HttpKernelServiceProvider($this->container->reveal(), $this->callbackInvoker->reveal());
        $this->application = new Application();
    }

    public function test_register_application_with_default_services()
    {
        $this->httpKernelProvider->register($this->application);

        $this->assertArrayHasKey('resolver', $this->application);
        $this->assertInstanceOf(ControllerResolver::class, $this->application['resolver']);
        $this->assertArrayHasKey('callback_resolver', $this->application);
        $this->assertInstanceOf(CallbackResolver::class, $this->application['callback_resolver']);
    }

    public function test_register_application_have_bridge_argument_resolver_service()
    {
        $this->httpKernelProvider->register($this->application);

        if (Kernel::VERSION_ID >= 30100) {
            $this->assertArrayHasKey('argument_resolver', $this->application);
            $this->assertInstanceOf(ArgumentResolverInterface::class, $this->application['argument_resolver']);
            $this->assertInstanceOf(ArgumentResolver::class, $this->application['argument_resolver']);
        }
    }
}
