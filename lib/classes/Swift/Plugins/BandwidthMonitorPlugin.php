<?php

/*
 AntiFlood plugin in Swift Mailer.
 
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

//@require 'Swift/Events/SendListener.php';
//@require 'Swift/Events/SendEvent.php';
//@require 'Swift/Events/CommandListener.php';
//@require 'Swift/Events/CommandEvent.php';
//@require 'Swift/Events/ResponseListener.php';
//@require 'Swift/Events/ResponseEvent.php';
//@require 'Swift/InputByteStream.php';

/**
 * Reduces network flooding when sending large amounts of mail.
 * @package Swift
 * @subpackage Plugins
 * @author Chris Corbyn
 */
class Swift_Plugins_BandwidthMonitorPlugin
  implements Swift_Events_SendListener, Swift_Events_CommandListener,
  Swift_Events_ResponseListener, Swift_InputByteStream
{
  
  /**
   * The outgoing traffic counter.
   * @var int
   * @access private
   */
  private $_out = 0;
  
  /**
   * The incoming traffic counter.
   * @var int
   * @access private
   */
  private $_in = 0;
  
  /**
   * Not used.
   */
  public function beforeSendPerformed(Swift_Events_SendEvent $evt)
  {
  }
  
  /**
   * Invoked immediately after the Message is sent.
   * @param Swift_Events_SendEvent $evt
   */
  public function sendPerformed(Swift_Events_SendEvent $evt)
  {
    $message = $evt->getMessage();
    $message->toByteStream($this);
  }
  
  /**
   * Invoked immediately following a command being sent.
   * @param Swift_Events_ResponseEvent $evt
   */
  public function commandSent(Swift_Events_CommandEvent $evt)
  {
    $command = $evt->getCommand();
    $this->_out += strlen($command);
  }
  
  /**
   * Invoked immediately following a response coming back.
   * @param Swift_Events_ResponseEvent $evt
   */
  public function responseReceived(Swift_Events_ResponseEvent $evt)
  {
    $response = $evt->getResponse();
    $this->_in += strlen($response);
  }
  
  /**
   * Called when a message is sent so that the outgoing counter can be increased.
   * @param string $bytes
   */
  public function write($bytes)
  {
    $this->_out += strlen($bytes);
  }
  
  /**
   * Not used.
   */
  public function flushContents()
  {
  }
  
  /**
   * Get the total number of bytes sent to the server.
   * @return int
   */
  public function getBytesOut()
  {
    return $this->_out;
  }
  
  /**
   * Get the total number of bytes received from the server.
   * @return int
   */
  public function getBytesIn()
  {
    return $this->_in;
  }
  
  /**
   * Reset the internal counters to zero.
   */
  public function reset()
  {
    $this->_out = 0;
    $this->_in = 0;
  }
  
}
