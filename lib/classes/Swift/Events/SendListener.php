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
 * Listens for Messages being sent from within the Transport system.
 *
 * @author Chris Corbyn
 */
interface SendListener extends EventListener
{
    /**
     * Invoked immediately before the Message is sent.
     *
     * @param \Swift\Events\SendEvent $evt
     */
    public function beforeSendPerformed(SendEvent $evt);

    /**
     * Invoked immediately after the Message is sent.
     *
     * @param \Swift\Events\SendEvent $evt
     */
    public function sendPerformed(SendEvent $evt);
}
