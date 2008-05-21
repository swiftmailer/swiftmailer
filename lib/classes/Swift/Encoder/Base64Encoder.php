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

//@require 'Swift/Encoder.php';

/**
 * Handles Base 64 Encoding in Swift Mailer.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
class Swift_Encoder_Base64Encoder implements Swift_Encoder
{
  
  /**
   * Takes an unencoded string and produces a Base64 encoded string from it.
   * Base64 encoded strings have a maximum line length of 76 characters.
   * If the first line needs to be shorter, indicate the difference with
   * $firstLineOffset.
   * @param string $string to encode
   * @param int $firstLineOffset
   * @param int $maxLineLength, optional, 0 indicates the default of 76 bytes
   * @return string
   */
  public function encodeString($string, $firstLineOffset = 0,
    $maxLineLength = 0)
  {
    if (0 >= $maxLineLength || 76 < $maxLineLength)
    {
      $maxLineLength = 76;
    }
    
    $encodedString = base64_encode($string);
    $firstLine = '';
    
    if (0 != $firstLineOffset)
    {
      $firstLine = substr(
        $encodedString, 0, $maxLineLength - $firstLineOffset
        ) . "\r\n";
      $encodedString = substr(
        $encodedString, $maxLineLength - $firstLineOffset
        );
    }
    
    return $firstLine . trim(chunk_split($encodedString, $maxLineLength, "\r\n"));
  }
  
  /**
   * Does nothing.
   */
  public function charsetChanged($charset)
  {
  }
  
}
