<?php

namespace DI\Bridge\Silex\Provider;

use DI\Bridge\Silex\CallbackInvoker;
use DI\Bridge\Silex\CallbackResolver;
use DI\Bridge\Silex\Controller\ArgumentResolver;
use DI\Bridge\Silex\Controller\ControllerResolver;
use DI\Bridge\Silex\ConverterListener;
use DI\Bridge\Silex\MiddlewareListener;
use Interop\Container\ContainerInterface;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\ResolverChain;
use Pimple\ServiceProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Jacob Dreesen <jacob.dreesen@gmail.com>
 */
class HttpKernelServiceProvider implements ServiceProviderInterface, EventListenerProviderInterface
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
     * @param ContainerInterface $container
     * @param CallbackInvoker    $callbackInvoker
     */
    public function __construct(ContainerInterface $container, CallbackInvoker $callbackInvoker)
    {
        $this->container = $container;
        $this->callbackInvoker = $callbackInvoker;
    }

    public function register(\Pimple\Container $app)
    {
        // Override the controller resolver with ours.
        $app['resolver'] = function ($app) {
            return new ControllerResolver(
                $app['phpdi.callable_resolver']
            );
        };

        // Override the callback resolver with ours.
        $app['callback_resolver'] = function ($app) {
            return new CallbackResolver(
                $app,
                $app['phpdi.callable_resolver']
            );
        };

        // Override the argument resolver with ours.
        $app['argument_resolver'] = function () {
            return new ArgumentResolver(
                new ResolverChain([
                    new AssociativeArrayResolver,
                    new TypeHintContainerResolver($this->container),
                ])
            );
        };

        // add request to ensure that DI have access to correct request instance from application
        $app['request'] = $app->factory(function ($app) {
            return $app['request_stack']->getCurrentRequest();
        });
    }

    public function subscribe(\Pimple\Container $app, EventDispatcherInterface $dispatcher)
    {
        // Remove the Silex listeners first
        $this->removeSubscriber($dispatcher, \Silex\EventListener\MiddlewareListener::class);
        $this->removeSubscriber($dispatcher, \Silex\EventListener\ConverterListener::class);

        // And register ours instead
        $dispatcher->addSubscriber(new MiddlewareListener($app, $this->callbackInvoker));
        $dispatcher->addSubscriber(new ConverterListener($app['routes'], $app['callback_resolver'], $this->callbackInvoker));
    }

    /**
     * Removes an event subscriber by its class name.
     *
     * @param EventDispatcherInterface $dispatcher
     * @param string                   $subscriberClass
     */
    private function removeSubscriber(EventDispatcherInterface $dispatcher, $subscriberClass)
    {
        if (!is_subclass_of($subscriberClass, EventSubscriberInterface::class, true)) {
            throw new \InvalidArgumentException('$subscriberClass must implement ' . EventSubscriberInterface::class);
        }

        /** @var EventSubscriberInterface $subscriberClass */
        foreach ($subscriberClass::getSubscribedEvents() as $eventName => $params) {
            foreach ($dispatcher->getListeners($eventName) as $listener) {
                if (is_array($listener) && is_a($listener[0], $subscriberClass, true)) {
                    $dispatcher->removeListener($eventName, $listener);
                }
            }
        }
    }
}
