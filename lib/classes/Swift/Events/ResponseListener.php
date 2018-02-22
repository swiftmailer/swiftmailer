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
 * Listens for responses from a remote SMTP server.
 *
 * @author Chris Corbyn
 */
interface ResponseListener extends EventListener
{
    /**
     * Invoked immediately following a response coming back.
     *
     * @param \Swift\Events\ResponseEvent $evt
     */
    public function responseReceived(ResponseEvent $evt);
}
