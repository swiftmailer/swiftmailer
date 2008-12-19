<?php

/*
 ByteArrayReplacementFilter from Swift Mailer.
 
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
 * This stream filter deals with Byte arrays rather than simple strings.
 * @package Swift
 * @author Chris Corbyn
 */
class Swift_StreamFilters_ByteArrayReplacementFilter
  implements Swift_StreamFilter
{
  
  /** The needle(s) to search for */
  private $_search;
  
  /** The replacement(s) to make */
  private $_replace;
  
  /**
   * Create a new ByteArrayReplacementFilter with $search and $replace.
   * @param array $search
   * @param array $replace
   */
  public function __construct($search, $replace)
  {
    $this->_search = $search;
    $this->_replace = $replace;
  }
  
  /**
   * Returns true if based on the buffer passed more bytes should be buffered.
   * @param array $buffer
   * @return boolean
   */
  public function shouldBuffer($buffer)
  {
    $endOfBuffer = end($buffer);
    foreach ($this->_search as $search)
    {
      if (is_array($search))
      {
        if (in_array($endOfBuffer, $search))
        {
          return true;
        }
      }
      else
      {
        return in_array($endOfBuffer, $this->_search);
      }
    }
    
    return false;
  }
  
  /**
   * Perform the actual replacements on $buffer and return the result.
   * @param array $buffer
   * @return array
   */
  public function filter($buffer)
  {
    $newBuffer = $buffer;
    
    foreach ($this->_search as $i => $search)
    {
      if (is_array($search))
      {
        //TODO: Can this be optimized?
        $replace = (isset($this->_replace[$i]) && is_array($this->_replace[$i]))
          ? $this->_replace[$i]
          : $this->_replace
          ;
        $newBuffer = $this->_filterByNeedle($newBuffer, $search, $replace);
      }
      else
      {
        return $this->_filterByNeedle($buffer, $this->_search, $this->_replace);
      }
    }
    
    return $newBuffer;
  }
  
  // -- Private Methods
  
  private function _filterByNeedle($buffer, $needle, $replace)
  {
    //TODO: Find a way to optimize this
    $newBuffer = $buffer;
    
    for ($i = 0; $i < count($newBuffer); ++$i)
    {
      // If the elements from this point, for as far as the search match
      // the search itself, then splice the replacement into its position
      if (array_slice($newBuffer, $i, count($needle)) == $needle)
      {
        array_splice($newBuffer, $i, count($needle), $replace);
        
        // Now move the pointer to the index after the replaced section
        // allowing for the ++$i in the loop
        $i += count($replace) - 1;
      }
    }
    return $newBuffer;
  }
  
}
