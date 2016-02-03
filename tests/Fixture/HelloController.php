<?php


namespace DI\Bridge\Silex\Test\Fixture;

class HelloController
{
    public function __invoke(\ArrayObject $user)
    {
        return $user['name'];
    }
}
