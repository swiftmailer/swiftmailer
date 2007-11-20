<?php

require_once 'Swift/Encoder/Base64Encoder.php';

class Swift_Encoder_Base64EncoderTest extends UnitTestCase
{
  
  private $_encoder;
  
  private $_allowedOutputBytes = array();
  
  public function setUp()
  {
    $this->_encoder = new Swift_Encoder_Base64Encoder();
    
    $this->_allowedOutputBytes = array_merge(
      array(ord('='), ord("\r"), ord("\n"), ord('/'), ord('+')),
      range(ord('A'), ord('Z')), range(ord('a'), ord('z')), range(ord('0'), ord('9'))
      );
  }
  
  /*
  There's really no point in testing the entire base64 encoding to the
  level QP encoding has been tested.  base64_encode() has been in PHP for
  years.
  */
  
  public function testInputOutputRatioIs3to4Bytes()
  {
    /*
    RFC 2045, 6.8
    
         The encoding process represents 24-bit groups of input bits as output
         strings of 4 encoded characters.  Proceeding from left to right, a
         24-bit input group is formed by concatenating 3 8bit input groups.
         These 24 bits are then treated as 4 concatenated 6-bit groups, each
         of which is translated into a single digit in the base64 alphabet.
         */
    
    $this->assertEqual(
      4, strlen($this->_encoder->encodeString('123')),
      '%s: 3 bytes of input should yield 4 bytes of output'
      );
    $this->assertEqual(
      8, strlen($this->_encoder->encodeString('123456')),
      '%s: 6 bytes in input should yield 8 bytes of output'
      );
    $this->assertEqual(
      12, strlen($this->_encoder->encodeString('123456789')),
      '%s: 9 bytes in input should yield 12 bytes of output'
      );
  }
  
  public function testCharactersInOutput()
  {
    /*
    RFC 2045, 6.8
    
      Each 6-bit group is used as an index into an array of 64 printable
         characters.  The character referenced by the index is placed in the
         output string.  These characters, identified in Table 1, below, are
         selected so as to be universally representable, and the set excludes
         characters with particular significance to SMTP (e.g., ".", CR, LF)
         and to the multipart boundary delimiters defined in RFC 2046 (e.g.,
         "-").
    
    Value Encoding  Value Encoding  Value Encoding  Value Encoding
        0 A            17 R            34 i            51 z
        1 B            18 S            35 j            52 0
        2 C            19 T            36 k            53 1
        3 D            20 U            37 l            54 2
        4 E            21 V            38 m            55 3
        5 F            22 W            39 n            56 4
        6 G            23 X            40 o            57 5
        7 H            24 Y            41 p            58 6
        8 I            25 Z            42 q            59 7
        9 J            26 a            43 r            60 8
       10 K            27 b            44 s            61 9
       11 L            28 c            45 t            62 +
       12 M            29 d            46 u            63 /
       13 N            30 e            47 v
       14 O            31 f            48 w         (pad) =
       15 P            32 g            49 x
       16 Q            33 h            50 y
       */
    
    $input = '';
    for ($ordinal = 0; $ordinal < 256; ++$ordinal)
    {
      $input .= pack('C', $ordinal);
    }
    
    $output = $this->_encoder->encodeString($input);
    
    $outputBytes = unpack('C*', $output);
    
    foreach ($outputBytes as $byte)
    {
      $this->assertTrue(
        in_array($byte, $this->_allowedOutputBytes),
        '%s: Output bytes must be in A-Z, a-z, 0-9, /, + or ='
        );
    }
  }
  
  public function testPadLength()
  {
    /*
    RFC 2045, 6.8
    
       Special processing is performed if fewer than 24 bits are available
       at the end of the data being encoded.  A full encoding quantum is
       always completed at the end of a body.  When fewer than 24 input bits
       are available in an input group, zero bits are added (on the right)
       to form an integral number of 6-bit groups.  Padding at the end of
       the data is performed using the "=" character.  Since all base64
       input is an integral number of octets, only the following cases can
       arise: (1) the final quantum of encoding input is an integral
       multiple of 24 bits; here, the final unit of encoded output will be
       an integral multiple of 4 characters with no "=" padding, (2) the
       final quantum of encoding input is exactly 8 bits; here, the final
       unit of encoded output will be two characters followed by two "="
       padding characters, or (3) the final quantum of encoding input is
       exactly 16 bits; here, the final unit of encoded output will be three
       characters followed by one "=" padding character.
       */
    
    for ($i = 0; $i < 30; ++$i)
    {
      $input = pack('C', rand(0, 255));   
      $this->assertPattern(
        '~^[a-zA-Z0-9/\+]{2}==$~', $this->_encoder->encodeString($input),
        '%s: A single byte should have 2 bytes of padding'
        );
    }
    
    for ($i = 0; $i < 30; ++$i)
    {
      $input = pack('C*', rand(0, 255), rand(0, 255));
      $this->assertPattern(
        '~^[a-zA-Z0-9/\+]{3}=$~', $this->_encoder->encodeString($input),
        '%s: Two bytes should have 1 byte of padding'
        );
    }
    
    for ($i = 0; $i < 30; ++$i)
    {
      $input  = pack('C*', rand(0, 255), rand(0, 255), rand(0, 255));
      $this->assertPattern(
        '~^[a-zA-Z0-9/\+]{4}$~', $this->_encoder->encodeString($input),
        '%s: Three bytes should have no padding'
        );
    }
  }
  
  public function testMaximumLineLengthIs76Characters()
  {
    /*
         The encoded output stream must be represented in lines of no more
         than 76 characters each.  All line breaks or other characters not
         found in Table 1 must be ignored by decoding software.
         */
         
    $input =
    'abcdefghijklmnopqrstuvwxyz' .
    'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
    '1234567890' .
    'abcdefghijklmnopqrstuvwxyz' .
    'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
    '1234567890' .
    'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
    $output = $this->_encoder->encodeString($input);
    
    $lines = explode("\r\n", $output);
    
    foreach ($lines as $line)
    {
      $this->assertTrue(
        76 >= strlen($line),
        '%s: Lines should be no more than 76 characters'
      );
    }
  }
  
}
