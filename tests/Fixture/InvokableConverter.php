<?php

namespace DI\Bridge\Silex\Test\Fixture;

class InvokableConverter
{
    public function __invoke($user)
    {
        return new \ArrayObject(['name' => $user]);
    }
}
