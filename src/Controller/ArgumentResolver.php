<?php

namespace DI\Bridge\Silex\Controller;

use Invoker\ParameterResolver\ParameterResolver;
use Invoker\Reflection\CallableReflection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;

/**
 * {@inheritdoc}
 */
class ArgumentResolver implements ArgumentResolverInterface
{
    /**
     * @var ParameterResolver
     */
    private $parameterResolver;

    public function __construct(ParameterResolver $parameterResolver)
    {
        $this->parameterResolver = $parameterResolver;
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