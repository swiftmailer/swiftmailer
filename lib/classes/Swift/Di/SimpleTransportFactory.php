<?php

/*
 Dependency Injection factory for Transpors in Swift Mailer.
 
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

//@require 'Swift/TransportFactory.php';

/**
 * The standard factory for making classes from the Transport subpackage.
 * @package Swift
 * @author Chris Corbyn
 */
class Swift_Di_SimpleTransportFactory extends Swift_TransportFactory
{
  
  /**
   * Create a new Smtp Transport.
   * @param string $host
   * @param int $port
   * @param string $encryption
   * @return Swift_Transport_EsmtpTransport
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
   * @return Swift_Transport_SendmailTransport
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
   * @return Swift_Transport_MailTransport
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
   * @return Swift_Transport_FailoverTransport
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
   * @return Swift_Transport_LoadBalancedTransport
   */
  public function createLoadBalanced($transports = array())
  {
    $balanced = $this->create('failover');
    $balanced->setTransports($transports);
    return $balanced;
  }
  
}
