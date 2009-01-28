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
  
  /** The Index for searching */
  private $_index;
  
  /**
   * Create a new ByteArrayReplacementFilter with $search and $replace.
   * @param array $search
   * @param array $replace
   */
  public function __construct($search, $replace)
  {
    $this->_search = $search;
    $this->_index = array ();
    foreach ($search as $search_element)
    {
      if (is_array($search_element))
      {
        foreach ($search_element as $char)
        {
          $this->_index[$char] = true;
        }
      }
      else
      {
        $this->_index[$search_element] = true;
      }
    }
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
    return isset ($this->_index[$endOfBuffer]);
  }
  
  /**
   * Perform the actual replacements on $buffer and return the result.
   * @param array $buffer
   * @return array
   */
  public function filter($buffer)
  {
    //TODO: optimize, use one pass tree based search
    
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
    $newBuffer = array();
    // Init
    $needle_size = count($needle);
    $found = $needle_size - 1;
    $count = count ($buffer);
    $max_pos = $count - $found;
    for ($i = 0; $i < $max_pos; ++$i)
    {
      for ($j = 0; $j < $needle_size; ++$j)
      {
        if ($buffer[$i + $j] != $needle[$j])
        {
          break;
        }
        if ($j == $found)
        {
          $newBuffer = array_merge($newBuffer, $replace);
          $i += $j;
          continue 2;
        }
      }
      $newBuffer[] = $buffer[$i];
    }
    for(; $i < $count; ++$i)
    {
      $newBuffer[] = $buffer[$i];
    }
    return $newBuffer;
  }

}
