<?php

namespace DI\Bridge\Silex;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Replacement for the Silex MiddlewareListener to allow arbitrary injection into middleware functions.
 *
 * @author Felix Becker <f.becker@outlook.com>
 */
class MiddlewareListener extends \Silex\EventListener\MiddlewareListener
{
    /**
     * @var CallbackInvoker
     */
    private $callbackInvoker;

    /**
     * @param Application     $app             The application
     * @param CallbackInvoker $callbackInvoker The invoker that handles injecting middleware
     */
    public function __construct(Application $app, CallbackInvoker $callbackInvoker)
    {
        parent::__construct($app);
        $this->callbackInvoker = $callbackInvoker;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');
        if (!$route = $this->app['routes']->get($routeName)) {
            return;
        }

        foreach ((array) $route->getOption('_before_middlewares') as $callback) {
            $middleware = $this->app['callback_resolver']->resolveCallback($callback);
            $ret = $this->callbackInvoker->call($middleware, [
                // type hints
                'Symfony\Component\HttpFoundation\Request' => $request,
                // Silex' default parameter order
                0 => $request,
                1 => $this->app,
            ]);

            if ($ret instanceof Response) {
                $event->setResponse($ret);

                return;
            } elseif (null !== $ret) {
                throw new \RuntimeException(sprintf('A before middleware for route "%s" returned an invalid response value. Must return null or an instance of Response.', $routeName));
            }
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $routeName = $request->attributes->get('_route');
        if (!$route = $this->app['routes']->get($routeName)) {
            return;
        }

        foreach ((array) $route->getOption('_after_middlewares') as $callback) {
            $middleware = $this->app['callback_resolver']->resolveCallback($callback);
            $ret = $this->callbackInvoker->call($middleware, [
                // type hints
                'Symfony\Component\HttpFoundation\Request' => $request,
                'Symfony\Component\HttpFoundation\Response' => $response,
                // Silex' default parameter order
                0 => $request,
                1 => $response,
                2 => $this->app,
            ]);

            if ($ret instanceof Response) {
                $event->setResponse($ret);
            } elseif (null !== $ret) {
                throw new \RuntimeException(sprintf('An after middleware for route "%s" returned an invalid response value. Must return null or an instance of Response.', $routeName));
            }
        }
    }
}
