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
        $builder = new ContainerBuilder;
        $builder->addDefinitions([
            // Create an alias so that we can inject with the type-hint
            'Twig_Environment' => \DI\get('twig'),
        ]);
        $app = $this->createApplication($builder);

        $app->register(new TwigServiceProvider(), [
            'twig.path' => __DIR__ . '/Fixture/views',
        ]);

        $app->get('/', function (\Twig_Environment $twig) {
            return $twig->render('foo.twig');
        });

        $response = $app->handle(Request::create('/'));
        $this->assertEquals('Hello', $response->getContent());
    }
}
