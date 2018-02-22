<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\Events;

/**
 * Listens for Exceptions thrown from within the Transport system.
 *
 * @author Chris Corbyn
 */
interface TransportExceptionListener extends EventListener
{
    /**
     * Invoked as a TransportException is thrown in the Transport system.
     *
     * @param \Swift\Events\TransportExceptionEvent $evt
     */
    public function exceptionThrown(TransportExceptionEvent $evt);
}
