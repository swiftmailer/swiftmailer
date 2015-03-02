<?php
/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Sets the sender of a message if none is set
 *
 * @author     Adam Zielinski
 */
class Swift_Plugins_DefaultSenderPlugin implements Swift_Events_SendListener
{

    /**
     * Email of sender to use in all messages that are sent
     * without any sender information.
     *
     * @var string
     */
    private $defaultSenderEmail;

    /**
     * Name of sender to use in all messages that are sent
     * without any sender information.
     *
     * @var string
     */
    private $defaultSenderName;

    /**
     * List if IDs of messages which got sender information set
     * by this plugin.
     *
     * @var string[]
     */
    private $handledMessageIds = array();

    /**
     * Create a new ImpersonatePlugin to impersonate $sender.
     *
     * @param string $defaultSenderEmail
     * @param string $defaultSenderName
     */
    public function __construct($defaultSenderEmail, $defaultSenderName = '')
    {
        $this->defaultSenderEmail = $defaultSenderEmail;
        $this->defaultSenderName = $defaultSenderName;
    }

    /**
     * Invoked immediately before the Message is sent.
     *
     * @param Swift_Events_SendEvent $evt
     */
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();
        $headers = $message->getHeaders();

        // replace sender
        if (!count($message->getFrom())) {
            $message->setFrom($this->defaultSenderEmail, $this->defaultSenderName);
            $this->handledMessageIds[$message->getId()] = true;
        }
    }

    /**
     * Invoked immediately after the Message is sent.
     *
     * @param Swift_Events_SendEvent $evt
     */
    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();

        // restore original headers
        $id = $message->getId();
        if (array_key_exists($id, $this->handledMessageIds)) {
            $message->setFrom(null);
            unset($this->handledMessageIds[$id]);
        }
    }
}
