<?php

/*
 ArrayLogger from Swift Mailer.
 
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

/**
 * Logs to an Array backend.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Plugins_Loggers_ArrayLogger implements Swift_Plugins_Logger
{
  
  /**
   * The log contents.
   * @var array
   * @access private
   */
  private $_log = array();
  
  /**
   * Max size of the log.
   * @var int
   * @access private
   */
  private $_size = 0;
  
  /**
   * Create a new ArrayLogger with a maximum of $size entries.
   * @var int $size
   */
  public function __construct($size = 50)
  {
    $this->_size = $size;
  }
  
  /**
   * Add a log entry.
   * @param string $entry
   */
  public function add($entry)
  {
    $this->_log[] = $entry;
    while (count($this->_log) > $this->_size)
    {
      array_shift($this->_log);
    }
  }
  
  /**
   * Clear the log contents.
   */
  public function clear()
  {
    $this->_log = array();
  }
  
  /**
   * Get this log as a string.
   * @return string
   */
  public function dump()
  {
    return implode(PHP_EOL, $this->_log);
  }
  
}
