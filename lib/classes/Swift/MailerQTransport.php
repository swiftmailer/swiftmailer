<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * MailerQTransport for sending mail through a MailerQ server.
 *
 * @package    Swift
 * @subpackage Transport
 * @author     http://mailerq.com
 */
class Swift_MailerQTransport extends Swift_Transport_MailerQTransport
{
    /**
     * Constructs the MailerQTransport class
     * 
     * @param   string $hostname
     * @param   string $exchange
     * @param   string $login
     * @param   string $password
     * @param   string $vhost
     * @param   array  $options Associative array of additional options, supported are:
     *                              - maxdeliverytime
     *                              - maxattepmts
     *                              - ips
     *                              - helo
     *                              - key
     *                              - keepbody
     *                              - queues
     *                          Along with the options you can send your custom property.
     *                          Documentation: http://www.mailerq.com/documentation/send-email#Properties
     */
    public function __construct($hostname = 'localhost', $exchange = 'outbox', $login = 'guest', $password = 'guest', $vhost = '/', $options = array())
    {
        $arguments = Swift_DependencyContainer::getInstance()
            ->createDependenciesFor('transport.mailerq');
        
        $this->setHostname($hostname);
        $this->setExchange($exchange);
        $this->setLogin($login);
        $this->setPassword($password);
        $this->setVhost($vhost);
        $this->setOptions($options);
        
        call_user_func_array(
            array($this, 'Swift_Transport_MailerQTransport::__construct'),
            $arguments
        );        
    }

    /**
     * Creates a new MailerQTransport instance.
     * 
     * @param string $hostname
     * @param string $exchange
     * @param string $login
     * @param string $password
     * @param string $vhost
     * @param array  $options Additional options to send along with the message
     */
    public static function newInstance($hostname = 'localhost', $exchange = 'outbox', $login = 'guest', $password = 'guest', $vhost = '/', $options = array())
    {
        return new self($hostname, $exchange, $login, $password, $vhost, $options);
    }
}
