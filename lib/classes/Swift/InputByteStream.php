<?php

/*
 Input ByteStream (for writing) in Swift Mailer.
 
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
 * An abstract means of writing data.
 * Classes implementing this interface may use a subsystem which requires less
 * memory than working with large strings of data.
 * @package Swift
 * @subpackage ByteStream
 * @author Chris Corbyn
 */
interface Swift_InputByteStream
{
  
  /**
   * Writes $bytes to the end of the stream.
   * @param string $bytes
   */
  public function write($bytes);
  
  /**
   * Flush the contents of the stream (empty it) and set the internal pointer
   * to the beginning.
   */
  public function flushContents();
  
}
