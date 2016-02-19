<?php

namespace DI\Bridge\Silex\Test;

use DI\Bridge\Silex\Application;
use DI\ContainerBuilder;
use PHPUnit_Framework_TestCase;

class BaseTestCase extends PHPUnit_Framework_TestCase
{
    public static function isSilex1(\Silex\Application $app)
    {
        return ! self::isSilex2($app);
    }

    public static function isSilex2(\Silex\Application $app)
    {
        return is_subclass_of($app, 'Pimple\Container', false);
    }

    protected function createApplication(ContainerBuilder $builder = null)
    {
        $app = new Application($builder);

        $app['debug'] = true;
        unset($app['exception_handler']);

        return $app;
    }
}
