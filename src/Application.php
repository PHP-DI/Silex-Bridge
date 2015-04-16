<?php

namespace DI\Bridge\Silex;

use DI\Bridge\Silex\Controller\ControllerResolver;
use DI\Container;
use DI\ContainerBuilder;
use DI\Scope;
use InvalidArgumentException;

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

    public function __construct()
    {
        $this->container = ContainerBuilder::buildDevContainer();

        parent::__construct();

        // Override the controller resolver
        $this->container->set('resolver', function () {
            return new ControllerResolver($this->container);
        });
    }

    public function offsetGet($id)
    {
        return $this->container->get($id);
    }

    public function offsetExists($id)
    {
        return $this->container->has($id);
    }

    public function offsetSet($id, $value)
    {
        if ($value instanceof \Closure) {
            $value = \DI\factory($value)
                ->scope(Scope::SINGLETON());
        }

        $this->container->set($id, $value);
    }

    public function offsetUnset($id)
    {
        // TODO
        throw new \LogicException('Unsupported operation');
    }

    public static function share($callable)
    {
        if (!is_object($callable) || !method_exists($callable, '__invoke')) {
            throw new InvalidArgumentException('Service definition is not a Closure or invokable object.');
        }

        return \DI\factory($callable);
    }

    public static function protect($callable)
    {
        if (!is_object($callable) || !method_exists($callable, '__invoke')) {
            throw new InvalidArgumentException('Callable is not a Closure or invokable object.');
        }

        return \DI\value($callable);
    }

    public function raw($id)
    {
        // TODO
        throw new \LogicException('Unsupported operation');
    }

    public function extend($id, $callable)
    {
        // TODO
        throw new \LogicException('Unsupported operation');
    }

    public function keys()
    {
        // TODO
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
