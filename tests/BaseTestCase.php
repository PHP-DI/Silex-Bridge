<?php

namespace DI\Bridge\Silex\Test;

use DI\Bridge\Silex\Application;
use DI\ContainerBuilder;
use PHPUnit_Framework_TestCase;

class BaseTestCase extends PHPUnit_Framework_TestCase
{
    protected function createApplication(ContainerBuilder $builder = null)
    {
        $app = new Application($builder);

        $app['debug'] = true;
        unset($app['exception_handler']);

        return $app;
    }
}
