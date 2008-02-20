<?php

require_once 'Swift/ByteStream/ArrayByteStream.php';

class Swift_ByteStream_ArrayByteStreamTest extends UnitTestCase
{

  public function testReadingSingleBytesFromBaseInput()
  {
    $input = array('a', 'b', 'c');
    $bs = new Swift_ByteStream_ArrayByteStream($input);
    $output = array();
    while (false !== $bytes = $bs->read(1))
    {
      $output[] = $bytes;
    }
    $this->assertEqual($input, $output,
      '%s: Bytes read from stream should be the same as bytes in constructor'
      );
  }
  
  public function testReadingMultipleBytesFromBaseInput()
  {
    $input = array('a', 'b', 'c', 'd');
    $bs = new Swift_ByteStream_ArrayByteStream($input);
    $output = array();
    while (false !== $bytes = $bs->read(2))
    {
      $output[] = $bytes;
    }
    $this->assertEqual(array('ab', 'cd'), $output,
      '%s: Bytes read from stream should be in pairs'
      );
  }
  
  public function testReadingOddOffsetOnLastByte()
  {
    $input = array('a', 'b', 'c', 'd', 'e');
    $bs = new Swift_ByteStream_ArrayByteStream($input);
    $output = array();
    while (false !== $bytes = $bs->read(2))
    {
      $output[] = $bytes;
    }
    $this->assertEqual(array('ab', 'cd', 'e'), $output,
      '%s: Bytes read from stream should be in pairs except final read'
      );
  }
  
  public function testSettingPointerPartway()
  {
    $input = array('a', 'b', 'c');
    $bs = new Swift_ByteStream_ArrayByteStream($input);
    $bs->setReadPointer(1);
    $this->assertEqual('b', $bs->read(1),
      '%s: Byte should be second byte since pointer as at offset 1'
      );
  }
  
  public function testResettingPointerAfterExhaustion()
  {
    $input = array('a', 'b', 'c');
    $bs = new Swift_ByteStream_ArrayByteStream($input);
    
    while (false !== $bs->read(1));
    
    $bs->setReadPointer(0);
    $this->assertEqual('a', $bs->read(1),
      '%s: Byte should be first byte since pointer as at offset 0'
      );
  }
  
  public function testPointerNeverSetsBelowZero()
  {
    $input = array('a', 'b', 'c');
    $bs = new Swift_ByteStream_ArrayByteStream($input);
    
    $bs->setReadPointer(-1);
    $this->assertEqual('a', $bs->read(1),
      '%s: Byte should be first byte since pointer should be at offset 0'
      );
  }
  
  public function testPointerNeverSetsAboveStackSize()
  {
    $input = array('a', 'b', 'c');
    $bs = new Swift_ByteStream_ArrayByteStream($input);
    
    $bs->setReadPointer(3);
    $this->assertIdentical(false, $bs->read(1),
      '%s: Stream should be at end and thus return false'
      );
  }
  
  public function testBytesCanBeWrittenToStream()
  {
    $input = array('a', 'b', 'c');
    $bs = new Swift_ByteStream_ArrayByteStream($input);
    
    $bs->write('de');
    
    $output = array();
    while (false !== $bytes = $bs->read(1))
    {
      $output[] = $bytes;
    }
    $this->assertEqual(array('a', 'b', 'c', 'd', 'e'), $output,
      '%s: Bytes read from stream should be from initial stack + written'
      );
  }
  
  public function testContentsCanBeFlushed()
  {
    $input = array('a', 'b', 'c');
    $bs = new Swift_ByteStream_ArrayByteStream($input);
    
    $bs->flushContents();
    
    $this->assertIdentical(false, $bs->read(1),
      '%s: Contents have been flushed so read() should return false'
      );
  }
  
  public function testConstructorCanTakeStringArgument()
  {
    $bs = new Swift_ByteStream_ArrayByteStream('abc');
    $output = array();
    while (false !== $bytes = $bs->read(1))
    {
      $output[] = $bytes;
    }
    $this->assertEqual(array('a', 'b', 'c'), $output,
      '%s: Bytes read from stream should be the same as bytes in constructor'
      );
  }
  
}
