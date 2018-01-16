<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Sets the sender of a message if none is set.
 *
 * @author Adam Zielinski
 */
class Swift_Plugins_DefaultSenderPlugin implements Swift_Events_SendListener
{
    private $defaultSenderEmail;

    private $defaultSenderName;

    private $handledMessageIds = array();

    /**
     * @param string $defaultSenderEmail Email of sender to use in all messages that are sent without any sender information.
     * @param string $defaultSenderName Name of sender to use in all messages that are sent without any sender information.
     */
    public function __construct($defaultSenderEmail, $defaultSenderName = '')
    {
        $this->defaultSenderEmail = $defaultSenderEmail;
        $this->defaultSenderName = $defaultSenderName;
    }

    /**
     * @{inheritdoc}
     */
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();

        // replace sender
        if (!count($message->getFrom())) {
            $message->setFrom($this->defaultSenderEmail, $this->defaultSenderName);
            $this->handledMessageIds[$message->getId()] = true;
        }
    }

    /**
     * @{inheritdoc}
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
