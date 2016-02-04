<?php

namespace DI\Bridge\Silex;

use Invoker\CallableResolver;
use Invoker\Exception\NotCallableException;

/**
 * This alternative resolver uses the generic Invoker to support PHP-DI's extended callable syntax
 */
class CallbackResolver extends \Silex\CallbackResolver
{
    /**
     * @var CallableResolver
     */
    private $resolver;

    public function __construct(\Pimple $app, CallableResolver $resolver)
    {
        $this->resolver = $resolver;
        parent::__construct($app);
    }

    /**
     * @param string $name
     * @return array|callable
     */
    public function convertCallback($name)
    {
        // original pattern
        if ($this->isValid($name)) {
            return parent::convertCallback($name);
        }

        // try to resolve callback from container
        try {
            return $this->resolver->resolve($name);
        } catch (NotCallableException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
    }

    /**
     * @param string|callable $name
     * @return callable
     */
    public function resolveCallback($name)
    {
        return $this->convertCallback($name);
    }
}
