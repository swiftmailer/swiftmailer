<?php

/*
 TransportExceptionEvent class in Swift Mailer.
 
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

//@require 'Swift/Events/EventObject.php';
//@require 'Swift/TransportException.php';

/**
 * Generated when a TransportException is thrown from the Transport system.
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
class Swift_Events_TransportExceptionEvent extends Swift_Events_EventObject
{
  
  /**
   * The Exception thrown.
   * @var Swift_TransportException
   */
  private $_exception;
  
  /**
   * Create a new TransportExceptionEvent for $transport.
   * @param Swift_Transport $transport
   * @param Swift_TransportException $ex
   */
  public function __construct(Swift_Transport $transport,
    Swift_TransportException $ex)
  {
    parent::__construct($transport);
    $this->_exception = $ex;
  }
  
  /**
   * Get the TransportException thrown.
   * @return Swift_TransportException
   */
  public function getException()
  {
    return $this->_exception;
  }
  
}
