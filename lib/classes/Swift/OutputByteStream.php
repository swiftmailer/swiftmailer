<?php

/*
 Output ByteStream (for reading) in Swift Mailer.
 
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
 * An abstract means of reading data.
 * Classes implementing this interface may use a subsystem which requires less
 * memory than working with large strings of data.
 * @package Swift
 * @subpackage ByteStream
 * @author Chris Corbyn
 */
interface Swift_OutputByteStream
{
  
  /**
   * Reads $length bytes from the stream into a string and moves the pointer
   * through the stream by $length. If less bytes exist than are requested the
   * remaining bytes are given instead. If no bytes are remaining at all, boolean
   * false is returned.
   * @param int $length
   * @return string
   * @throws Swift_IoException
   */
  public function read($length);
  
  /**
   * Move the internal read pointer to $byteOffset in the stream.
   * @param int $byteOffset
   * @return boolean
   * @throws Swift_IoException
   */
  public function setReadPointer($byteOffset);
  
}
