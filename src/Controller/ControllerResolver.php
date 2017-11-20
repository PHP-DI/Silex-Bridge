<?php

namespace DI\Bridge\Silex\Controller;

use Invoker\CallableResolver;
use Invoker\Exception\NotCallableException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ControllerResolver implements ControllerResolverInterface
{
    /**
     * @var CallableResolver
     */
    private $callableResolver;

    /**
     * Constructor.
     *
     * @param CallableResolver  $callableResolver
     */
    public function __construct(CallableResolver $callableResolver)
    {
        $this->callableResolver = $callableResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getController(Request $request)
    {
        if (! $controller = $request->attributes->get('_controller')) {
            throw new \LogicException(sprintf(
                'Controller for URI "%s" could not be found because the "_controller" parameter is missing.',
                $request->getPathInfo()
            ));
        }

        try {
            return $this->callableResolver->resolve($controller);
        } catch (NotCallableException $e) {
            throw new \InvalidArgumentException(sprintf(
                'Controller for URI "%s" is not callable: %s',
                $request->getPathInfo(),
                $e->getMessage()
            ));
        }
    }

    /**
     * {@inheritdoc}
     * @deprecated This method is deprecated. Silex 2.x uses ArgumentResolverInterface for controller arguments
     */
    public function getArguments(Request $request, $controller)
    {
        return [];
    }
}
