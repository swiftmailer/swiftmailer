<?php

/*
 Generic IoBuffer implementation from Swift Mailer.
 
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

//@require 'Swift/Transport/IoBuffer.php';

/**
 * A generic IoBuffer implementation supporting remote sockets and local processes.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_PolymorphicBuffer implements Swift_Transport_IoBuffer
{
  
  /**
   * Perform any initialization needed, using the given $params.
   * Parameters will vary depending upon the type of IoBuffer used.
   * @param array $params
   */
  public function initialize(array $params)
  {
  }
  
  /**
   * Perform any shutdown logic needed.
   */
  public function terminate()
  {
  }
  
  /**
   * Set an array of string replacements which should be made on data written
   * to the buffer.  This could replace LF with CRLF for example.
   * @param string[] $replacements
   */
  public function setWriteTranslations(array $replacements)
  {
  }
  
  /**
   * Get a line of output (including any CRLF).
   * The $sequence number comes from any writes and may or may not be used
   * depending upon the implementation.
   * @param int $sequence of last write to scan from
   * @return string
   */
  public function readLine($sequence)
  {
  }
  
  /**
   * Reads $length bytes from the stream into a string and moves the pointer
   * through the stream by $length. If less bytes exist than are requested the
   * remaining bytes are given instead. If no bytes are remaining at all, boolean
   * false is returned.
   * @param int $length
   * @return string
   */
  public function read($length)
  {
  }
  
  /**
   * Move the internal read pointer to $byteOffset in the stream.
   * @param int $byteOffset
   * @return boolean
   */
  public function setReadPointer($byteOffset)
  {
  }
  
  /**
   * Writes $bytes to the end of the stream.
   * This method returns the sequence ID of the write (i.e. 1 for first, 2 for
   * second, etc etc).
   * @param string $bytes
   * @return int
   */
  public function write($bytes)
  {
  }
  
  /**
   * Flush the contents of the stream (empty it) and set the internal pointer
   * to the beginning.
   */
  public function flushContents()
  {
  }
  
}
