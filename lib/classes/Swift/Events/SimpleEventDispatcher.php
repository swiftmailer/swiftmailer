<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\Events;

use Swift\Transport;
use Swift\Mime\SimpleMessage;
use Swift\TransportException;

/**
 * The EventDispatcher which handles the event dispatching layer.
 *
 * @author Chris Corbyn
 */
class SimpleEventDispatcher implements EventDispatcher
{
    /** A map of event types to their associated listener types */
    private $eventMap = [];

    /** Event listeners bound to this dispatcher */
    private $listeners = [];

    /** Listeners queued to have an Event bubbled up the stack to them */
    private $bubbleQueue = [];

    /**
     * Create a new EventDispatcher.
     */
    public function __construct()
    {
        $this->eventMap = [
            CommandEvent::class => CommandListener::class,
            ResponseEvent::class => ResponseListener::class,
            SendEvent::class => SendListener::class,
            TransportChangeEvent::class => TransportChangeListener::class,
            TransportExceptionEvent::class => TransportExceptionListener::class,
        ];
    }

    /**
     * Create a new SendEvent for $source and $message.
     *
     * @return \Swift\Events\SendEvent
     */
    public function createSendEvent(Transport $source, SimpleMessage $message)
    {
        return new SendEvent($source, $message);
    }

    /**
     * Create a new CommandEvent for $source and $command.
     *
     * @param string $command      That will be executed
     * @param array  $successCodes That are needed
     *
     * @return \Swift\Events\CommandEvent
     */
    public function createCommandEvent(Transport $source, $command, $successCodes = [])
    {
        return new CommandEvent($source, $command, $successCodes);
    }

    /**
     * Create a new ResponseEvent for $source and $response.
     *
     * @param string $response
     * @param bool   $valid    If the response is valid
     *
     * @return \Swift\Events\ResponseEvent
     */
    public function createResponseEvent(Transport $source, $response, $valid)
    {
        return new ResponseEvent($source, $response, $valid);
    }

    /**
     * Create a new TransportChangeEvent for $source.
     *
     * @return \Swift\Events\TransportChangeEvent
     */
    public function createTransportChangeEvent(Transport $source)
    {
        return new TransportChangeEvent($source);
    }

    /**
     * Create a new TransportExceptionEvent for $source.
     *
     * @return \Swift\Events\TransportExceptionEvent
     */
    public function createTransportExceptionEvent(Transport $source, TransportException $ex)
    {
        return new TransportExceptionEvent($source, $ex);
    }

    /**
     * Bind an event listener to this dispatcher.
     */
    public function bindEventListener(EventListener $listener)
    {
        foreach ($this->listeners as $l) {
            // Already loaded
            if ($l === $listener) {
                return;
            }
        }
        $this->listeners[] = $listener;
    }

    /**
     * Dispatch the given Event to all suitable listeners.
     *
     * @param string $target method
     */
    public function dispatchEvent(EventObject $evt, $target)
    {
        $this->prepareBubbleQueue($evt);
        $this->bubble($evt, $target);
    }

    /** Queue listeners on a stack ready for $evt to be bubbled up it */
    private function prepareBubbleQueue(EventObject $evt)
    {
        $this->bubbleQueue = [];
        $evtClass = get_class($evt);
        foreach ($this->listeners as $listener) {
            if (array_key_exists($evtClass, $this->eventMap)
                && ($listener instanceof $this->eventMap[$evtClass])) {
                $this->bubbleQueue[] = $listener;
            }
        }
    }

    /** Bubble $evt up the stack calling $target() on each listener */
    private function bubble(EventObject $evt, $target)
    {
        if (!$evt->bubbleCancelled() && $listener = array_shift($this->bubbleQueue)) {
            $listener->$target($evt);
            $this->bubble($evt, $target);
        }
    }
}
