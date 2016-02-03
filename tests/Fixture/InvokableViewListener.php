<?php

namespace DI\Bridge\Silex\Test\Fixture;

class InvokableViewListener
{
    public function __invoke($controllerResult)
    {
        return $controllerResult . ' from mars';
    }
}
