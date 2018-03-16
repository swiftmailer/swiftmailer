<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier <fabien.potencier@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift;

use Swift\Mime\SimpleMessage;

/**
 * Interface for spools.
 *
 * @author Fabien Potencier
 */
interface Spool
{
    /**
     * Starts this Spool mechanism.
     */
    public function start();

    /**
     * Stops this Spool mechanism.
     */
    public function stop();

    /**
     * Tests if this Spool mechanism has started.
     *
     * @return bool
     */
    public function isStarted();

    /**
     * Queues a message.
     *
     * @param SimpleMessage $message The message to store
     *
     * @return bool Whether the operation has succeeded
     */
    public function queueMessage(SimpleMessage $message);

    /**
     * Sends messages using the given transport instance.
     *
     * @param Swift_Transport $transport        A transport instance
     * @param string[]        $failedRecipients An array of failures by-reference
     *
     * @return int The number of sent emails
     */
    public function flushQueue(Transport $transport, &$failedRecipients = null);
}
