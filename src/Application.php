<?php

namespace DI\Bridge\Silex;

use DI\Bridge\Silex\Controller\ControllerResolver;
use DI\Container;
use DI\ContainerBuilder;
use Pimple;

/**
 * Replacement for the Silex Application class to use PHP-DI instead of Pimple.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Application extends \Silex\Application
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Pimple
     */
    private $pimple;

    /**
     * @param Container|null $container You can optionally provide your own container.
     */
    public function __construct(Container $container = null)
    {
        $this->container = $container ?: ContainerBuilder::buildDevContainer();
        $this->pimple = new Pimple();

        parent::__construct();

        // Override the controller resolver with ours
        $this->pimple['resolver'] = function () {
            return new ControllerResolver($this->container);
        };
    }

    public function offsetGet($id)
    {
        if ($this->container->has($id)) {
            return $this->container->get($id);
        }
        return $this->pimple[$id];
    }

    public function offsetExists($id)
    {
        if ($this->container->has($id)) {
            return true;
        }
        return isset($this->pimple[$id]);
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
        $this->pimple->extend($id, $callable);
    }

    public function keys()
    {
        throw new \LogicException('Unsupported operation');
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
