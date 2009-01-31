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
   * 
   * Writing may not happen immediately if the stream chooses to buffer.  If
   * you want to write these bytes with immediate effect, call {@link commit()}
   * after calling write().
   * 
   * This method returns the sequence ID of the write (i.e. 1 for first, 2 for
   * second, etc etc).
   *
   * @param string $bytes
   * @return int
   * @throws Swift_IoException
   */
  public function write($bytes);
  
  /**
   * For any bytes that are currently buffered inside the stream, force them
   * off the buffer.
   * 
   * @throws Swift_IoException
   */
  public function commit();
  
  /**
   * Attach $is to this stream.
   * The stream acts as an observer, receiving all data that is written.
   * All {@link write()} and {@link flushBuffers()} operations will be mirrored.
   * 
   * @param Swift_InputByteStream $is
   */
  public function bind(Swift_InputByteStream $is);
  
  /**
   * Remove an already bound stream.
   * If $is is not bound, no errors will be raised.
   * If the stream currently has any buffered data it will be written to $is
   * before unbinding occurs.
   * 
   * @param Swift_InputByteStream $is
   */
  public function unbind(Swift_InputByteStream $is);
  
  /**
   * Flush the contents of the stream (empty it) and set the internal pointer
   * to the beginning.
   * @throws Swift_IoException
   */
  public function flushBuffers();
  
}
