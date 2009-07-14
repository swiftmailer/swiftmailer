<?php

/*
 The Base 64 transfer encoder in Swift Mailer.
 
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

//@require 'Swift/Mime/ContentEncoder.php';
//@require 'Swift/Encoder/Base64Encoder.php';
//@require 'Swift/InputByteStream.php';
//@require 'Swift/OutputByteStream.php';

/**
 * Handles Base 64 Transfer Encoding in Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_ContentEncoder_Base64ContentEncoder
  extends Swift_Encoder_Base64Encoder
  implements Swift_Mime_ContentEncoder
{
  
  /**
   * Encode stream $in to stream $out.
   * @param Swift_OutputByteStream $in
   * @param Swift_InputByteStream $out
   * @param int $firstLineOffset
   * @param int $maxLineLength, optional, 0 indicates the default of 76 bytes
   */
  public function encodeByteStream(
    Swift_OutputByteStream $os, Swift_InputByteStream $is, $firstLineOffset = 0,
    $maxLineLength = 0)
  {
    if (0 >= $maxLineLength || 76 < $maxLineLength)
    {
      $maxLineLength = 76;
    }
    
    $remainder = 0;
    
    while (false !== $bytes = $os->read(8190))
    {
      $encoded = base64_encode($bytes);
      $encodedTransformed = '';
      $thisMaxLineLength = $maxLineLength - $remainder - $firstLineOffset;
      
      while ($thisMaxLineLength < strlen($encoded))
      {
        $encodedTransformed .= substr($encoded, 0, $thisMaxLineLength) . "\r\n";
        $firstLineOffset = 0;
        $encoded = substr($encoded, $thisMaxLineLength);
        $thisMaxLineLength = $maxLineLength;
        $remainder = 0;
      }
      
      if (0 < $remainingLength = strlen($encoded))
      {
        $remainder += $remainingLength;
        $encodedTransformed .= $encoded;
        $encoded = null;
      }
      
      $is->write($encodedTransformed);
    }
  }
  
  /**
   * Get the name of this encoding scheme.
   * Returns the string 'base64'.
   * @return string
   */
  public function getName()
  {
    return 'base64';
  }
  
}
