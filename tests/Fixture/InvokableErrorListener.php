<?php

namespace DI\Bridge\Silex\Test\Fixture;

use Symfony\Component\HttpFoundation\Response;

class InvokableErrorListener
{
    public function __invoke(\Exception $e, $code)
    {
        return new Response('Sad panda :(');
    }
}
