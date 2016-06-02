<?php

namespace DI\Bridge\Silex;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\RouteCollection;

/**
 * Replacement for the Silex ConverterListener to allow arbitrary injection into param converters.
 *
 * @author Felix Becker <f.becker@outlook.com>
 */
class ConverterListener extends \Silex\EventListener\ConverterListener
{
    /**
     * @var CallbackInvoker
     */
    private $callbackInvoker;

    /**
     * @param RouteCollection  $routes           A RouteCollection instance
     * @param CallbackResolver $callbackResolver A CallbackResolver instance
     * @param CallbackInvoker  $callbackInvoker  The invoker that handles resolving and injecting param converters
     */
    public function __construct(RouteCollection $routes, CallbackResolver $callbackResolver, CallbackInvoker $callbackInvoker)
    {
        parent::__construct($routes, $callbackResolver);
        $this->callbackInvoker = $callbackInvoker;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        $route = $this->routes->get($request->attributes->get('_route'));
        if ($route && $converters = $route->getOption('_converters')) {
            foreach ($converters as $name => $callback) {
                $value = $request->attributes->get($name);
                $middleware = $this->callbackResolver->resolveCallback($callback);
                $ret = $this->callbackInvoker->call($middleware, [
                    // parameter name
                    $name => $value,
                    // type hints
                    Request::class => $request,
                    // Silex' default parameter order
                    0 => $value,
                    1 => $request,
                ]);

                $request->attributes->set($name, $ret);
            }
        }
    }
}
