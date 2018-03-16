<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2011 Fabien Potencier <fabien.potencier@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift;

use Swift\Mime\SimpleMessage;

/**
 * Stores Messages in memory.
 *
 * @author Fabien Potencier
 */
class MemorySpool implements Spool
{
    protected $messages = [];
    private $flushRetries = 3;

    /**
     * Tests if this Transport mechanism has started.
     *
     * @return bool
     */
    public function isStarted()
    {
        return true;
    }

    /**
     * Starts this Transport mechanism.
     */
    public function start()
    {
    }

    /**
     * Stops this Transport mechanism.
     */
    public function stop()
    {
    }

    /**
     * @param int $retries
     */
    public function setFlushRetries($retries)
    {
        $this->flushRetries = $retries;
    }

    /**
     * Stores a message in the queue.
     *
     * @param \Swift\Mime\SimpleMessage $message The message to store
     *
     * @return bool Whether the operation has succeeded
     */
    public function queueMessage(SimpleMessage $message)
    {
        //clone the message to make sure it is not changed while in the queue
        $this->messages[] = clone $message;

        return true;
    }

    /**
     * Sends messages using the given transport instance.
     *
     * @param \Swift\Transport $transport        A transport instance
     * @param string[]        $failedRecipients An array of failures by-reference
     *
     * @return int The number of sent emails
     */
    public function flushQueue(Transport $transport, &$failedRecipients = null)
    {
        if (!$this->messages) {
            return 0;
        }

        if (!$transport->isStarted()) {
            $transport->start();
        }

        $count = 0;
        $retries = $this->flushRetries;
        while ($retries--) {
            try {
                while ($message = array_pop($this->messages)) {
                    $count += $transport->send($message, $failedRecipients);
                }
            } catch (TransportException $exception) {
                if ($retries) {
                    // re-queue the message at the end of the queue to give a chance
                    // to the other messages to be sent, in case the failure was due to
                    // this message and not just the transport failing
                    array_unshift($this->messages, $message);

                    // wait half a second before we try again
                    usleep(500000);
                } else {
                    throw $exception;
                }
            }
        }

        return $count;
    }
}
