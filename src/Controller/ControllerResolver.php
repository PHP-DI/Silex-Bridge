<?php

namespace DI\Bridge\Silex\Controller;

use Invoker\CallableResolver;
use Invoker\Exception\NotCallableException;
use Invoker\ParameterResolver\ParameterResolver;
use Invoker\Reflection\CallableReflection;
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
     * @var ParameterResolver
     */
    private $parameterResolver;

    /**
     * Constructor.
     *
     * @param CallableResolver  $callableResolver
     * @param ParameterResolver $parameterResolver
     */
    public function __construct(CallableResolver $callableResolver, ParameterResolver $parameterResolver)
    {
        $this->callableResolver = $callableResolver;
        $this->parameterResolver = $parameterResolver;
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
     */
    public function getArguments(Request $request, $controller)
    {
        $controllerReflection = CallableReflection::create($controller);
        $controllerParameters = $controllerReflection->getParameters();
        $resolvedArguments = [];

        foreach ($controllerParameters as $index => $parameter) {
            if ('request' === $parameter->getName() || ($parameter->getClass() && $parameter->getClass()->isInstance($request))) {
                $resolvedArguments[$index] = $request;

                break;
            }
        }

        $arguments = $this->parameterResolver->getParameters(
            $controllerReflection,
            $request->attributes->all(),
            $resolvedArguments
        );

        ksort($arguments);

        // Check if all parameters are resolved
        $diff = array_diff_key($controllerParameters, $arguments);
        if (0 < count($diff)) {
            /** @var \ReflectionParameter $parameter */
            $parameter = reset($diff);
            throw new \RuntimeException(sprintf(
                'Controller "%s" requires that you provide a value for the "$%s" argument.',
                $controllerReflection->getName(),
                $parameter->getName()
            ));
        }

        return $arguments;
    }
}
