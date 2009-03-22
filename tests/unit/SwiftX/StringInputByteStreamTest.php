<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'SwiftX/StringInputByteStream.php';

class SwiftX_StringInputByteStreamTest extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testFirstByteValueIsReadFromStringDuringReadNext()
  {
    $in = $this->_createStream('abcdef');
    $this->assertIdentical(0x61, $in->readNext(),
      '%s: Byte value of first character "a" (0x61) should be read'
    );
  }
  
  public function testNextByteIsReadFromStringDuringSubsequentReadNextCalls()
  {
    $in = $this->_createStream('abcdef');
    $in->readNext();
    $this->assertIdentical(0x62, $in->readNext(),
      '%s: Byte value of second character "b" (0x62) should be read'
    );
  }
  
  public function testPositionIsReturnedFromPositionMethod()
  {
    $in = $this->_createStream('abcdef');
    
    $this->assertEqual(0, $in->position(),
      '%s: Position before first read should be 0'
    );
    $in->readNext();
    $this->assertEqual(1, $in->position(),
      '%s: Position before second read should be 1'
    );
    $in->readNext();
    $this->assertEqual(2, $in->position(),
      '%s: Position before third read should be 2'
    );
  }
  
  public function testPositionCanBeSet()
  {
    $in = $this->_createStream('abcdef');
    $in->position(5);
    $this->assertIdentical(0x66, $in->readNext(),
      '%s: Byte value of 6th character "f" (0x66) should be read'
    );
  }
  
  public function testReadNextReturnsMinusOneAtEOF()
  {
    $in = $this->_createStream('a');
    $in->readNext();
    $this->assertIdentical(-1, $in->readNext(),
      '%s: Return value at EOF should be -1'
    );
  }
  
  public function testBytesCanBeReadIntoBufferWithRestrictedLength()
  {
    $in = $this->_createStream('abcdef');
    $in->read($buf, 3);
    $this->assertIdentical(array(0x61, 0x62, 0x63), $buf,
      '%s: Bytes "a" (0x61), "b" (0x62) and "c" (0x63) should be read'
    );
  }
  
  public function testMoreBytesAreReadIntoBufferOnSubsquentCallsToRead()
  {
    $in = $this->_createStream('abcdef');
    $in->read($buf, 3);
    $in->read($buf, 3);
    $this->assertIdentical(range(0x61, 0x66), $buf,
      '%s: Bytes "a" (0x61) through "f" (0x66) should be read'
    );
  }
  
  public function testPositionIsMovedWhenReadingIntoBuffer()
  {
    $in = $this->_createStream('abcdef');
    $in->read($buf, 3);
    $this->assertEqual(3, $in->position(),
      '%s: 3 bytes should be have been read so position should be 3'
    );
  }
  
  public function testNumberOfBytesReadIsReturnedFromRead()
  {
    $in = $this->_createStream('abcdef');
    $this->assertEqual(3, $in->read($buf, 3), '%s: 3 bytes should have been read');
  }
  
  public function testActualNumberOfBytesReadIsReturnedIfLessAvailable()
  {
    $in = $this->_createStream('abcde');
    $this->assertEqual(5, $in->read($buf, 8), '%s: 5 bytes should have been read');
  }
  
  public function testDefaultReadLimitIs8192()
  {
    $startBytes = array();
    for ($i = 0; $i < 8192; ++$i)
    {
      $startBytes[] = rand(0, 255);
    }
    //Add more bytes
    $bytes = $startBytes;
    for ($i = 0; $i < 100; ++$i)
    {
      $bytes[] = rand(0, 255);
    }
    
    $string = call_user_func_array('pack', array_merge((array) 'C*', $bytes));
    
    $in = $this->_createStream($string);
    $in->read($buf);
    $this->assertIdentical(8192, count($buf),
      '%s: First 8192 bytes should be read'
    );
  }
  
  public function testMinusOneIsReturnedFromReadAtEOF()
  {
    $in = $this->_createStream('a');
    $in->read($buf);
    $this->assertEqual(-1, $in->read($buf), '%s: -1 should be returned at EOF');
  }
  
  public function testHasAvailableReturnsTrueIfEOFNotReached()
  {
    $in = $this->_createStream('a');
    $this->assertTrue($in->hasAvailable(),
      '%s: EOF not reached so hasAvailable() should return true'
    );
  }
  
  public function testHasAvailableReturnsFalseIfEOFReached()
  {
    $in = $this->_createStream('a');
    $in->readNext();
    
    $this->assertFalse($in->hasAvailable(),
      '%s: EOF reached so hasAvailable() should return false'
    );
  }
  
  // -- Creation Methods
  
  private function _createStream($string)
  {
    return new SwiftX_StringInputByteStream($string);
  }
  
}
