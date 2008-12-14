<?php

/*
 ResponseEvent class in Swift Mailer.
 
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

/**
 * Generated when a response is received on a SMTP connection.
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
class Swift_Events_ResponseEvent extends Swift_Events_EventObject
{
  
  /**
   * The overall result.
   * @var boolean
   */
  private $_valid;
  
  /**
   * The response received from the server.
   * @var string
   */
  private $_response;
  
  /**
   * Create a new ResponseEvent for $source and $response.
   * @param Swift_Transport $source
   * @param string $response
   * @param boolean $valid
   */
  public function __construct(Swift_Transport $source, $response, $valid = false)
  {
    parent::__construct($source);
    $this->_response = $response;
    $this->_valid = $valid;
  }
  
  /**
   * Get the response which was received from the server.
   * @return string
   */
  public function getResponse()
  {
    return $this->_response;
  }
  
  /**
   * Get the success status of this Event.
   * @return boolean
   */
  public function isValid()
  {
    return $this->_valid;
  }
  
}