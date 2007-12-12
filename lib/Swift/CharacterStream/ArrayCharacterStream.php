<?php

/*
 CharacterStream implementation using an array in Swift Mailer.
 
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

require_once dirname(__FILE__) . '/../CharacterStream.php';
require_once dirname(__FILE__) . '/../ByteStream.php';


/**
 * A CharacterStream implementation which stores characters in an internal array.
 * @package Swift
 * @subpackage CharacterStream
 * @author Chris Corbyn
 */
class Swift_CharacterStream_ArrayCharacterStream
  implements Swift_CharacterStream
{

  /**
   * Set the character set used in this CharacterStream.
   * @param string $charset
   */
  public function setCharacterSet($charset)
  {
  }
  
  /**
   * Set the CharacterSetValidatorFactory for multi charset support.
   * @param Swift_CharacterSetValidatorFactory $factory
   */
  public function setCharacterSetValidatorFactory(
    Swift_CharacterSetValidatorFactory $factory)
  {
  }
  
  /**
   * Overwrite this character stream using the byte sequence in the byte stream.
   * @param Swift_ByteStream $os output stream to read from
   */
  public function importByteStream(Swift_ByteStream $os)
  {
  }
  
  /**
   * Import a string a bytes into this CharacterStream, overwriting any existing
   * data in the stream.
   * @param string $string
   */
  public function importString($string)
  {
  }
  
  /**
   * Read $length characters from the stream and move the internal pointer
   * $length further into the stream.
   * @param int $length
   * @return string[]
   */
  public function read($length)
  {
  }
  
  /**
   * Write $chars to the end of the stream.
   * @param string $chars
   */
  public function write($chars)
  {
  }
  
  /**
   * Move the internal pointer to $charOffset in the stream.
   * @param int $charOffset
   */
  public function setPointer($charOffset)
  {
  }
  
  /**
   * Empty the stream and reset the internal pointer.
   */
  public function flushContents()
  {
  }
  
}
