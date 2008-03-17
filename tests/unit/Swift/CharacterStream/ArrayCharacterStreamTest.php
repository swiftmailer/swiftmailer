<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/CharacterStream/ArrayCharacterStream.php';
require_once 'Swift/CharacterReaderFactory.php';
require_once 'Swift/CharacterReader.php';
require_once 'Swift/OutputByteStream.php';

class Swift_CharacterStream_ArrayCharacterStreamTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testValidatorAlgorithmOnImportString()
  {
    $context = new Mockery();
    
    $reader = $context->mock('Swift_CharacterReader');
    $factory = $context->mock('Swift_CharacterReaderFactory');
    
    $context->checking(Expectations::create()
      -> allowing($factory)->getReaderFor('utf-8') -> will(returnValue($reader))
      );
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $seq = $context->sequence('read-sequence');
    $context->checking(Expectations::create()
      -> ignoring($reader)->getInitialByteSize() -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD1)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      );
    
    $stream->importString(pack('C*',
      0xD0, 0x94,
      0xD0, 0xB6,
      0xD0, 0xBE,
      0xD1, 0x8D,
      0xD0, 0xBB,
      0xD0, 0xB0
      )
    );
    
    $context->assertIsSatisfied();
  }
  
  public function testCharactersWrittenUseValidator()
  {
    $context = new Mockery();
    
    $reader = $context->mock('Swift_CharacterReader');
    $factory = $context->mock('Swift_CharacterReaderFactory');
    
    $context->checking(Expectations::create()
      -> allowing($factory)->getReaderFor('utf-8') -> will(returnValue($reader))
      );
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $seq = $context->sequence('read-sequence');
    $context->checking(Expectations::create()
      -> ignoring($reader)->getInitialByteSize() -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD1)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD1)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD1)) -> inSequence($seq) -> will(returnValue(1))
      );
    
    $stream->importString(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE));
    
    $stream->write(pack('C*',
      0xD0, 0xBB,
      0xD1, 0x8E,
      0xD0, 0xB1,
      0xD1, 0x8B,
      0xD1, 0x85
      )
    );
    
    $context->assertIsSatisfied();
  }
  
  public function testReadCharactersAreInTact()
  {
    $context = new Mockery();
    
    $reader = $context->mock('Swift_CharacterReader');
    $factory = $context->mock('Swift_CharacterReaderFactory');
    
    $context->checking(Expectations::create()
      -> allowing($factory)->getReaderFor('utf-8') -> will(returnValue($reader))
      );
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $seq = $context->sequence('read-sequence');
    $context->checking(Expectations::create()
      -> ignoring($reader)->getInitialByteSize() -> will(returnValue(1))
      //String
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      //Stream
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD1)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD1)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD1)) -> inSequence($seq) -> will(returnValue(1))
      );
    
    $stream->importString(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE));
    
    $stream->write(pack('C*',
      0xD0, 0xBB,
      0xD1, 0x8E,
      0xD0, 0xB1,
      0xD1, 0x8B,
      0xD1, 0x85
      )
    );
    
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0x94), $stream->read(1));
    $this->assertIdenticalBinary(
      pack('C*', 0xD0, 0xB6, 0xD0, 0xBE), $stream->read(2)
      );
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0xBB), $stream->read(1));
    $this->assertIdenticalBinary(
      pack('C*', 0xD1, 0x8E, 0xD0, 0xB1, 0xD1, 0x8B), $stream->read(3)
      );
    $this->assertIdenticalBinary(pack('C*', 0xD1, 0x85), $stream->read(1));
    
    $this->assertIdentical(false, $stream->read(1));
    
    $context->assertIsSatisfied();
  }
  
  public function testCharactersCanBeReadAsByteArrays()
  {
    $context = new Mockery();
    
    $reader = $context->mock('Swift_CharacterReader');
    $factory = $context->mock('Swift_CharacterReaderFactory');
    
    $context->checking(Expectations::create()
      -> allowing($factory)->getReaderFor('utf-8') -> will(returnValue($reader))
      );
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $seq = $context->sequence('read-sequence');
    $context->checking(Expectations::create()
      -> ignoring($reader)->getInitialByteSize() -> will(returnValue(1))
      //String
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      //Stream
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD1)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD1)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD1)) -> inSequence($seq) -> will(returnValue(1))
      );
    
    $stream->importString(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE));
    
    $stream->write(pack('C*',
      0xD0, 0xBB,
      0xD1, 0x8E,
      0xD0, 0xB1,
      0xD1, 0x8B,
      0xD1, 0x85
      )
    );
    
    $this->assertEqual(array(0xD0, 0x94), $stream->readBytes(1));
    $this->assertEqual(array(0xD0, 0xB6, 0xD0, 0xBE), $stream->readBytes(2));
    $this->assertEqual(array(0xD0, 0xBB), $stream->readBytes(1));
    $this->assertEqual(
      array(0xD1, 0x8E, 0xD0, 0xB1, 0xD1, 0x8B), $stream->readBytes(3)
      );
    $this->assertEqual(array(0xD1, 0x85), $stream->readBytes(1));
    
    $this->assertIdentical(false, $stream->readBytes(1));
    
    $context->assertIsSatisfied();
  }
  
  public function testRequestingLargeCharCountPastEndOfStream()
  {
    $context = new Mockery();
    
    $reader = $context->mock('Swift_CharacterReader');
    $factory = $context->mock('Swift_CharacterReaderFactory');
    
    $context->checking(Expectations::create()
      -> allowing($factory)->getReaderFor('utf-8') -> will(returnValue($reader))
      );
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $seq = $context->sequence('read-sequence');
    $context->checking(Expectations::create()
      -> ignoring($reader)->getInitialByteSize() -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      );
    
    $stream->importString(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE));
    
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE),
      $stream->read(100)
      );
    
    $this->assertIdentical(false, $stream->read(1));
    
    $context->assertIsSatisfied();
  }
  
  public function testRequestingByteArrayCountPastEndOfStream()
  {
    $context = new Mockery();
    
    $reader = $context->mock('Swift_CharacterReader');
    $factory = $context->mock('Swift_CharacterReaderFactory');
    
    $context->checking(Expectations::create()
      -> allowing($factory)->getReaderFor('utf-8') -> will(returnValue($reader))
      );
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $seq = $context->sequence('read-sequence');
    $context->checking(Expectations::create()
      -> ignoring($reader)->getInitialByteSize() -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      );
    
    $stream->importString(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE));
    
    $this->assertEqual(array(0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE),
      $stream->readBytes(100)
      );
    
    $this->assertIdentical(false, $stream->readBytes(1));
    
    $context->assertIsSatisfied();
  }
  
  public function testPointerOffsetCanBeSet()
  {
    $context = new Mockery();
    
    $reader = $context->mock('Swift_CharacterReader');
    $factory = $context->mock('Swift_CharacterReaderFactory');
    
    $context->checking(Expectations::create()
      -> allowing($factory)->getReaderFor('utf-8') -> will(returnValue($reader))
      );
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $seq = $context->sequence('read-sequence');
    $context->checking(Expectations::create()
      -> ignoring($reader)->getInitialByteSize() -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      );
    
    $stream->importString(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE));
    
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0x94), $stream->read(1));
    
    $stream->setPointer(0);
    
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0x94), $stream->read(1));
    
    $stream->setPointer(2);
    
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0xBE), $stream->read(1));
    
    $context->assertIsSatisfied();
  }
  
  public function testContentsCanBeFlushed()
  {
    $context = new Mockery();
    
    $reader = $context->mock('Swift_CharacterReader');
    $factory = $context->mock('Swift_CharacterReaderFactory');
    
    $context->checking(Expectations::create()
      -> allowing($factory)->getReaderFor('utf-8') -> will(returnValue($reader))
      );
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $seq = $context->sequence('read-sequence');
    $context->checking(Expectations::create()
      -> ignoring($reader)->getInitialByteSize() -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      );
    
    $stream->importString(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE));
    
    $stream->flushContents();
    
    $this->assertIdentical(false, $stream->read(1));
    
    $context->assertIsSatisfied();
  }
  
  public function testByteStreamCanBeImportingUsesValidator()
  { 
    $context = new Mockery();
    
    $reader = $context->mock('Swift_CharacterReader');
    $factory = $context->mock('Swift_CharacterReaderFactory');
    
    $context->checking(Expectations::create()
      -> allowing($factory)->getReaderFor('utf-8') -> will(returnValue($reader))
      );
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $os = $context->mock('Swift_OutputByteStream');
    
    $seq = $context->sequence('read-stream');
    $context->checking(Expectations::create()
      -> one($os)->read(any()) -> inSequence($seq) -> will(returnValue(pack('C*', 0xD0)))
      -> one($os)->read(any()) -> inSequence($seq) -> will(returnValue(pack('C*', 0x94)))
      -> one($os)->read(any()) -> inSequence($seq) -> will(returnValue(pack('C*', 0xD0)))
      -> one($os)->read(any()) -> inSequence($seq) -> will(returnValue(pack('C*', 0xB6)))
      -> one($os)->read(any()) -> inSequence($seq) -> will(returnValue(pack('C*', 0xD0)))
      -> one($os)->read(any()) -> inSequence($seq) -> will(returnValue(pack('C*', 0xBE)))
      -> ignoring($os)->read(any()) -> will(returnValue(false))
      );
      
    $seq = $context->sequence('read-chars');
    $context->checking(Expectations::create()
      -> ignoring($reader)->getInitialByteSize() -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      );
    
    $stream->importByteStream($os);
    
    $context->assertIsSatisfied();
  }
  
  public function testImportingStreamProducesCorrectCharArray()
  {
    $context = new Mockery();
    
    $reader = $context->mock('Swift_CharacterReader');
    $factory = $context->mock('Swift_CharacterReaderFactory');
    
    $context->checking(Expectations::create()
      -> allowing($factory)->getReaderFor('utf-8') -> will(returnValue($reader))
      );
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $os = $context->mock('Swift_OutputByteStream');
    
    $seq = $context->sequence('read-stream');
    $context->checking(Expectations::create()
      -> one($os)->read(any()) -> inSequence($seq) -> will(returnValue(pack('C*', 0xD0)))
      -> one($os)->read(any()) -> inSequence($seq) -> will(returnValue(pack('C*', 0x94)))
      -> one($os)->read(any()) -> inSequence($seq) -> will(returnValue(pack('C*', 0xD0)))
      -> one($os)->read(any()) -> inSequence($seq) -> will(returnValue(pack('C*', 0xB6)))
      -> one($os)->read(any()) -> inSequence($seq) -> will(returnValue(pack('C*', 0xD0)))
      -> one($os)->read(any()) -> inSequence($seq) -> will(returnValue(pack('C*', 0xBE)))
      -> ignoring($os)->read(any()) -> will(returnValue(false))
      );
    
    $seq = $context->sequence('read-chars');
    $context->checking(Expectations::create()
      -> ignoring($reader)->getInitialByteSize() -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      -> one($reader)->validateByteSequence(array(0xD0)) -> inSequence($seq) -> will(returnValue(1))
      );
    
    $stream->importByteStream($os);
    
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0x94), $stream->read(1));
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0xB6), $stream->read(1));
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0xBE), $stream->read(1));
    
    $this->assertIdentical(false, $stream->read(1));
    
    $context->assertIsSatisfied();
  }
  
  public function testAlgorithmWithFixedWidthCharsets()
  {
    $context = new Mockery();
    
    $reader = $context->mock('Swift_CharacterReader');
    $factory = $context->mock('Swift_CharacterReaderFactory');
    
    $context->checking(Expectations::create()
      -> allowing($factory)->getReaderFor('utf-8') -> will(returnValue($reader))
      );
    
    $seq = $context->sequence('read-chars');
    $context->checking(Expectations::create()
      -> ignoring($reader)->getInitialByteSize() -> will(returnValue(2))
      -> one($reader)->validateByteSequence(array(0xD1, 0x8D)) -> inSequence($seq)
      -> one($reader)->validateByteSequence(array(0xD0, 0xBB)) -> inSequence($seq)
      -> one($reader)->validateByteSequence(array(0xD0, 0xB0)) -> inSequence($seq)
      );
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream(
      $factory, 'utf-8'
    );
    $stream->importString(pack('C*', 0xD1, 0x8D, 0xD0, 0xBB, 0xD0, 0xB0));
      
    $this->assertIdenticalBinary(pack('C*', 0xD1, 0x8D), $stream->read(1));
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0xBB), $stream->read(1));
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0xB0), $stream->read(1));
    
    $this->assertIdentical(false, $stream->read(1));
    
    $context->assertIsSatisfied();
  }
  
}
