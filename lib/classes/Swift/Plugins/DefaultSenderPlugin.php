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
     * The default sender email.
     *
     * @var String
     */
    private $_defaultSenderEmail;

    /**
     * The default sender name.
     *
     * @var String
     */
    private $_defaultSenderName;

    /**
     * List if IDs of handled messages
     *
     * @var array
     */
    private $_handledMessageIds = array();

    /**
     * Create a new ImpersonatePlugin to impersonate $sender.
     *
     * @param string $sender address
     */
    public function __construct($defaultSenderEmail, $defaultSenderName = '')
    {
        $this->_defaultSenderEmail = $defaultSenderEmail;
        $this->_defaultSenderName = $defaultSenderName;
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
            $message->setFrom($this->_defaultSenderEmail, $this->_defaultSenderName);
            $this->_handledMessageIds[$message->getId()] = true;
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
        if (array_key_exists($id, $this->_handledMessageIds)) {
            $message->setFrom(null);
            unset($this->_handledMessageIds[$id]);
        }
    }

}
