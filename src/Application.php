<?php

namespace DI\Bridge\Silex;

use DI\Bridge\Silex\Container\ContainerInteropProxy;
use DI\Bridge\Silex\Provider\HttpKernelServiceProvider;
use DI\Container;
use DI\ContainerBuilder;
use Interop\Container\ContainerInterface;
use Invoker\CallableResolver;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

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

        $this['phpdi.callable_resolver'] = function () {
            return new CallableResolver($this->containerInteropProxy);
        };

        // Register own HttpKernelServiceProvider which overrides some defaults.
        $this->register(new HttpKernelServiceProvider($this->containerInteropProxy, $this->callbackInvoker));
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
            if (!$event->isMasterRequest()) {
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
            if (!$event->isMasterRequest()) {
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
