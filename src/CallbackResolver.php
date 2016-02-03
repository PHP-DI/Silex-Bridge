<?php

namespace DI\Bridge\Silex;

use Invoker\CallableResolver;
use Invoker\Exception\NotCallableException;

/**
 * Class CallbackResolver
 * @package DI\Bridge\Silex
 */
class CallbackResolver extends \Silex\CallbackResolver
{
    /**
     * @var CallableResolver
     */
    private $resolver;

    /**
     * @param \Pimple $app
     * @param CallableResolver $resolver
     */
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
            throw new \InvalidArgumentException(sprintf('Service "%s" does not exist.', $name));
        }
    }

    /**
     * @param string $name
     * @return array|callable
     */
    public function resolveCallback($name)
    {
        return $this->convertCallback($name);
    }
}
