<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2011 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\Plugins;

use Swift\Events\SendListener;
use Swift\Events\SendEvent;
use Swift\Mime\SimpleMessage;

/**
 * Stores all sent emails for further usage.
 *
 * @author Fabien Potencier
 */
class MessageLogger implements SendListener
{
    /**
     * @var SimpleMessage[]
     */
    private $messages;

    public function __construct()
    {
        $this->messages = [];
    }

    /**
     * Get the message list.
     *
     * @return SimpleMessage[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Get the message count.
     *
     * @return int count
     */
    public function countMessages()
    {
        return count($this->messages);
    }

    /**
     * Empty the message list.
     */
    public function clear()
    {
        $this->messages = [];
    }

    /**
     * Invoked immediately before the Message is sent.
     */
    public function beforeSendPerformed(SendEvent $evt)
    {
        $this->messages[] = clone $evt->getMessage();
    }

    /**
     * Invoked immediately after the Message is sent.
     */
    public function sendPerformed(SendEvent $evt)
    {
    }
}
