<?php

require_once 'Swift/Encoder/Base64Encoder.php';
require_once 'Swift/ByteStream.php';

Mock::generate('Swift_ByteStream', 'Swift_MockByteStream');

class Swift_Encoder_Base64EncoderTest extends UnitTestCase
{
  
  private $_encoder;
  
  public function setUp()
  {
    $this->_encoder = new Swift_Encoder_Base64Encoder();
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
    $os = new Swift_MockByteStream();
    $os->setReturnValueAt(0, 'read', '123');
    $os->setReturnValueAt(1, 'read', false);
    
    $is = new Swift_MockByteStream();
    $is->expectCallCount('write', 1);
    $is->expectAt(0, 'write', array('MTIz'));
    
    $this->_encoder->encodeByteStream($os, $is);
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
      $os = new Swift_MockByteStream();
      $os->setReturnValueAt(0, 'read', pack('C', rand(0, 255)));
      $os->setReturnValueAt(1, 'read', false);
      
      $is = new Swift_MockByteStream();
      $is->expectCallCount('write', 1);
      $is->expectAt(0, 'write', array(new PatternExpectation(
        '~^[a-zA-Z0-9/\+]{2}==$~',
        '%s: A single byte should have 2 bytes of padding'
        )));
      
      $this->_encoder->encodeByteStream($os, $is);
    }
    
    for ($i = 0; $i < 30; ++$i)
    {
      $os = new Swift_MockByteStream();
      $os->setReturnValueAt(
        0, 'read', pack('C*', rand(0, 255), rand(0, 255)));
      $os->setReturnValueAt(1, 'read', false);
      
      $is = new Swift_MockByteStream();
      $is->expectCallCount('write', 1);
      $is->expectAt(0, 'write', array(new PatternExpectation(
        '~^[a-zA-Z0-9/\+]{3}=$~',
        '%s: Two bytes should have 1 byte of padding'
        )));
      
      $this->_encoder->encodeByteStream($os, $is);
    }
    
    for ($i = 0; $i < 30; ++$i)
    {
      $os = new Swift_MockByteStream();
      $os->setReturnValueAt(
        0, 'read', pack('C*', rand(0, 255), rand(0, 255), rand(0, 255)));
      $os->setReturnValueAt(1, 'read', false);
      
      $is = new Swift_MockByteStream();
      $is->expectCallCount('write', 1);
      $is->expectAt(0, 'write', array(new PatternExpectation(
        '~^[a-zA-Z0-9/\+]{4}$~',
        '%s: Three bytes should have no padding'
        )));
      
      $this->_encoder->encodeByteStream($os, $is);
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
    $os = new Swift_MockByteStream();
    $os->setReturnValueAt(0, 'read', 'abcdefghijkl'); //12
    $os->setReturnValueAt(1, 'read', 'mnopqrstuvwx'); //24
    $os->setReturnValueAt(2, 'read', 'yzabc1234567'); //36
    $os->setReturnValueAt(3, 'read', '890ABCDEFGHI'); //48
    $os->setReturnValueAt(4, 'read', 'JKLMNOPQRSTU'); //60
    $os->setReturnValueAt(5, 'read', 'VWXYZ1234567'); //72
    $os->setReturnValueAt(6, 'read', 'abcdefghijkl'); //84
    
    $os->setReturnValueAt(7, 'read', false);
    
    $is = new Swift_MockByteStream();
    $is->expectCallCount('write', 7);
    $is->expectAt(0, 'write', array('YWJjZGVmZ2hpamts'));               //16
    $is->expectAt(1, 'write', array('bW5vcHFyc3R1dnd4'));               //32
    $is->expectAt(2, 'write', array('eXphYmMxMjM0NTY3'));               //48
    $is->expectAt(3, 'write', array('ODkwQUJDREVGR0hJ'));               //64
    $is->expectAt(4, 'write', array('SktMTU5PUFFS' . "\r\n" . 'U1RV')); //76*, 4
    $is->expectAt(5, 'write', array('VldYWVoxMjM0NTY3'));               //20
    $is->expectAt(6, 'write', array('YWJjZGVmZ2hpamts'));               //36
    
    $this->_encoder->encodeByteStream($os, $is);
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
    $os = new Swift_MockByteStream();
    $os->setReturnValueAt(0, 'read', 'abcdefghijkl'); //12
    $os->setReturnValueAt(1, 'read', 'mnopqrstuvwx'); //24
    $os->setReturnValueAt(2, 'read', 'yzabc1234567'); //36
    $os->setReturnValueAt(3, 'read', '890ABCDEFGHI'); //48
    $os->setReturnValueAt(4, 'read', 'JKLMNOPQRSTU'); //60
    $os->setReturnValueAt(5, 'read', 'VWXYZ1234567'); //72
    $os->setReturnValueAt(6, 'read', 'abcdefghijkl'); //84
    
    $os->setReturnValueAt(7, 'read', false);
    
    $is = new Swift_MockByteStream();
    $is->expectCallCount('write', 7);
    $is->expectAt(0, 'write', array('YWJjZGVmZ2hpamts'));               //16
    $is->expectAt(1, 'write', array('bW5vcHFyc3R1dnd4'));               //32
    $is->expectAt(2, 'write', array('eXphYmMxMjM0NTY3'));               //48
    $is->expectAt(3, 'write', array('ODkwQUJDR' . "\r\n" . 'EVGR0hJ')); //57*, 7
    $is->expectAt(4, 'write', array('SktMTU5PUFFSU1RV'));               //23
    $is->expectAt(5, 'write', array('VldYWVoxMjM0NTY3'));               //39
    $is->expectAt(6, 'write', array('YWJjZGVmZ2hpamts'));               //55
    
    $this->_encoder->encodeByteStream($os, $is, 19);
  }
  
}
