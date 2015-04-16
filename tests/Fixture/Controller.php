<?php

namespace DI\Bridge\Silex\Test\Fixture;

class Controller
{
    public function home()
    {
        return 'Hello world';
    }

    public function hello($name)
    {
        return 'Hello ' . $name;
    }
}
