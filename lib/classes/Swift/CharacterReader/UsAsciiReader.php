<?php

/*
 Analyzes US-ASCII characters.

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
 * Analyzes US-ASCII characters.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
class Swift_CharacterReader_UsAsciiReader
  implements Swift_CharacterReader
{

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
    $byte = reset($bytes);
    if (1 == count($bytes) && $byte >= 0x00 && $byte <= 0x7F)
    {
      return 0;
    }
    else
    {
      return -1;
    }
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
