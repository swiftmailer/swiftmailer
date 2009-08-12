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
  
  /** Write sequence */
  private $_sequence = 0;
  
  /** StreamFilters */
  private $_filters = array();
  
  /** A buffer for writing */
  private $_writeBuffer = '';
  
  /** Bound streams */
  private $_mirrors = array();
  
  /**
   * Commit the given bytes to the storage medium immediately.
   * @param string $bytes
   * @access protected
   */
  abstract protected function _commit($bytes);
  
  /**
   * Flush any buffers/content with immediate effect.
   * @access protected
   */
  abstract protected function _flush();
  
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
   * @throws Swift_IoException
   */
  public function write($bytes)
  {
    $this->_writeBuffer .= $bytes;
    foreach ($this->_filters as $filter)
    {
      if ($filter->shouldBuffer($this->_writeBuffer))
      {
        return;
      }
    }
    $this->_doWrite($this->_writeBuffer);
    return ++$this->_sequence;
  }
  
  /**
   * For any bytes that are currently buffered inside the stream, force them
   * off the buffer.
   * 
   * @throws Swift_IoException
   */
  public function commit()
  {
    $this->_doWrite($this->_writeBuffer);
  }
  
  /**
   * Attach $is to this stream.
   * The stream acts as an observer, receiving all data that is written.
   * All {@link write()} and {@link flushBuffers()} operations will be mirrored.
   * 
   * @param Swift_InputByteStream $is
   */
  public function bind(Swift_InputByteStream $is)
  {
    $this->_mirrors[] = $is;
  }
  
  /**
   * Remove an already bound stream.
   * If $is is not bound, no errors will be raised.
   * If the stream currently has any buffered data it will be written to $is
   * before unbinding occurs.
   * 
   * @param Swift_InputByteStream $is
   */
  public function unbind(Swift_InputByteStream $is)
  {
    foreach ($this->_mirrors as $k => $stream)
    {
      if ($is === $stream)
      {
        if ($this->_writeBuffer !== '')
        {
          $stream->write($this->_filter($this->_writeBuffer));
        }
        unset($this->_mirrors[$k]);
      }
    }
  }
  
  /**
   * Flush the contents of the stream (empty it) and set the internal pointer
   * to the beginning.
   * @throws Swift_IoException
   */
  public function flushBuffers()
  {
    if ($this->_writeBuffer !== '')
    {
      $this->_doWrite($this->_writeBuffer);
    }
    $this->_flush();
    
    foreach ($this->_mirrors as $stream)
    {
      $stream->flushBuffers();
    }
  }
  
  // -- Private methods
  
  /** Run $bytes through all filters */
  private function _filter($bytes)
  {
    foreach ($this->_filters as $filter)
    {
      $bytes = $filter->filter($bytes);
    }
    return $bytes;
  }
  
  /** Just write the bytes to the stream */
  private function _doWrite($bytes)
  {
    $this->_commit($this->_filter($bytes));
    
    foreach ($this->_mirrors as $stream)
    {
      $stream->write($bytes);
    }
    
    $this->_writeBuffer = '';
  }
  
}
