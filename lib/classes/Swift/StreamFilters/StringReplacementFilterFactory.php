<?php

/*
 StringReplacementFilterFactory from Swift Mailer.
 
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

//@require 'Swift/StreamFilters/StringReplacementFilter.php';
//@require 'Swift/StreamFilterFactory.php';

/**
 * Creates filters for replacing needles in a string buffer.
 * @package Swift
 * @author Chris Corbyn
 */
class Swift_StreamFilters_StringReplacementFilterFactory
  implements Swift_ReplacementFilterFactory
{
  
  /** Lazy-loaded filters */
  private $_filters = array();
  
  /**
   * Create a new StreamFilter to replace $search with $replace in a string.
   * @param string $search
   * @param string $replace
   * @return Swift_StreamFilter
   */
  public function createFilter($search, $replace)
  {
    if (!isset($this->_filters[$search][$replace]))
    {
      if (!isset($this->_filters[$search]))
      {
        $this->_filters[$search] = array();
      }
      
      if (!isset($this->_filters[$search][$replace]))
      {
        $this->_filters[$search][$replace] = array();
      }
      
      $this->_filters[$search][$replace]
        = new Swift_StreamFilters_StringReplacementFilter($search, $replace);
    }
    
    return $this->_filters[$search][$replace];
  }
  
}
