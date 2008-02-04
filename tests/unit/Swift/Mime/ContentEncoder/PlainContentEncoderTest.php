<?php

require_once 'Swift/AbstractSwiftUnitTestCase.php';
require_once 'Swift/Mime/ContentEncoder/PlainContentEncoder.php';
require_once 'Swift/ByteStream.php';

Mock::generate('Swift_ByteStream', 'Swift_MockByteStream');

class Swift_Mime_ContentEncoder_PlainContentEncoderTest
  extends Swift_AbstractSwiftUnitTestCase
{
  
  public function testNameCanBeSpecifiedInConstructor()
  {
    $encoder = $this->_getEncoder('7bit');
    $this->assertEqual('7bit', $encoder->getName());
    
    $encoder = $this->_getEncoder('8bit');
    $this->assertEqual('8bit', $encoder->getName());
  }
  
  public function testNoOctetsAreModifiedInString()
  {
    $encoder = $this->_getEncoder('7bit');
    foreach (range(0x00, 0xFF) as $octet)
    {
      $byte = pack('C', $octet);
      $this->assertIdenticalBinary($byte, $encoder->encodeString($byte));
    }
  }
  
  public function testNoOctetsAreModifiedInByteStream()
  {
    $encoder = $this->_getEncoder('7bit');
    foreach (range(0x00, 0xFF) as $octet)
    {
      $byte = pack('C', $octet);
      $os = new Swift_MockByteStream();
      $os->setReturnValueAt(0, 'read', $byte);
      $os->setReturnValueAt(1, 'read', false);
      
      $is = new Swift_MockByteStream();
      $is->expectOnce('write', array(new Swift_IdenticalBinaryExpectation($byte)));
      
      $encoder->encodeByteStream($os, $is);
    }
  }
  
  public function testLineLengthCanBeSpecified()
  {
    $encoder = $this->_getEncoder('7bit');
    
    $chars = array();
    for ($i = 0; $i < 50; $i++)
    {
      $chars[] = 'a';
    }
    $input = implode(' ', $chars); //99 chars long
    
    $this->assertEqual(
      'a a a a a a a a a a a a a a a a a a a a a a a a a ' . "\r\n" . //50 *
      'a a a a a a a a a a a a a a a a a a a a a a a a a',            //99
      $encoder->encodeString($input, 0, 50),
      '%s: Lines should be wrapped at 50 chars'
      );
  }
  
  public function testLineLengthCanBeSpecifiedInByteStream()
  {
    $encoder = $this->_getEncoder('7bit');
    
    $os = new Swift_MockByteStream();
    $is = new Swift_MockByteStream();
    
    $callCount = 0;
    for ($i = 0; $i < 50; $i++)
    {
      $os->setReturnValueAt($i, 'read', 'a ');
      if ($i > 0 && 0 == ($i % 25))
      {
        $is->expectAt($i, 'write', array("\r\n" . 'a '));
      }
      else
      {
        $is->expectAt($i, 'write', array('a '));
      }
      $callCount++;
    }
    $os->setReturnValueAt($callCount, 'read', false);
    $os->expectCallCount('read', $callCount + 1);
    $is->expectCallCount('write', $callCount);
    
    $encoder->encodeByteStream($os, $is, 0, 50);
  }
  
  // -- Private helpers
  
  private function _getEncoder($name)
  {
    return new Swift_Mime_ContentEncoder_PlainContentEncoder($name);
  }
  
}
