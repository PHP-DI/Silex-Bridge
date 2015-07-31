<?php

namespace DI\Bridge\Silex\Controller;

use DI\InvokerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ControllerResolver implements ControllerResolverInterface
{
    /**
     * @var InvokerInterface
     */
    private $invoker;

    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }

    /**
     * {@inheritdoc}
     */
    public function getController(Request $request)
    {
        $controller = $request->attributes->get('_controller');

        if (! $controller) {
            throw new \LogicException('No controller can be found for this request');
        }

        return function () use ($request, $controller) {
            $parameters = [
                'request' => $request,
            ];
            $parameters += $request->attributes->all() + $request->request->all() + $request->query->all();

            return $this->invoker->call($controller, $parameters);
        };
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments(Request $request, $controller)
    {
        return array();
    }
}
