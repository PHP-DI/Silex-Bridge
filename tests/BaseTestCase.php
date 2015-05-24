<?php

namespace DI\Bridge\Silex\Test;

use DI\Bridge\Silex\Application;
use DI\Container;
use PHPUnit_Framework_TestCase;

class BaseTestCase extends PHPUnit_Framework_TestCase
{
    protected function createApplication(Container $container = null)
    {
        $app = new Application($container);

        $app['debug'] = true;
        $app['exception_handler']->disable();

        return $app;
    }
}
