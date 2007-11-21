<?php

/*
 Encoder API for Swift Mailer.
 
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
 * Interface for all Encoder schemes.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
interface Swift_Encoder
{
  
  /**
   * Encode a given string to produce an encoded string.
   * @param string $string
   * @param int $firstLineOffset if first line needs to be shorter
   * @return string
   */
  public function encodeString($string, $firstLineOffset = 0);
  
  /**
   * Encode $in to $out.
   * @param Swift_ByteStream $in
   * @param Swift_ByteStream $out
   * @param int $firstLineOffset
   */
  public function encodeByteStream(
    Swift_ByteStream $in, Swift_ByteStream $out, $firstLineOffset = 0);
  
}
