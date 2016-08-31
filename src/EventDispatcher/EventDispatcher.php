<?php

namespace DI\Bridge\Silex\EventDispatcher;

use DI\Bridge\Silex\CallbackInvoker;
use Symfony\Component\EventDispatcher\Event;

/**
 * Replacement for the Symfony EventDispatcher to allow arbitrary injection into event listeners.
 *
 * @author Bram Van der Sype <bram.vandersype@gmail.com>
 */
class EventDispatcher extends \Symfony\Component\EventDispatcher\EventDispatcher
{
    /**
     * @var CallbackInvoker
     */
    private $callbackInvoker;

    /**
     * @param CallbackInvoker $callbackInvoker
     */
    public function __construct(CallbackInvoker $callbackInvoker)
    {
        $this->callbackInvoker = $callbackInvoker;
    }

    /**
     * @param \callable[] $listeners
     * @param string $eventName
     * @param Event $event
     */
    protected function doDispatch($listeners, $eventName, Event $event)
    {
        foreach ($listeners as $listener) {
            if ($event->isPropagationStopped()) {
                break;
            }
            
            $this->callbackInvoker->call($listener, [
                'event' => $event,
                0 => $event,
                1 => $eventName,
                2 => $this
            ]);
        }
    }

}
