<?php

/*
 InputByteStream API (for reading from input sources) for Swift Mailer.
 
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
 * Reads byte values from a stream of input.
 * 
 * @package Swift
 * @subpackage Streams
 * 
 * @author Chris Corbyn
 */
interface SwiftX_InputByteStream
{
  
  /**
   * Boolean test to see if bytes are available for reading in the stream.
   * 
   * @return boolean
   */
  public function hasAvailable();
  
  /**
   * Read the value of the next byte in the input.
   * 
   * The value of the byte is returned, or -1 if no more bytes are available.
   * 
   * @return int
   */
  public function readNext();
  
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
  public function read(&$buf = array(), $limit = 8192);
  
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
  public function position($offset = null);
  
  /**
   * Close the internal input source and free up resources.
   * 
   * For some streams that do not need to close the source this method may do
   * nothing.
   */
  public function close();
  
}
