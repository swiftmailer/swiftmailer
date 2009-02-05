<?php

/*
 High-availability failover Transport class from Swift Mailer.
 
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

//@require 'Swift/Transport/LoadBalancedTransport.php';
//@require 'Swift/Mime/Message.php';

/**
 * Contains a list of redundant Transports so when one fails, the next is used.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_FailoverTransport
  extends Swift_Transport_LoadBalancedTransport
{
  
  /**
   * Registered transport curently used.
   * @var Swift_Transport
   * @access private
   */
  private $_currentTransport;
  
  /**
   * Creates a new FailoverTransport.
   */
  public function __construct()
  {
    parent::__construct();
  }
  
  /**
   * Send the given Message.
   * Recipient/sender data will be retreived from the Message API.
   * The return value is the number of recipients who were accepted for delivery.
   * @param Swift_Mime_Message $message
   * @param string[] &$failedRecipients to collect failures by-reference
   * @return int
   */
  public function send(Swift_Mime_Message $message, &$failedRecipients = null)
  {
    $maxTransports = count($this->_transports);
    $sent = 0;
    
    for ($i = 0; $i < $maxTransports
      && $transport = $this->_getNextTransport(); ++$i)
    {
      try
      {
        if (!$transport->isStarted())
        {
          $transport->start();
        }
        
        return $transport->send($message, $failedRecipients);
      }
      catch (Swift_TransportException $e)
      {
        $this->_killCurrentTransport();
      }
    }
    
    if (count($this->_transports) == 0)
    {
      throw new Swift_TransportException(
        'All Transports in FailoverTransport failed, or no Transports available'
        );
    }
    
    return $sent;
  }
  
  // -- Protected methods
  
  protected function _getNextTransport()
  {
    if (!isset($this->_currentTransport))
    {
      $this->_currentTransport = parent::_getNextTransport();
    }
    return $this->_currentTransport;
  }
  
  protected function _killCurrentTransport()
  {
    $this->_currentTransport = null;
    parent::_killCurrentTransport();
  }
  
}
