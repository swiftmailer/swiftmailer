<?php

/*
 Abstract Dependency Injection factory for Transport components in Swift Mailer.
 
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
 */

//@require 'Swift/Di.php';

/**
 * The abstract factory for making Transport components.
 * @package Swift
 * @author Chris Corbyn
 */
class Swift_TransportFactory
{
  
  /** Constant for using SMTP with TLS encryption */
  const SMTP_ENC_TLS = 'tls';
  
  /** Constant for using SMTP with SSL encryption */
  const SMTP_ENC_SSL = 'ssl';
  
  /** Constant for using SMTP without encryption */
  const SMTP_ENC_NONE = 'tcp';
  
  /**
   * Singleton instance.
   * @var Swift_TransportFactory
   * @access private
   */
  private static $_instance = null;
  
  /**
   * Constructor cannot be used.
   * @access private
   */
  private function __construct()
  {
  }
  
  /**
   * Get an instance as a singleton.
   * @return Swift_TransportFactory
   */
  public static function getInstance()
  {
    if (!isset(self::$_instance))
    {
      self::$_instance = new self();
    }
    return self::$_instance;
  }
  
  /**
   * Create a new instance of the component named $name.
   * @param string $name
   * @param array $lookup to override any pre-defined lookups
   * @return object
   * @throws Exception if no such component exists
   */
  public function create($name, $lookup = array(), $fqName = false)
  {
    $name = $fqName ? $name : sprintf('transport.%s', $name);
    return Swift_Di::getInstance()->create($name, $lookup);
  }
  
  /**
   * Create a new Smtp Transport.
   * @param string $host
   * @param int $port
   * @param string $encryption
   * @return Swift_Transport
   */
  public function createSmtp($host = null, $port = null,
    $encryption = self::SMTP_ENC_NONE)
  {
    $smtp = $this->create('smtp');
    if ($host)
    {
      $smtp->setHost($host);
    }
    if ($port)
    {
      $smtp->setPort($port);
    }
    if ($encryption)
    {
      $smtp->setEncryption($encryption);
    }
    return $smtp;
  }
    
  /**
   * Create a new Sendmail Transport.
   * @param string $command
   * @return Swift_Transport
   */
  public function createSendmail($command = null)
  {
    $sendmail = $this->create('sendmail');
    if ($command)
    {
      $sendmail->setCommand($command);
    }
    return $sendmail;
  }
  
  /**
   * Create a new Mail (mail() function) Transport.
   * @param string $params for $additional_params in mail()
   * @return Swift_Transport
   */
  public function createMail($params = null)
  {
    $mail = $this->create('mail');
    if ($params)
    {
      $mail->setExtraParams($params);
    }
    return $mail;
  }
  
  /**
   * Create a new Failover Transport.
   * @param Swift_Transport[] $transports
   * @return Swift_Transport
   */
  public function createFailover($transports = array())
  {
    $failover = $this->create('failover');
    $failover->setTransports($transports);
    return $failover;
  }
  
  /**
   * Create a new load balanced Transport.
   * @param Swift_Transport[] $transports
   * @return Swift_Transport
   */
  public function createLoadBalanced($transports = array())
  {
    $balanced = $this->create('failover');
    $balanced->setTransports($transports);
    return $balanced;
  }
  
}
