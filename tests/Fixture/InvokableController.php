<?php

namespace DI\Bridge\Silex\Test\Fixture;

class InvokableController
{
    public function __invoke()
    {
        return 'Hello world';
    }
}
