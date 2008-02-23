<?php

/*
 Array based transport log from Swift Mailer.
 
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

//@require 'Swift/Transport/Log.php';

/**
 * A transport log which uses an array.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_Log_ArrayLog implements Swift_Transport_Log
{
  
  /**
   * The state of this log.
   * @var boolean
   * @access private
   */
  private $_enabled = false;
  
  /**
   * Entries in this log.
   * @var string[]
   * @access private
   */
  private $_logEntries = array();
  
  /**
   * Enable or disable logging.
   * @param boolean $enabled
   */
  public function setLogEnabled($enabled = true)
  {
    $this->_enabled = $enabled;
  }
  
  /**
   * Add a log entry.
   * @param string $entry
   */
  public function addLogEntry($entry)
  {
    if ($this->_enabled)
    {
      $this->_logEntries[] = $entry;
    }
  }
  
  /**
   * Clear the log contents.
   */
  public function clearLog()
  {
    $this->_logEntries = array();
  }
  
  /**
   * Get this log as a string.
   * @return string
   */
  public function dump()
  {
    return implode(PHP_EOL, $this->_logEntries);
  }
  
}
