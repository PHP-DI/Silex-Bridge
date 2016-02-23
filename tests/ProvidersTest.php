<?php

namespace DI\Bridge\Silex\Test;

use DI\Bridge\Silex\Test\Fixture\Controller;
use DI\ContainerBuilder;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Swift_Events_SimpleEventDispatcher;
use Swift_Mailer;
use Swift_Transport_NullTransport;
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

        $app->get('/', function (UrlGenerator $urlGenerator) {
            return $urlGenerator->generate('home');
        })->bind('home');

        $response = $app->handle(Request::create('/'));
        $this->assertEquals('/', $response->getContent());
    }

    /**
     * @see https://github.com/PHP-DI/Silex-Bridge/issues/3
     * @test
     */
    public function test_mailer()
    {
        $builder = new ContainerBuilder;
        $builder->addDefinitions([
            // Create an alias so that we can inject with the type-hint
            'Swift_Mailer' => \DI\get('mailer'),
        ]);
        $app = $this->createApplication($builder);

        $app->register(new SwiftmailerServiceProvider, [
            'swiftmailer.transport' => new Swift_Transport_NullTransport(
                new Swift_Events_SimpleEventDispatcher
            ),
        ]);

        $app->get('/', function (Swift_Mailer $mailer) {
            $message = \Swift_Message::newInstance();
            $mailer->send($message);
            return 'OK';
        });

        $response = $app->handle(Request::create('/'));
        $this->assertEquals('OK', $response->getContent());
    }

    /**
     * @see https://github.com/PHP-DI/Silex-Bridge/issues/7
     * @test
     */
    public function test_service_controller_service_provider()
    {
        $app = $this->createApplication();

        $app->register(new ServiceControllerServiceProvider, [
            'service.controller' => new Controller,
        ]);

        $app->get('/{name}', 'service.controller:hello');

        $response = $app->handle(Request::create('/john'));
        $this->assertEquals('Hello john', $response->getContent());
    }
}
