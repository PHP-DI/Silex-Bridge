<?php

namespace DI\Bridge\Silex\Test\Fixture;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InvokableMiddleware
{
    public function __invoke(Request $request)
    {
        return new Response('Hello from middleware');
    }
}
