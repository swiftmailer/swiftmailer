<?php

/*
 Content Transfer Encoder API for Swift Mailer.
 
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
 * Interface for all Transfer Encoding schemes.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
interface Swift_Mime_ContentEncoder extends Swift_Encoder
{
  
  /**
   * Used for encoding text input and ensuring the output is in the canonical
   * form (i.e. all line endings are CRLF).
   * @param string $string
   * @param int $firstLineOffset if the first line needs shortening
   * @param int $maxLineLength
   * @return string
   */
  public function canonicEncodeString($string, $firstLineOffset = 0,
    $maxLineLength = 0);
  
  /**
   * Encode $in to $out, converting all line endings to CRLF.
   * @param Swift_ByteStream $os to read from
   * @param Swift_ByteStream $is to write to
   * @param int $firstLineOffset
   * @param int $maxLineLength - 0 indicates the default length for this encoding
   */
  public function canonicEncodeByteStream(
    Swift_ByteStream $os, Swift_ByteStream $is, $firstLineOffset = 0,
    $maxLineLength = 0);
    
  /**
   * Encode $in to $out.
   * @param Swift_ByteStream $os to read from
   * @param Swift_ByteStream $is to write to
   * @param int $firstLineOffset
   * @param int $maxLineLength - 0 indicates the default length for this encoding
   */
  public function encodeByteStream(
    Swift_ByteStream $os, Swift_ByteStream $is, $firstLineOffset = 0,
    $maxLineLength = 0);
  
  /**
   * Get the MIME name of this content encoding scheme.
   * @return string
   */
  public function getName();
  
}
