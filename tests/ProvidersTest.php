<?php

namespace DI\Bridge\Silex\Test;

use DI\ContainerBuilder;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;

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

    /**
     * @see https://github.com/PHP-DI/Silex-Bridge/issues/3
     * @test
     */
    public function test_url_generator()
    {
        $builder = new ContainerBuilder;
        $builder->addDefinitions([
            // Create an alias so that we can inject with the type-hint
            'Symfony\Component\Routing\Generator\UrlGenerator' => \DI\get('url_generator'),
        ]);
        $app = $this->createApplication($builder);

        $app->register(new UrlGeneratorServiceProvider());

        $app->get('/', function (UrlGenerator $urlGenerator) {
            return $urlGenerator->generate('home');
        })->bind('home');

        $response = $app->handle(Request::create('/'));
        $this->assertEquals('/', $response->getContent());
    }
}
