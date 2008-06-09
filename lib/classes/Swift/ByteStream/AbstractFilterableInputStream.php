<?php

/*
 Bi-Directional FileStream in Swift Mailer.
 
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

//@require 'Swift/InputByteStream.php';
//@require 'Swift/Filterable.php';
//@require 'Swift/StreamFilter.php';

/**
 * Provides the base functionality for an InputStream supporting filters.
 * @package Swift
 * @subpackage ByteStream
 * @author Chris Corbyn
 */
abstract class Swift_ByteStream_AbstractFilterableInputStream
  implements Swift_InputByteStream, Swift_Filterable
{
  
  /** StreamFilters */
  private $_filters = array();
  
  /** A buffer for writing */
  private $_writeBuffer = '';
  
  /** Write-through ByteStream */
  private $_writeThrough = null;
  
  /**
   * Commit the given bytes to the storage medium immediately.
   * @param string $bytes
   * @access protected
   */
  abstract protected function _commit($bytes);
  
  /**
   * Add a StreamFilter to this InputByteStream.
   * @param Swift_StreamFilter $filter
   * @param string $key
   */
  public function addFilter(Swift_StreamFilter $filter, $key)
  {
    $this->_filters[$key] = $filter;
  }
  
  /**
   * Remove an already present StreamFilter based on its $key.
   * @param string $key
   */
  public function removeFilter($key)
  {
    unset($this->_filters[$key]);
  }
  
  /**
   * Writes $bytes to the end of the stream.
   * @param string $bytes
   * @param Swift_InputByteStream $is, optional
   */
  public function write($bytes, Swift_InputByteStream $is = null)
  {
    $this->_writeBuffer .= $bytes;
    $shouldBuffer = false;
    foreach ($this->_filters as $filter)
    {
      if ($filter->shouldBuffer($this->_writeBuffer))
      {
        $this->_writeThrough = $is;
        return;
      }
    }
    $this->_doWrite($this->_writeBuffer, $is);
  }
  
  /**
   * Flush the contents of the stream (empty it) and set the internal pointer
   * to the beginning.
   */
  public function flushBuffers()
  {
    if (isset($this->_writeBuffer))
    {
      $this->_doWrite($this->_writeBuffer, $this->_writeThrough);
    }
  }
  
  // -- Private methods
  
  /** Just write the bytes to the stream */
  private function _doWrite($bytes, Swift_InputByteStream $is = null)
  {
    foreach ($this->_filters as $filter)
    {
      $bytes = $filter->filter($bytes);
    }
    
    $this->_commit($bytes);
    
    if (isset($is))
    {
      $is->write($bytes);
    }
    $this->_writeBuffer = '';
    $this->_writeThrough = null;
  }
  
}
