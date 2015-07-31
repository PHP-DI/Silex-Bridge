<?php

namespace DI\Bridge\Silex;

use DI\Bridge\Silex\Container\CompositeContainer;
use DI\Bridge\Silex\Controller\ControllerResolver;
use DI\Container;
use DI\ContainerBuilder;
use Interop\Container\ContainerInterface;
use Interop\Container\Pimple\PimpleInterop;
use Pimple;

/**
 * Replacement for the Silex Application class to use PHP-DI instead of Pimple.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Application extends \Silex\Application
{
    /**
     * @var CompositeContainer
     */
    private $rootContainer;

    /**
     * @var Container
     */
    private $phpdi;

    /**
     * @var Pimple
     */
    private $pimple;

    /**
     * @param ContainerBuilder|null $containerBuilder You can optionally provide your preconfigured container builder.
     * @param array                 $values
     */
    public function __construct(ContainerBuilder $containerBuilder = null, array $values = [])
    {
        // The composite container "merges" PHP-DI and Pimple into one container
        $this->rootContainer = new CompositeContainer();

        $this->pimple = new PimpleInterop();
        $this->rootContainer->setPimple($this->pimple);

        $containerBuilder = $containerBuilder ?: new ContainerBuilder();
        $containerBuilder->addDefinitions([
            'Interop\Container\ContainerInterface' => $this->rootContainer,
        ]);
        $containerBuilder->wrapContainer($this->rootContainer);
        $this->phpdi = $containerBuilder->build();

        $this->rootContainer->setPhpdi($this->phpdi);

        parent::__construct($values);

        // Override the controller resolver with ours
        $this->pimple['resolver'] = function () {
            return new ControllerResolver($this->phpdi);
        };
    }

    public function offsetGet($id)
    {
        return $this->rootContainer->get($id);
    }

    public function offsetExists($id)
    {
        return $this->rootContainer->has($id);
    }

    public function offsetSet($id, $value)
    {
        $this->pimple[$id] = $value;
    }

    public function offsetUnset($id)
    {
        unset($this->pimple[$id]);
    }

    public function raw($id)
    {
        return $this->pimple->raw($id);
    }

    public function extend($id, $callable)
    {
        return $this->pimple->extend($id, $callable);
    }

    public function keys()
    {
        throw new \LogicException('Unsupported operation');
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->rootContainer;
    }

    /**
     * @return Container
     */
    public function getPhpDi()
    {
        return $this->phpdi;
    }
}
