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
//N=0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,
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
   * Returns the complete charactermap
   *
   * @param string $string
   * @param int $startOffset
   * @param array $currentMap
   * @param mixed $ignoredChars
   */
  public function getCharPositions($string, $startOffset, &$currentMap, &$ignoredChars)
  {
  	if (!isset($currentMap['i']) || !isset($currentMap['p']))
  	{
  	  $currentMap['p'] = $currentMap['i'] = array();
   	}
  	$strlen=strlen($string);
  	$foundChars=count($currentMap['p']);
  	$invalid=false;
  	for ($i=0; $i<$strlen; ++$i)
  	{
  	  $char=ord($string[$i]);
  	  $size=self::$length_map[$char];
  	  if ($size==0)
  	  {
  	    /* char is invalid, we must wait for a resync */
  	  	$invalid=true;
  	  	continue;
   	  }
   	  else
   	  {
   	  	if ($invalid==true)
   	  	{
   	  	  /* We mark the chars as invalid and start a new char */
   	  	  $currentMap['p'][$foundChars]=$startOffset+$i;
   	      $currentMap['i'][$foundChars]=true;
   	      ++$foundChars;
   	      $invalid=false;
   	  	}
   	  	if ($i+$size<$strlen){
   	  		$ignoredChars=substr($string, $i-$strlen);
   	  		break;
   	  	}
   	  	for ($j=1; $j<$size; ++$j)
   	  	{
          $char=$string[$i+$j];
          if ($char>"\x80" && $char<"\xB0")
          {
            // Valid - continue parsing
          }
          else
          {
            /* char is invalid, we must wait for a resync */
            $invalid=true;
            continue 2;
          }
   	  	}
   	  	/* Ok we got a complete char here */
   	  	$currentMap['p'][$foundChars]=$startOffset+$i+$size;
   	    ++$foundChars;
   	  }
  	}
  }
  
  /**
   * Returns mapType
   * @int mapType
   */
  public function getMapType()
  {
  	return self::MAP_TYPE_POSITIONS;
  }
 
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
