<?php

namespace DI\Bridge\Silex\Test;

use DI\Bridge\Silex\Application;
use DI\ContainerBuilder;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function can_provide_our_own_container()
    {
        $container = ContainerBuilder::buildDevContainer();

        $app = new Application($container);

        $this->assertSame($container, $app->getContainer());
    }
}
