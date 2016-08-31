<?php

namespace DI\Bridge\Silex;

use Interop\Container\ContainerInterface;
use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;
use Invoker\ParameterResolver\TypeHintResolver;

/**
 * A subclass of Invoker that always tries to first resolve through provided parameter names, then
 * type hints, then through the DI container and finally allows a fallback to a default parameter order.
 *
 * @author Felix Becker <f.becker@outlook.com>
 */
class CallbackInvoker extends Invoker
{
    /**
     * @param ContainerInterface $container The container for injection
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct(new ResolverChain([
            new AssociativeArrayResolver,
            new TypeHintResolver,
            new TypeHintContainerResolver($container),
            new NumericArrayResolver,
        ]), $container);
    }
}
