<?php

namespace DI\Bridge\Silex\Test;

use DI\Bridge\Silex\Application;
use DI\ContainerBuilder;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function can_take_our_own_container_builder()
    {
        $builder = new ContainerBuilder;
        $builder->addDefinitions([
            'foo' => 'bar',
        ]);

        $app = new Application($builder);

        $container = $app->getContainer();
        $this->assertEquals('bar', $container->get('foo'));
    }
}
