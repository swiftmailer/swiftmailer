<?php

/*
 StringReplacementFilter from Swift Mailer.
 
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

//@require 'Swift/StreamFilter.php';

/**
 * Processes bytes as they pass through a buffer and replaces sequences in it.
 * @package Swift
 * @author Chris Corbyn
 */
class Swift_StreamFilters_StringReplacementFilter implements Swift_StreamFilter
{
  
  /** The needle(s) to search for */
  private $_search;
  
  /** The replacement(s) to make */
  private $_replace;
  
  /**
   * Create a new StringReplacementFilter with $search and $replace.
   * @param string|array $search
   * @param string|array $replace
   */
  public function __construct($search, $replace)
  {
    $this->_search = $search;
    $this->_replace = $replace;
  }
  
  /**
   * Returns true if based on the buffer passed more bytes should be buffered.
   * @param string $buffer
   * @return boolean
   */
  public function shouldBuffer($buffer)
  {
    $endOfBuffer = substr($buffer, -1);
    foreach ((array) $this->_search as $needle)
    {
      if (false !== strpos($needle, $endOfBuffer))
      {
        return true;
      }
    }
    return false;
  }
  
  /**
   * Perform the actual replacements on $buffer and return the result.
   * @param string $buffer
   * @return string
   */
  public function filter($buffer)
  {
    return str_replace($this->_search, $this->_replace, $buffer);
  }
  
}
