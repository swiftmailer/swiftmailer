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
  
  /**
   * Returns an integer which specifies how many more bytes to read.
   * A positive integer indicates the number of more bytes to fetch before invoking
   * this method again.
   * A value of zero means this is already a valid character.
   * A value of -1 means this cannot possibly be a valid character.
   * @param string $bytes
   * @return int
   */
  public function validateByteSequence($bytes)
  {
    $b = $bytes[0];
    
    if ($b >= 0x00 && $b <= 0x7F)
    {
      $expected = 1;
    }
    elseif ($b >= 0xC0 && $b <= 0xDF)
    {
      $expected = 2;
    }
    elseif ($b >= 0xE0 && $b <= 0xEF)
    {
      $expected = 3;
    }
    elseif ($b >= 0xF0 && $b <= 0xF7)
    {
      $expected = 4;
    }
    elseif ($b >= 0xF8 && $b <= 0xFB)
    {
      $expected = 5;
    }
    elseif ($b >= 0xFC && $b <= 0xFD)
    {
      $expected = 6;
    }
    else
    {
      $expected = 0;
    }
    
    return max(-1, $expected - count($bytes));
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
