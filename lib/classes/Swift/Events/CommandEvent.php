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
//@require 'Swift/Transport.php';

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
  private $_command;
  
  /**
   * An array of codes which a successful response will contain.
   * @var int[]
   */
  private $_successCodes = array();
  
  /**
   * Create a new CommandEvent for $source with $command.
   * @param Swift_Transport $source
   * @param string $command
   * @param array $successCodes
   */
  public function __construct(Swift_Transport $source,
    $command, $successCodes = array())
  {
    parent::__construct($source);
    $this->_command = $command;
    $this->_successCodes = $successCodes;
  }
  
  /**
   * Get the command which was sent to the server.
   * @return string
   */
  public function getCommand()
  {
    return $this->_command;
  }
  
  /**
   * Get the numeric response codes which indicate success for this command.
   * @return int[]
   */
  public function getSuccessCodes()
  {
    return $this->_successCodes;
  }
  
}