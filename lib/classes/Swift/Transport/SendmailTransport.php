<?php

/*
 Sendmail Transport from Swift Mailer.
 
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

//@require 'Swift/Transport/EsmtpTransport.php';
//@require 'Swift/Transport/IoBuffer.php';

/**
 * SendmailTransport for sending mail through a sendmail/postfix (etc..) binary.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_SendmailTransport extends Swift_Transport_EsmtpTransport
{
  
  /**
   * Create a new SendmailTransport with $buf for I/O.
   * @param Swift_Transport_IoBuffer $buf
   */
  public function __construct(Swift_Transport_IoBuffer $buf)
  {
    parent::__construct($buf, array());
    $this->_params['command'] = '/usr/sbin/sendmail -bs';
    $this->_params['type'] = Swift_Transport_IoBuffer::TYPE_PROCESS;
  }
  
  public function setCommand($command)
  {
    $this->_params['command'] = $command;
  }
  
  public function getCommand()
  {
    return $this->_params['command'];
  }
  
  public function send(Swift_Mime_Message $message)
  {
    $command = $this->getCommand();
    $buffer = $this->getBuffer();
    if (false !== strpos($command, ' -t'))
    {
      $stripDot = (
        false === strpos($command, ' -i')
        && false === strpos($command, ' -oi')
        );
      $buffer->initialize($this->_params);
      if ($stripDot)
      {
        $buffer->setWriteTranslations(array("\r\n."=>"\r\n..", "\r\n"=>PHP_EOL));
      }
      else
      {
        $buffer->setWriteTranslations(array("\r\n"=>PHP_EOL));
      }
      $count = count((array) $message->getTo())
        + count((array) $message->getCc())
        + count((array) $message->getBcc())
        ;
      $message->toByteStream($buffer);
      $buffer->setWriteTranslations(array());
      $buffer->terminate();
    }
    elseif (false !== strpos($command, ' -bs'))
    {
      $count = parent::send($message);
    }
    else
    {
      throw new Swift_Transport_TransportException(
        'Unsupported sendmail command flags [' . $command . ']. ' .
        'Must be one of "-bs", "-t", "-i -t" or "-oi -t"'
        );
    }
    
    return $count;
  }
  
}
