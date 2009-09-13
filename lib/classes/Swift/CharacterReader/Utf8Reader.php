<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
