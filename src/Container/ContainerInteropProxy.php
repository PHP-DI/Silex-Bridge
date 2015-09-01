<?php

namespace DI\Bridge\Silex\Container;

use DI\Bridge\Silex\Application;
use Interop\Container\ContainerInterface;

/**
 * Proxies container-interop methods to the application.
 *
 * ContainerInterface cannot be implemented directly by Application because it defines
 * a `get()` method already (to add a controller for a GET HTTP method). So we use this
 * proxy to have a ContainerInterop container but still use the application.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ContainerInteropProxy implements ContainerInterface
{
    /**
     * @var Application
     */
    private $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function get($id)
    {
        return $this->application->offsetGet($id);
    }

    public function has($id)
    {
        return $this->application->offsetExists($id);
    }
}
