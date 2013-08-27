<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Sends Messages using the MailerQ server.
 *
 * @package    Swift
 * @subpackage Transport
 * @author     http://mailerq.com
 */
class Swift_Transport_MailerQTransport implements Swift_Transport
{
    /** The event dispatcher from the plugin API */
    private $_eventDispatcher;
    
    /** Swift_MailerQConnection that handles publishing the message */
    private $_connection;
    
    /** Localhost same as in RabbitMQ server */
    private $_hostname = 'localhost';
    
    /** Outbox same as in RabbitMQ server */
    private $_exchange = 'outbox';
    
    /** Login same as in RabbitMQ server */
    private $_login = 'guest';
    
    /** Password same as in RabbitMQ server */
    private $_password = 'guest';
    
    /** Vhost same as in RabbitMQ server */
    private $_vhost = '/';
    
    /** Additional options to send along with the message */
    private $_options;
    
    /**
     * Constructs the MailerQTransport class.
     * 
     * @param Swift_Events_EventDispatcher $eventDispatcher
     * @param Swift_MailerQConnection $connection
     */ 
    public function __construct(Swift_Events_EventDispatcher $eventDispatcher)
    {
        // core parameters
        $this->_eventDispatcher = $eventDispatcher;
        
        // connection
        $this->_connection = new Swift_MailerQConnection($this->_hostname, $this->_exchange, $this->_login, $this->_password, $this->_vhost);
    }
    
    /**
     * Determine whether the connection
     * has been established or not
     */
    public function isStarted()
    {
        return $this->_connection->valid();
    }

    /**
     * Not used.
     */
    public function start() { }

    /**
     * Not used.
     */
    public function stop() { }

    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param Swift_Mime_Message $message
     * @param string[]           $failedRecipients An array of failures by-reference
     *
     * @return integer
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        if ($evt = $this->_eventDispatcher->createSendEvent($this, $message))
        {
            $this->_eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) return 0;
        }
        
        // merge all recipients into one array
        $recipients = array_keys(array_merge(
            $this->_getTo($message),
            $this->_getCC($message), 
            $this->_getBCC($message)
        ));
        
        // if recipient has not been set it will throw a transport exception
        if (!$recipients)
        {
            throw new Swift_TransportException('Cannot send a message without a recipient');
        }        
        
        // and publish the message
        foreach ($recipients as $recipient)
        {
            $this->_connection->publishMessage(array_merge(array(
                'envelope'  => $this->_getEnvelope($message),
                'recipient' => $recipient,
                'body'      =>  $message->toString()
            ), $this->_options));
        }
        
        // if the publish message result went fine
        // then fire RESULT_SUCCESS
        if($this->isStarted())
        {
            if ($evt) 
            {
                $evt->setResult(Swift_Events_SendEvent::RESULT_SUCCESS);
                $evt->setFailedRecipients($failedRecipients);
                $this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
            }            
        }
        // in case of failure fire RESULT_FAILED
        else
        {
            if ($evt) {
                $evt->setResult(Swift_Events_SendEvent::RESULT_FAILED);
                $evt->setFailedRecipients($failedRecipients);
                $this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
            }            
        }
    }
    
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        $this->_eventDispatcher->bindEventListener($plugin);
    }
    
    /**
     * 
     * Returns the envelope address from the message.
     * 
     * @param Swift_Mime_Message $message
     */
    private function _getEnvelope(Swift_Mime_Message $message)
    {
        $return = $message->getReturnPath();
        $reply = $message->getReplyTo();
        $from = $message->getFrom();
        $envelope = null;
        
        if (!empty($return)) {
            $envelope = $return;
        } elseif (!empty($reply)) {
            $keys = array_keys($reply);
            $envelope = array_shift($keys);
        } elseif (!empty($from)) {
            $keys = array_keys($from);
            $envelope = array_shift($keys);
        }

        return $envelope;
    }
    
    /**
     * Helper method to retrieve TO from the message
     * 
     * @param Swift_Mime_Message $message
     */    
    private function _getTo(Swift_Mime_Message $message)
    {
        $to = $message->getTo();
        return (!empty($to)) ? $to : array();
    }    
    
    /**
     * Helper method to retrieve CC from the message
     * 
     * @param Swift_Mime_Message $message
     */
    private function _getCC(Swift_Mime_Message $message)
    {
        $cc = $message->getCC();
        return (!empty($cc)) ? $cc : array();
    }    
    
    /**
     * Helper method to retrieve BCC from the message
     * 
     * @param Swift_Mime_Message $message
     */    
    private function _getBCC(Swift_Mime_Message $message)
    {
        $bcc = $message->getBCC();
        return (!empty($bcc)) ? $bcc : array();        
    }
    
    /**
     * Sets the hostname
     * 
     * @param string $hostname
     */
    public function setHostname($hostname)
    {
        $this->_hostname = $hostname;
    }
    
    /**
     * Sets the exchange
     * 
     * @param string $exchange
     */    
    public function setExchange($exchange)
    {
        $this->_exchange = $exchange;
    }

    /**
     * Sets the login
     * 
     * @param string $login
     */    
    public function setLogin($login)
    {
        $this->_login = $login;
    }
    
    /**
     * Sets the password
     * 
     * @param string $password
     */    
    public function setPassword($password)
    {
        $this->_password = $password;
    }
    
    /**
     * Sets the vhost
     * 
     * @param string $vhost
     */    
    public function setVhost($vhost)
    {
        $this->_vhost = $vhost;
    }
    
    /**
     * Sets the additional options
     * 
     * @param array $options
     */    
    public function setOptions($options)
    {
        $this->_options = $options;
    }
}
