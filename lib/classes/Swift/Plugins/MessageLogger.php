<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2011 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Stores all sent emails for further usage.
 *
 * @author Fabien Potencier
 */
class Swift_Plugins_MessageLogger implements Swift_Events_SendListener
{
    /**
     * @var Swift_Mime_Message[]
     */
    private $messages;

    public function __construct()
    {
        $this->messages = [];
    }

    /**
     * Get the message list.
     *
     * @return Swift_Mime_Message[]
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
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        $this->messages[] = clone $evt->getMessage();
    }

    /**
     * Invoked immediately after the Message is sent.
     */
    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
    }
}
