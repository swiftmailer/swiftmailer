<?php

/*
 EchoLogger from Swift Mailer.
 
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
 * Prints all log messages in real time.
 * 
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Plugins_Loggers_EchoLogger implements Swift_Plugins_Logger
{
  
  /** Whether or not HTML should be output */
  private $_isHtml;
  
  /**
   * Create a new EchoLogger.
   * 
   * @param boolean $isHtml
   */
  public function __construct($isHtml = true)
  {
    $this->_isHtml = $isHtml;
  }
  
  /**
   * Add a log entry.
   * @param string $entry
   */
  public function add($entry)
  {
    if ($this->_isHtml)
    {
      printf('%s%s%s', htmlspecialchars($entry, ENT_QUOTES), '<br />', PHP_EOL);
    }
    else
    {
      printf('%s%s', $entry, PHP_EOL);
    }
  }
  
  /**
   * Not implemented.
   */
  public function clear()
  {
  }
  
  /**
   * Not implemented.
   */
  public function dump()
  {
  }
  
}
