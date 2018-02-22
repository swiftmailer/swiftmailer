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
 * Interface for the EventDispatcher which handles the event dispatching layer.
 *
 * @author Chris Corbyn
 */
interface EventDispatcher
{
    /**
     * Create a new SendEvent for $source and $message.
     *
     * @param \Swift\Transport $source
     * @param \Swift\Mime\SimpleMessage
     *
     * @return \Swift\Events\SendEvent
     */
    public function createSendEvent(Transport $source, SimpleMessage $message);

    /**
     * Create a new CommandEvent for $source and $command.
     *
     * @param \Swift\Transport $source
     * @param string          $command      That will be executed
     * @param array           $successCodes That are needed
     *
     * @return \Swift\Events\CommandEvent
     */
    public function createCommandEvent(Transport $source, $command, $successCodes = []);

    /**
     * Create a new ResponseEvent for $source and $response.
     *
     * @param \Swift\Transport $source
     * @param string          $response
     * @param bool            $valid    If the response is valid
     *
     * @return \Swift\Events\ResponseEvent
     */
    public function createResponseEvent(Transport $source, $response, $valid);

    /**
     * Create a new TransportChangeEvent for $source.
     *
     * @param \Swift\Transport $source
     *
     * @return \Swift\Events\TransportChangeEvent
     */
    public function createTransportChangeEvent(Transport $source);

    /**
     * Create a new TransportExceptionEvent for $source.
     *
     * @param \Swift\Transport          $source
     * @param \Swift\TransportException $ex
     *
     * @return \Swift\Events\TransportExceptionEvent
     */
    public function createTransportExceptionEvent(Transport $source, TransportException $ex);

    /**
     * Bind an event listener to this dispatcher.
     *
     * @param \Swift\Events\EventListener $listener
     */
    public function bindEventListener(EventListener $listener);

    /**
     * Dispatch the given Event to all suitable listeners.
     *
     * @param \Swift\Events\EventObject $evt
     * @param string                   $target method
     */
    public function dispatchEvent(EventObject $evt, $target);
}
