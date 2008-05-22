<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Mime/ContentEncoder/PlainContentEncoder.php';
require_once 'Swift/InputByteStream.php';
require_once 'Swift/OutputByteStream.php';

class Swift_MockInputByteStream implements Swift_InputByteStream {
  public $content = '';
  public function write($string, Swift_InputByteStream $is = null) {
    $this->content .= $string;
  }
  public function flushContents() {
  }
}

Mock::generate('Swift_OutputByteStream', 'Swift_MockOutputByteStream');

class Swift_Mime_ContentEncoder_PlainContentEncoderTest
  extends Swift_Tests_SwiftUnitTestCase
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
      $os = new Swift_MockOutputByteStream();
      $os->setReturnValueAt(0, 'read', $byte);
      $os->setReturnValueAt(1, 'read', false);
      
      $is = new Swift_MockInputByteStream();
      
      $encoder->encodeByteStream($os, $is);
      $this->assertIdenticalBinary($byte, $is->content);
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
    
    $os = new Swift_MockOutputByteStream();
    $is = new Swift_MockInputByteStream();
    
    $callCount = 0;
    for ($i = 0; $i < 50; $i++)
    {
      $os->setReturnValueAt($i, 'read', 'a ');
      $callCount++;
    }
    
    $os->setReturnValueAt($callCount, 'read', false);
    
    $encoder->encodeByteStream($os, $is, 0, 50);
    $this->assertEqual(
      str_repeat('a ', 25) . "\r\n" . str_repeat('a ', 25),
      $is->content
      );
  }
  
  public function testencodeStringGeneratesCorrectCrlf()
  {
    $encoder = $this->_getEncoder('7bit', true);
    $this->assertEqual("a\r\nb", $encoder->encodeString("a\rb"),
      '%s: Line endings should be standardized'
      );
    $this->assertEqual("a\r\nb", $encoder->encodeString("a\nb"),
      '%s: Line endings should be standardized'
      );
    $this->assertEqual("a\r\n\r\nb", $encoder->encodeString("a\n\rb"),
      '%s: Line endings should be standardized'
      );
    $this->assertEqual("a\r\n\r\nb", $encoder->encodeString("a\r\rb"),
      '%s: Line endings should be standardized'
      );
    $this->assertEqual("a\r\n\r\nb", $encoder->encodeString("a\n\nb"),
      '%s: Line endings should be standardized'
      );
  }
  
  public function testCanonicEncodeByteStreamGeneratesCorrectCrlf_1()
  {
    $encoder = $this->_getEncoder('7bit', true);
    $os = new Swift_MockOutputByteStream();
    $os->setReturnValueAt(0, 'read', 'a');
    $os->setReturnValueAt(1, 'read', "\r");
    $os->setReturnValueAt(2, 'read', 'b');
    $os->setReturnValueAt(3, 'read', false);
    
    $is = new Swift_MockInputByteStream();
    
    $encoder->encodeByteStream($os, $is);
    $this->assertEqual("a\r\nb", $is->content);
  }
  
  public function testCanonicEncodeByteStreamGeneratesCorrectCrlf_2()
  {
    $encoder = $this->_getEncoder('7bit', true);
    $os = new Swift_MockOutputByteStream();
    $os->setReturnValueAt(0, 'read', 'a');
    $os->setReturnValueAt(1, 'read', "\n");
    $os->setReturnValueAt(2, 'read', 'b');
    $os->setReturnValueAt(3, 'read', false);
    
    $is = new Swift_MockInputByteStream();
    
    $encoder->encodeByteStream($os, $is);
    $this->assertEqual("a\r\nb", $is->content);
  }
  
  public function testCanonicEncodeByteStreamGeneratesCorrectCrlf_3()
  {
    $encoder = $this->_getEncoder('7bit', true);
    $os = new Swift_MockOutputByteStream();
    $os->setReturnValueAt(0, 'read', 'a');
    $os->setReturnValueAt(1, 'read', "\n\r");
    $os->setReturnValueAt(2, 'read', 'b');
    $os->setReturnValueAt(3, 'read', false);
    
    $is = new Swift_MockInputByteStream();
    
    $encoder->encodeByteStream($os, $is);
    $this->assertEqual("a\r\n\r\nb", $is->content);
  }
  
  public function testCanonicEncodeByteStreamGeneratesCorrectCrlf_4()
  {
    $encoder = $this->_getEncoder('7bit', true);
    $os = new Swift_MockOutputByteStream();
    $os->setReturnValueAt(0, 'read', 'a');
    $os->setReturnValueAt(1, 'read', "\n\n");
    $os->setReturnValueAt(2, 'read', 'b');
    $os->setReturnValueAt(3, 'read', false);
    
    $is = new Swift_MockInputByteStream();
    
    $encoder->encodeByteStream($os, $is);
    $this->assertEqual("a\r\n\r\nb", $is->content);
  }
  
  public function testCanonicEncodeByteStreamGeneratesCorrectCrlf_5()
  {
    $encoder = $this->_getEncoder('7bit', true);
    $os = new Swift_MockOutputByteStream();
    $os->setReturnValueAt(0, 'read', 'a');
    $os->setReturnValueAt(1, 'read', "\r\r");
    $os->setReturnValueAt(2, 'read', 'b');
    $os->setReturnValueAt(3, 'read', false);
    
    $is = new Swift_MockInputByteStream();
    
    $encoder->encodeByteStream($os, $is);
    $this->assertEqual("a\r\n\r\nb", $is->content);
  }
  
  // -- Private helpers
  
  private function _getEncoder($name, $canonical = false)
  {
    return new Swift_Mime_ContentEncoder_PlainContentEncoder($name, $canonical);
  }
  
}
