<?php

require_once 'Swift/Mime/ContentEncoder/Base64ContentEncoder.php';
require_once 'Swift/OutputByteStream.php';
require_once 'Swift/InputByteStream.php';

class Swift_MockInputByteStream implements Swift_InputByteStream {
  public $content = '';
  public function write($string, Swift_InputByteStream $is = null) {
    $this->content .= $string;
  }
  public function flushContents() {
  }
}

Mock::generate('Swift_OutputByteStream', 'Swift_MockOutputByteStream');

class Swift_Mime_ContentEncoder_Base64ContentEncoderTest extends UnitTestCase
{
  
  private $_encoder;
  
  public function setUp()
  {
    $this->_encoder = new Swift_Mime_ContentEncoder_Base64ContentEncoder();
  }
  
  public function testNameIsBase64()
  {
    $this->assertEqual('base64', $this->_encoder->getName());
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
    
    $os = new Swift_MockOutputByteStream();
    $os->setReturnValueAt(0, 'read', '123');
    $os->setReturnValueAt(1, 'read', false);
    
    $is = new Swift_MockInputByteStream();
    
    $this->_encoder->encodeByteStream($os, $is);
    $this->assertEqual('MTIz', $is->content);
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
      $os = new Swift_MockOutputByteStream();
      $os->setReturnValueAt(0, 'read', pack('C', rand(0, 255)));
      $os->setReturnValueAt(1, 'read', false);
      
      $is = new Swift_MockInputByteStream();
      
      $this->_encoder->encodeByteStream($os, $is);
      $this->assertPattern('~^[a-zA-Z0-9/\+]{2}==$~', $is->content,
        '%s: A single byte should have 2 bytes of padding'
        );
    }
    
    for ($i = 0; $i < 30; ++$i)
    {
      $os = new Swift_MockOutputByteStream();
      $os->setReturnValueAt(
        0, 'read', pack('C*', rand(0, 255), rand(0, 255)));
      $os->setReturnValueAt(1, 'read', false);
      
      $is = new Swift_MockInputByteStream();
      
      $this->_encoder->encodeByteStream($os, $is);
      $this->assertPattern('~^[a-zA-Z0-9/\+]{3}=$~', $is->content,
        '%s: Two bytes should have 1 byte of padding'
        );
    }
    
    for ($i = 0; $i < 30; ++$i)
    {
      $os = new Swift_MockOutputByteStream();
      $os->setReturnValueAt(
        0, 'read', pack('C*', rand(0, 255), rand(0, 255), rand(0, 255)));
      $os->setReturnValueAt(1, 'read', false);
      
      $is = new Swift_MockInputByteStream();
      
      $this->_encoder->encodeByteStream($os, $is);
      $this->assertPattern('~^[a-zA-Z0-9/\+]{4}$~', $is->content,
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
         
    $os = new Swift_MockOutputByteStream();
    $os->setReturnValueAt(0, 'read', 'abcdefghijkl'); //12
    $os->setReturnValueAt(1, 'read', 'mnopqrstuvwx'); //24
    $os->setReturnValueAt(2, 'read', 'yzabc1234567'); //36
    $os->setReturnValueAt(3, 'read', '890ABCDEFGHI'); //48
    $os->setReturnValueAt(4, 'read', 'JKLMNOPQRSTU'); //60
    $os->setReturnValueAt(5, 'read', 'VWXYZ1234567'); //72
    $os->setReturnValueAt(6, 'read', 'abcdefghijkl'); //84
    
    $os->setReturnValueAt(7, 'read', false);
    
    $is = new Swift_MockInputByteStream();
    
    $this->_encoder->encodeByteStream($os, $is);
    $this->assertEqual(
      "YWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXphYmMxMjM0NTY3ODkwQUJDREVGR0hJSktMTU5PUFFS\r\n" .
      "U1RVVldYWVoxMjM0NTY3YWJjZGVmZ2hpamts",
      $is->content
      );
  }
  
  public function testMaximumLineLengthCanBeDifferent()
  {      
    $os = new Swift_MockOutputByteStream();
    $os->setReturnValueAt(0, 'read', 'abcdefghijkl'); //12
    $os->setReturnValueAt(1, 'read', 'mnopqrstuvwx'); //24
    $os->setReturnValueAt(2, 'read', 'yzabc1234567'); //36
    $os->setReturnValueAt(3, 'read', '890ABCDEFGHI'); //48
    $os->setReturnValueAt(4, 'read', 'JKLMNOPQRSTU'); //60
    $os->setReturnValueAt(5, 'read', 'VWXYZ1234567'); //72
    $os->setReturnValueAt(6, 'read', 'abcdefghijkl'); //84
    
    $os->setReturnValueAt(7, 'read', false);
    
    $is = new Swift_MockInputByteStream();
    
    $this->_encoder->encodeByteStream($os, $is, 0, 80);
    $this->assertEqual(
      "YWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXphYmMxMjM0NTY3ODkwQUJDREVGR0hJSktMTU5PUFFSU1RV\r\n" .
      "VldYWVoxMjM0NTY3YWJjZGVmZ2hpamts",
      $is->content
      );
  }
  
  public function testFirstLineLengthCanBeDifferent()
  {
    $os = new Swift_MockOutputByteStream();
    $os->setReturnValueAt(0, 'read', 'abcdefghijkl'); //12
    $os->setReturnValueAt(1, 'read', 'mnopqrstuvwx'); //24
    $os->setReturnValueAt(2, 'read', 'yzabc1234567'); //36
    $os->setReturnValueAt(3, 'read', '890ABCDEFGHI'); //48
    $os->setReturnValueAt(4, 'read', 'JKLMNOPQRSTU'); //60
    $os->setReturnValueAt(5, 'read', 'VWXYZ1234567'); //72
    $os->setReturnValueAt(6, 'read', 'abcdefghijkl'); //84
    
    $os->setReturnValueAt(7, 'read', false);
    
    $is = new Swift_MockInputByteStream();
    
    $this->_encoder->encodeByteStream($os, $is, 19);
    $this->assertEqual(
      "YWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXphYmMxMjM0NTY3ODkwQUJDR\r\n" .
      "EVGR0hJSktMTU5PUFFSU1RVVldYWVoxMjM0NTY3YWJjZGVmZ2hpamts",
      $is->content
      );
  }
  
}
