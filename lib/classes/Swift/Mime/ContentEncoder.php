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

//@require 'Swift/Encoder.php';
//@require 'Swift/InputByteStream.php';
//@require 'Swift/OutputByteStream.php';

/**
 * Interface for all Transfer Encoding schemes.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
interface Swift_Mime_ContentEncoder extends Swift_Encoder
{
  
  /**
   * Encode $in to $out.
   * @param Swift_OutputByteStream $os to read from
   * @param Swift_InputByteStream $is to write to
   * @param int $firstLineOffset
   * @param int $maxLineLength - 0 indicates the default length for this encoding
   */
  public function encodeByteStream(
    Swift_OutputByteStream $os, Swift_InputByteStream $is, $firstLineOffset = 0,
    $maxLineLength = 0);
  
  /**
   * Get the MIME name of this content encoding scheme.
   * @return string
   */
  public function getName();
  
}
