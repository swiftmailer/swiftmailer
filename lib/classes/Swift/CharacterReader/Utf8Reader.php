<?php

/*
 Analyzes UTF-8 characters.

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

//@require 'Swift/CharacterReader.php';

/**
 * Analyzes UTF-8 characters.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
class Swift_CharacterReader_Utf8Reader
  implements Swift_CharacterReader
{

  /** Pre-computed for optimization */
  private static $length_map=array(
    1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, //0x0N
    1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, //0x1N
    1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, //0x2N
    1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, //0x3N
    1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, //0x4N
    1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, //0x5N
    1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, //0x6N
    1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, //0x7N
    0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, //0x8N
    0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, //0x9N
    0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, //0xAN
    0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, //0xBN
    2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2, //0xCN
    2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2, //0xDN
    3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3, //0xEN
    4,4,4,4,4,4,4,4,5,5,5,5,6,6,0,0  //0xFN
 );


  /**
   * Returns an integer which specifies how many more bytes to read.
   * A positive integer indicates the number of more bytes to fetch before invoking
   * this method again.
   * A value of zero means this is already a valid character.
   * A value of -1 means this cannot possibly be a valid character.
   * @param string $bytes
   * @return int
   */
  public function validateByteSequence($bytes, $size)
  {
    if ($size<1){
      return -1;
    }
    $needed = self::$length_map[$bytes[0]] - $size;
    return ($needed > -1)
      ? $needed
      : -1
      ;
  }

  /**
   * Returns the number of bytes which should be read to start each character.
   * @return int
   */
  public function getInitialByteSize()
  {
    return 1;
  }

}
