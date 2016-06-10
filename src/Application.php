<?php

namespace DI\Bridge\Silex;

use DI\Bridge\Silex\Container\ContainerInteropProxy;
use DI\Bridge\Silex\Controller\ControllerResolver;
use Silex\EventListener\LocaleListener;
use Silex\EventListener\StringToResponseListener;
use Silex\LazyUrlMatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use DI\Container;
use DI\ContainerBuilder;
use Interop\Container\ContainerInterface;
use Invoker\CallableResolver;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\ResolverChain;

/**
 * Replacement for the Silex Application class to use PHP-DI instead of Pimple.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Application extends \Silex\Application
{
    /**
     * @var ContainerInteropProxy
     */
    private $containerInteropProxy;

    /**
     * @var Container
     */
    private $phpdi;

    /**
     * @var CallbackInvoker
     */
    private $callbackInvoker;

    /**
     * @param ContainerBuilder|null $containerBuilder You can optionally provide your preconfigured container builder.
     * @param array                 $values
     */
    public function __construct(ContainerBuilder $containerBuilder = null, array $values = [])
    {
        $this->containerInteropProxy = new ContainerInteropProxy($this);

        $containerBuilder = $containerBuilder ?: new ContainerBuilder();
        $containerBuilder->addDefinitions([
            'Interop\Container\ContainerInterface' => $this->containerInteropProxy,
            'Silex\Application' => $this,
            get_class($this) => $this,
        ]);
        $containerBuilder->wrapContainer($this->containerInteropProxy);
        $this->phpdi = $containerBuilder->build();
        $this->callbackInvoker = new CallbackInvoker($this->containerInteropProxy);

        parent::__construct($values);

        $this['phpdi.callable_resolver'] = $this->share(function () {
            return new CallableResolver($this->containerInteropProxy);
        });

        // Override the controller resolver with ours
        $this['resolver'] = $this->share(function () {
            return new ControllerResolver(
                $this['phpdi.callable_resolver'],
                new ResolverChain([
                    new AssociativeArrayResolver,
                    new TypeHintContainerResolver($this->containerInteropProxy),
                ])
            );
        });

        // Override the callback resolver with ours
        $this['callback_resolver'] = $this->share(function () {
            return new CallbackResolver(
                $this,
                $this['phpdi.callable_resolver']
            );
        });

        // Override the dispatcher with ours to use our event listeners
        $this['dispatcher'] = $this->share(function () {
            /**
             * @var EventDispatcherInterface
             */
            $dispatcher = new $this['dispatcher_class']();

            $urlMatcher = new LazyUrlMatcher(function () {
                return $this['url_matcher'];
            });
            if (Kernel::VERSION_ID >= 20800) {
                $dispatcher->addSubscriber(new RouterListener($urlMatcher, $this['request_stack'], $this['request_context'], $this['logger']));
            } else {
                $dispatcher->addSubscriber(new RouterListener($urlMatcher, $this['request_context'], $this['logger'], $this['request_stack']));
            }
            $dispatcher->addSubscriber(new LocaleListener($this, $urlMatcher, $this['request_stack']));
            if (isset($this['exception_handler'])) {
                $dispatcher->addSubscriber($this['exception_handler']);
            }
            $dispatcher->addSubscriber(new ResponseListener($this['charset']));
            $dispatcher->addSubscriber(new MiddlewareListener($this, $this->callbackInvoker));
            $dispatcher->addSubscriber(new ConverterListener($this['routes'], $this['callback_resolver'], $this->callbackInvoker));
            $dispatcher->addSubscriber(new StringToResponseListener());

            return $dispatcher;
        });
    }

    public function offsetGet($id)
    {
        if (parent::offsetExists($id)) {
            return parent::offsetGet($id);
        }
        return $this->phpdi->get($id);
    }

    public function offsetExists($id)
    {
        return parent::offsetExists($id) || $this->phpdi->has($id);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->containerInteropProxy;
    }

    /**
     * @return Container
     */
    public function getPhpDi()
    {
        return $this->phpdi;
    }

    public function before($callback, $priority = 0)
    {
        $this->on(KernelEvents::REQUEST, function (GetResponseEvent $event) use ($callback) {
            if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
                return;
            }

            $request = $event->getRequest();
            $middleware = $this['callback_resolver']->resolveCallback($callback);
            $ret = $this->callbackInvoker->call($middleware, [
                // type hints
                'Symfony\Component\HttpFoundation\Request' => $request,
                // Silex' default parameter order
                0 => $request,
                1 => $this,
            ]);

            if ($ret instanceof Response) {
                $event->setResponse($ret);
            }
        }, $priority);
    }

    public function after($callback, $priority = 0)
    {
        $this->on(KernelEvents::RESPONSE, function (FilterResponseEvent $event) use ($callback) {
            if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
                return;
            }

            $request = $event->getRequest();
            $response = $event->getResponse();
            $middleware = $this['callback_resolver']->resolveCallback($callback);
            $ret = $this->callbackInvoker->call($middleware, [
                // type hints
                'Symfony\Component\HttpFoundation\Request' => $request,
                'Symfony\Component\HttpFoundation\Response' => $response,
                // Silex' default parameter order
                0 => $request,
                1 => $response,
                2 => $this,
            ]);

            if ($ret instanceof Response) {
                $event->setResponse($ret);
            } elseif (null !== $ret) {
                throw new \RuntimeException('An after middleware returned an invalid response value. Must return null or an instance of Response.');
            }
        }, $priority);
    }

    public function finish($callback, $priority = 0)
    {
        $this->on(KernelEvents::TERMINATE, function (PostResponseEvent $event) use ($callback) {
            $request = $event->getRequest();
            $response = $event->getResponse();
            $middleware = $this['callback_resolver']->resolveCallback($callback);
            $this->callbackInvoker->call($middleware, [
                // type hints
                'Symfony\Component\HttpFoundation\Request' => $request,
                'Symfony\Component\HttpFoundation\Response' => $response,
                // Silex' default parameter order
                0 => $request,
                1 => $response,
                2 => $this,
            ]);
        }, $priority);
    }
}
