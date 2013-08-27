<?php

// check if the AMQP extension has been installed
if(!class_exists('AMQPConnection')) die('The extension AMQP is not installed on the server.');

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base MailerQ Connection class.
 *
 * @package    Swift
 * @subpackage Transport
 * @author     http://mailerq.com
 */
class Swift_MailerQConnection
{
    /** @var AMQPExchange */
    private $_exchange;
    
    /**
     * Creates the connection to the AMQP server.
     * @param string $hostname
     * @param string $name
     * @param string $login
     * @param string $password
     * $param string $vhost
     */
    public function __construct($hostname='localhost', $name='outbox', $login='guest', $password='guest', $vhost='/')
    {
        // create the connection
        try
        {
            // create a connection to RabbitMQ
            $connection = new AMQPConnection(array(
                'host' => $hostname, 
                'login' => $login,
                'password' => $password,
                'vhost' => $vhost
            ));
            
            // make the actual connection
            $connection->connect();
            
            // we need a AMQP channel
            $channel = new AMQPChannel($connection);
            
            // create the exchange
            $this->_exchange = new AMQPExchange($channel);
            $this->_exchange->setName($name);
            $this->_exchange->setType('direct');
            $this->_exchange->setFlags(AMQP_DURABLE);
            $this->_exchange->declareExchange();
            
            // and declare the queue
            $queue = new AMQPQueue($channel);
            $queue->setName($name);
            $queue->setFlags(AMQP_DURABLE);
            $queue->declareQueue();
            
            // the queue should be bound to the exchange
            $queue->bind($name, "0");
        }
        catch (AMQPException $exception)
        {
            // failure, forget about the channel and exchange
            $this->_channel = null;
            $this->_exchange = null;
            
            throw new Swift_TransportException('Connection cannot be established.');
        }
    }
    
    /**
     *  Check if the connection is valid
     *  @return boolean
     */
    public function valid()
    {
        // the exchange must be set
        return is_object($this->_exchange);
    }
    
    /**
     * Sends the message to the MailerQ
     * @param $properties
     * @return boolean
     */
    public function publishMessage(array $properties)
    {
        // exchange must be available
        if (!is_object($this->_exchange)) return false;
        
        // prevent AMQP exceptions
        try
        {
            // publish the message
            return $this->_exchange->publish(json_encode($properties), "0");
        }
        catch (AMQPException $exception)
        {
            // it really failed
            throw new Swift_TransportException('Message cannot be published. Exchange was not available.');
        }
    }
}




