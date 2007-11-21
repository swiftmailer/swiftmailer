<?php

require_once 'Swift/DelegatedExpectation.php';

require_once 'Swift/Encoder/Base64Encoder.php';
require_once 'Swift/ByteStream.php';

Mock::generate('Swift_ByteStream', 'Swift_MockByteStream');

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
  
  public function testStringInputOutputRatioIs3to4Bytes()
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
      'MTIz', $this->_encoder->encodeString('123'),
      '%s: 3 bytes of input should yield 4 bytes of output'
      );
    $this->assertEqual(
      'MTIzNDU2', $this->_encoder->encodeString('123456'),
      '%s: 6 bytes in input should yield 8 bytes of output'
      );
    $this->assertEqual(
      'MTIzNDU2Nzg5', $this->_encoder->encodeString('123456789'),
      '%s: 9 bytes in input should yield 12 bytes of output'
      );
  }
  
  public function testStreamInputOutputRatioIs3To4Bytes()
  {
    $actorStream = new Swift_MockByteStream();
    $actorStream->setReturnValueAt(0, 'read', '123');
    $actorStream->setReturnValueAt(1, 'read', false);
    
    $criticStream = new Swift_MockByteStream();
    $criticStream->expectCallCount('write', 1);
    $criticStream->expectAt(0, 'write', array('MTIz'));
    
    $this->_encoder->encodeByteStream($actorStream, $criticStream);
  }
  
  public function testCharactersInStringOutput()
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
  
  public function testCharactersInStreamOutput()
  {
    $actorStream = new Swift_MockByteStream();
    
    $input = '';
    $length = 0;
    $returnCount = 0;
    
    for ($ordinal = 0; $ordinal < 256; ++$ordinal)
    {
      $input .= pack('C', $ordinal);
      ++$length;
      if (3 == $length)
      {
        $actorStream->setReturnValueAt($returnCount++, 'read', $input);
        $input = '';
        $length = 0;
      }
    }
    
    if (0 != $length)
    {
      $actorStream->setReturnValueAt($returnCount++, 'read', $input);
    }
    
    $actorStream->setReturnValueAt($returnCount++, 'read', false);
    
    $criticStream = new Swift_MockByteStream();
    $criticStream->expectCallCount('write', $returnCount - 1);
    
    for ($i = 0; $i < $returnCount - 1; ++$i)
    {
      $criticStream->expectAt($i, 'write', array(
        new Swift_DelegatedExpectation(array($this, '_outputBytesInRange'),
        '%s: Output bytes must be in A-Z, a-z, 0-9, /, + or ='))
        );
    }
    
    $this->_encoder->encodeByteStream($actorStream, $criticStream);
  }
  
  public function testStringPadLength()
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
  
  public function testStreamPadLength()
  {
    for ($i = 0; $i < 30; ++$i)
    {
      $actorStream = new Swift_MockByteStream();
      $actorStream->setReturnValueAt(0, 'read', pack('C', rand(0, 255)));
      $actorStream->setReturnValueAt(1, 'read', false);
      
      $criticStream = new Swift_MockByteStream();
      $criticStream->expectCallCount('write', 1);
      $criticStream->expectAt(0, 'write', array(
        new Swift_DelegatedExpectation(array($this, '_testDoublePad'),
        '%s: A single byte should have 2 bytes of padding'))
        );
      
      $this->_encoder->encodeByteStream($actorStream, $criticStream);
    }
    
    for ($i = 0; $i < 30; ++$i)
    {
      $actorStream = new Swift_MockByteStream();
      $actorStream->setReturnValueAt(
        0, 'read', pack('C*', rand(0, 255), rand(0, 255)));
      $actorStream->setReturnValueAt(1, 'read', false);
      
      $criticStream = new Swift_MockByteStream();
      $criticStream->expectCallCount('write', 1);
      $criticStream->expectAt(0, 'write', array(
        new Swift_DelegatedExpectation(array($this, '_testSinglePad'),
        '%s: Two bytes should have 1 byte of padding'))
        );
      
      $this->_encoder->encodeByteStream($actorStream, $criticStream);
    }
    
    for ($i = 0; $i < 30; ++$i)
    {
      $actorStream = new Swift_MockByteStream();
      $actorStream->setReturnValueAt(
        0, 'read', pack('C*', rand(0, 255), rand(0, 255), rand(0, 255)));
      $actorStream->setReturnValueAt(1, 'read', false);
      
      $criticStream = new Swift_MockByteStream();
      $criticStream->expectCallCount('write', 1);
      $criticStream->expectAt(0, 'write', array(
        new Swift_DelegatedExpectation(array($this, '_testNoPad'),
        '%s: Three bytes should have no padding'))
        );
      
      $this->_encoder->encodeByteStream($actorStream, $criticStream);
    }
  }
  
  public function testMaximumStringLineLengthIs76Characters()
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
    
    $output =
    'YWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXpBQk' .          //38
    'NERUZHSElKS0xNTk9QUVJTVFVWV1hZWjEyMzQ1' . "\r\n" . //76 *
    'Njc4OTBhYmNkZWZnaGlqa2xtbm9wcXJzdHV2d3' .          //38
    'h5ekFCQ0RFRkdISUpLTE1OT1BRUlNUVVZXWFla' . "\r\n" . //76 *
    'MTIzNDU2Nzg5MEFCQ0RFRkdISUpLTE1OT1BRUl' .          //38
    'NUVVZXWFla';                                       //48
    
    $this->assertEqual(
      $output, $this->_encoder->encodeString($input),
      '%s: Lines should be no more than 76 characters'
      );
  }
  
  public function testMaximumStreamLineLengthIs76Characters()
  {
    //
  }
  
  public function testFirstStringLineLengthCanBeDifferent()
  {
    $input =
    'abcdefghijklmnopqrstuvwxyz' .
    'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
    '1234567890' .
    'abcdefghijklmnopqrstuvwxyz' .
    'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
    '1234567890' .
    'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
    $output =
    'YWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXpBQk' .          //38
    'NERUZHSElKS0xNTk9QU' . "\r\n" .                    //57 *
    'VJTVFVWV1hZWjEyMzQ1Njc4OTBhYmNkZWZnaGl' .          //38
    'qa2xtbm9wcXJzdHV2d3h5ekFCQ0RFRkdISUpLT' . "\r\n" . //76 *
    'E1OT1BRUlNUVVZXWFlaMTIzNDU2Nzg5MEFCQ0R' .          //38
    'FRkdISUpLTE1OT1BRUlNUVVZXWFla';                    //67
    
    $this->assertEqual(
      $output, $this->_encoder->encodeString($input, 19),
      '%s: First line offset is 19 so first line should be 57 chars long'
      );
  }
  
  public function testFirstStreamLineLengthCanBeDifferent()
  {
    //
  }
  
  // -- Delegated expectation checks
  
  //--delegated
  public function _outputBytesInRange($output)
  {
    $outputBytes = unpack('C*', $output);
    
    foreach ($outputBytes as $byte)
    {
      if (!in_array($byte, $this->_allowedOutputBytes))
      {
        return false;
      }
    }
    
    return true;
  }
  
  //--delegated
  public function _testSinglePad($output)
  {
    return preg_match('~^[a-zA-Z0-9/\+]{3}=$~', $output);
  }
  
  //--delegated
  public function _testDoublePad($output)
  {
    return preg_match('~^[a-zA-Z0-9/\+]{2}==$~', $output);
  }
  
  //--delegated
  public function _testNoPad($output)
  {
    return preg_match('~^[a-zA-Z0-9/\+]{4}$~', $output);
  }
  
}
