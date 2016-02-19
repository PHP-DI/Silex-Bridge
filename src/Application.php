<?php

namespace DI\Bridge\Silex;

use DI\Bridge\Silex\Container\ContainerInteropProxy;
use DI\Bridge\Silex\Controller\ControllerResolver;
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
     * @var bool
     */
    private $isSilex2;

    /**
     * @param ContainerBuilder|null $containerBuilder You can optionally provide your preconfigured container builder.
     * @param array                 $values
     */
    public function __construct(ContainerBuilder $containerBuilder = null, array $values = [])
    {
        $this->isSilex2 = is_subclass_of($this, 'Pimple\Container', false);
        $this->containerInteropProxy = new ContainerInteropProxy($this);

        $containerBuilder = $containerBuilder ?: new ContainerBuilder();
        $containerBuilder->addDefinitions([
            'Interop\Container\ContainerInterface' => $this->containerInteropProxy,
            'Silex\Application' => $this,
            get_class($this) => $this,
        ]);
        $containerBuilder->wrapContainer($this->containerInteropProxy);
        $this->phpdi = $containerBuilder->build();

        parent::__construct($values);

        $this['phpdi.callable_resolver'] = $this->createSharedPimpleService(function () {
            return new CallableResolver($this->containerInteropProxy);
        });

        // Override the controller resolver with ours
        $this['resolver'] = $this->createSharedPimpleService(function () {
            return new ControllerResolver(
                $this['phpdi.callable_resolver'],
                new ResolverChain([
                    new AssociativeArrayResolver,
                    new TypeHintContainerResolver($this->containerInteropProxy),
                ])
            );
        });

        // Override the callback resolver with ours
        $this['callback_resolver'] = $this->createSharedPimpleService(function () {
            return new CallbackResolver(
                $this,
                $this['phpdi.callable_resolver']
            );
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

    /**
     * Because Silex 2 uses Pimple 3 which has some API breaks compared to Pimple 1 we have to create shared services
     * differently.
     *
     * @param callable $service
     * @return callable
     */
    private function createSharedPimpleService(callable $service)
    {
        return $this->isSilex2 ? $service : $this->share($service);
    }
}
