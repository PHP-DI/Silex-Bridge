<?php

use DI\Bridge\Silex\Application;
use DI\Bridge\Silex\CallbackInvoker;
use DI\Bridge\Silex\CallbackResolver;
use DI\Bridge\Silex\Controller\ControllerResolver;
use DI\Bridge\Silex\Provider\HttpKernelServiceProvider;
use Interop\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class HttpKernelServiceProviderTest extends PHPUnit_Framework_TestCase
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

    public function test_register_application_have_request_service()
    {
        $this->application['request_stack'] = function () {
            $requestStack = new RequestStack();
            $requestStack->push(Request::createFromGlobals());

            return $requestStack;
        };

        $this->httpKernelProvider->register($this->application);

        $this->assertArrayHasKey('request', $this->application);
        $this->assertInstanceOf(Request::class, $this->application['request']);
    }
}
