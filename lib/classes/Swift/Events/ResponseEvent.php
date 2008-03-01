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
  
  /** The response is correct */
  const RESULT_VALID = 0x01;
  
  /** The response is incorrect */
  const RESULT_INVALID = 0x10;
  
  /**
   * The overall result as a bitmask from the class constants.
   * @var int
   */
  public $result = self::RESULT_VALID;
  
  /**
   * The response received from the server.
   * @var string
   */
  public $response;
  
  /**
   * Get the response which was received from the server.
   * @return string
   */
  public function getResponse()
  {
    return $this->response;
  }
  
  /**
   * Get the result of this Event.
   * The return value is a bitmask from {@link RESULT_VALID, RESULT_INVALID}
   * @return int
   */
  public function getResult()
  {
    return $this->result;
  }
  
  /**
   * Create a clean clone.
   */
  public function __clone()
  {
    parent::__clone();
    $this->response = '';
    $this->result = self::RESULT_VALID;
  }
  
}