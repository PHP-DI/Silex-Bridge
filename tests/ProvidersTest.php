<?php

namespace DI\Bridge\Silex\Test;

use DI\ContainerBuilder;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;

class ProvidersTest extends BaseTestCase
{
    /**
     * @test
     */
    public function test_twig()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->set('Twig_Environment', \DI\get('twig'));
        $app = $this->createApplication($container);

        $app->register(new TwigServiceProvider(), [
            'twig.path' => __DIR__ . '/Fixtures/views',
        ]);

        $app->get('/', function (\Twig_Environment $twig) {
            return $twig->render('foo');
        });

        $response = $app->handle(Request::create('/'));
        $this->assertEquals('Hello', $response->getContent());
    }
}
