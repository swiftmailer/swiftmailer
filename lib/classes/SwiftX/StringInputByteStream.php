<?php

/*
 StringInputByteStream for Swift Mailer.
 
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
 * Reads byte values from a string.
 * 
 * @package Swift
 * @subpackage Streams
 * 
 * @author Chris Corbyn
 */
class SwiftX_StringInputByteStream extends SwiftX_AbstractInputByteStream
{
  
  /** String input source */
  private $_string;
  
  /** Length of string */
  private $_length = 0;
  
  /** Current position in string */
  private $_position = 0;
  
  /**
   * Create a new StringInputByteStream from $string.
   * 
   * @param string $string
   */
  public function __construct($string)
  {
    $this->_string = $string;
    $this->_length = strlen($string);
  }
  
  /**
   * Boolean test to see if bytes are available for reading in the stream.
   * 
   * @return boolean
   */
  public function hasAvailable()
  {
    return $this->_position < $this->_length;
  }
  
  /**
   * Read the value of the next byte in the input.
   * 
   * The value of the byte is returned, or -1 if no more bytes are available.
   * 
   * @return int
   */
  public function readNext()
  {
    if ($this->_position >= $this->_length)
    {
      return -1;
    }
    
    return self::$_byteMap[$this->_string[$this->_position++]];
  }
  
  /**
   * Read bytes from the input into the buffer by-reference.
   * 
   * if $limit is specified, no more than $limit will be read.
   * 
   * If $buf is not empty, bytes will be added to it, not overwritten.
   * 
   * @param array $buf
   * @param int $limit maximum read
   * 
   * @return int
   */
  public function read(&$buf = null, $limit = 8192)
  {
    if ($this->_position >= $this->_length)
    {
      return -1;
    }
    
    if ($buf === null)
    {
      $buf = array();
    }
    
    $il = $this->_position + $limit;
    $startPosition = $this->_position;
    
    for ($i = $this->_position; $i < $il && $this->_position < $this->_length; ++$i)
    {
      $buf[] = self::$_byteMap[$this->_string[$this->_position++]];
    }
    
    return $this->_position - $startPosition;
  }
  
  /**
   * Read the position of the internal pointer and/or set it.
   * 
   * Without any arguments this method simply returns the current position.
   * If $offset is specified the position will be updated to $offset first.
   * 
   * @param int $offset
   * 
   * @return int
   */
  public function position($set = null)
  {
    if (isset($set))
    {
      if ($set < 0 || $set >= $this->_length)
      {
        throw new Swift_IoException(sprintf('Offset %d out of bounds', $set));
      }
      $this->_position = $set;
    }
    return $this->_position;
  }
  
  /**
   * Does nothing.
   */
  public function close()
  {
  }
  
}
