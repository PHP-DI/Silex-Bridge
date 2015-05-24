# PHP-DI integration with Silex

## Installation

```php
composer require php-di/silex-bridge
```

## Usage

In order to benefit from PHP-DI's integration in Silex, you only need to use `DI\Bridge\Silex\Application` instead of the original `Silex\Application`.

Here is the classic Silex example updated:

```php
<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new DI\Bridge\Silex\Application();

$app->get('/hello/{name}', function ($name) use ($app) {
    return 'Hello '.$app->escape($name);
});

$app->run();
```

## Benefits

Using PHP-DI in Silex allows you to use all the awesome features of PHP-DI to wire your dependencies (using the definition files, autowiring, annotations, …).

Another big benefit of the PHP-DI integration is the ability to use dependency injection inside controllers:

```php
class Mailer
{
    // ...
}

$app->post('/register/{name}', function ($name, Mailer $mailer) {
    $mailer->sendMail($name, 'Welcome!');

    return 'You have received a new email';
});
```

Dependency injection in controllers works using type-hinting:

- it can be mixed with request parameters (`$name` in the example above)
- the order of parameters doesn't matter, they are resolved by type-hint (for dependency injection) and by name (for request parameters)
- it only works with objects that you can type-hint: you can't inject string/int values for example, and you can't inject container entries whose name is not a class/interface name (e.g. `twig` or `doctrine.entity_manager`)

## Configuring the container

You can configure PHP-DI's container by creating your own `ContainerBuilder` and passing it to the application:

```php
$containerBuilder = new DI\ContainerBuilder();

// E.g. setup a cache
$containerBuilder->setDefinitionCache(new ApcCache());

// Add definitions
$containerBuilder->addDefinitions([
    // place your definitions here
]);

// Register a definition file
$containerBuilder->addDefinitions('config.php');

$app = new DI\Bridge\Silex\Application($containerBuilder);
```
