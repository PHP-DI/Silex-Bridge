<?php

namespace DI\Bridge\Silex\Container;

use DI\Container;
use Interop\Container\ContainerInterface;
use Pimple;

class CompositeContainer implements ContainerInterface
{
    /**
     * @var Container
     */
    private $phpdi;

    /**
     * @var Pimple
     */
    private $pimple;

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        if ($this->pimple->offsetExists($id)) {
            return $this->pimple->offsetGet($id);
        }

        return $this->phpdi->get($id);
    }

    /**
     * {@inheritDoc}
     */
    public function has($id)
    {
        if ($this->pimple->offsetExists($id)) {
            return true;
        }

        return $this->phpdi->has($id);
    }

    public function setPhpdi(Container $phpdi)
    {
        $this->phpdi = $phpdi;
    }

    public function setPimple(Pimple $pimple)
    {
        $this->pimple = $pimple;
    }
}
