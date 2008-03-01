<?php

/*
 CommandEvent class in Swift Mailer.
 
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
 * Generated when a command is sent over an SMTP connection.
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
class Swift_Events_CommandEvent extends Swift_Events_EventObject
{
  
  /**
   * The command sent to the server.
   * @var string
   */
  public $command;
  
  /**
   * An array of codes which a successful response will contain.
   * @var int[]
   */
  public $successCodes = array();
  
  /**
   * Get the command which was sent to the server.
   * @return string
   */
  public function getCommand()
  {
    return $this->command;
  }
  
  /**
   * Get the numeric response codes which indicate success for this command.
   * @return int[]
   */
  public function getSuccessCodes()
  {
    return $this->successCodes;
  }
  
  /**
   * Create a clean clone.
   */
  public function __clone()
  {
    parent::__clone();
    $this->command = '';
    $this->successCodes = array();
  }
  
}