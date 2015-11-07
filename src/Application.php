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

        parent::__construct($values);

        // Override the controller resolver with ours
        $this['resolver'] = $this->share(function () {
            return new ControllerResolver(
                new CallableResolver($this->containerInteropProxy),
                new ResolverChain([
                    new AssociativeArrayResolver,
                    new TypeHintContainerResolver($this->containerInteropProxy),
                ])
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
}
