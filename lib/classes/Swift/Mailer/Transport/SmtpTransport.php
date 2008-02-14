<?php

/*
 The SMTP Transport from Swift Mailer.
 
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

//@require 'Swift/Mailer/Transport.php';
//@require 'Swift/Mailer/Transport/IoBuffer.php';
//@require 'Swift/Mime/Message.php';

/**
 * Sends Messages over SMTP.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Mailer_Transport_SmtpTransport implements Swift_Mailer_Transport
{
  
  /**
   * An Input-Output buffer for sending/receiving SMTP commands and responses.
   * @var Swift_Mailer_Transport_IoBuffer
   * @access private
   */
  private $_buffer;
  
  /**
   * Creates a new SmtpTransport using the given I/O buffer.
   * @param Swift_Mailer_Transport_IoBuffer $buf
   */
  public function __construct(Swift_Mailer_Transport_IoBuffer $buf)
  {
    $this->_buffer = $buf;
  }
  
  /**
   * Test if an SMTP connection has been established.
   * @return boolean
   */
  public function isStarted()
  {
  }
  
  /**
   * Start the SMTP connection.
   */
  public function start()
  {
  }
  
  /**
   * Stop the SMTP connection.
   */
  public function stop()
  {
  }
  
  /**
   * Send the given Message.
   * Recipient/sender data will be retreived from the Message API.
   * The return value is the number of recipients who were accepted for delivery.
   * @param Swift_Mime_Message $message
   * @return int
   */
  public function send(Swift_Mime_Message $message)
  {
  }
  
}
