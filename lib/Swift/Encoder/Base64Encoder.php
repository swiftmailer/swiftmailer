<?php

/*
 The Base 64 encoder in Swift Mailer.
 
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


require_once dirname(__FILE__) . '/../Encoder.php';
require_once dirname(__FILE__) . '/../ByteStream.php';

/**
 * Handles Base 64 Encoding in Swift Mailer.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
class Swift_Encoder_Base64Encoder implements Swift_Encoder
{
  
  /**
   * Takes an unencoded string and produces a Base 64 encoded string from it.
   * Base64 encoded strings have a maximum line length of 76 characters.
   * If the first line needs to be shorter, indicate the difference with
   * $firstLineOffset.
   *
   * @param string $string to encode
   * @param int $firstLineOffset
   * @return string
   */
  public function encodeString($string, $firstLineOffset = 0)
  {
    $encodedString = base64_encode($string);
    $firstLine = '';
    
    if (0 != $firstLineOffset)
    {
      $firstLine = substr($encodedString, 0, 76 - $firstLineOffset) . "\r\n";
      $encodedString = substr($encodedString, 76 - $firstLineOffset);
    }
    
    return $firstLine . trim(chunk_split($encodedString, 76, "\r\n"));
  }
  
  /**
   * Encode stream $in to stream $out.
   * @param Swift_ByteStream $in
   * @param Swift_ByteStream $out
   * @param int $firstLineOffset
   */
  public function encodeByteStream(
    Swift_ByteStream $in, Swift_ByteStream $out, $firstLineOffset = 0)
  {
    while (false !== $bytes = $in->read(8190))
    {
      $out->write(base64_encode($bytes));
    }
  }
  
}
