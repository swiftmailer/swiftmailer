<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/CharacterStream/ArrayCharacterStream.php';
require_once 'Swift/CharacterReaderFactory.php';
require_once 'Swift/CharacterReader.php';
require_once 'Swift/OutputByteStream.php';

Mock::generate(
  'Swift_CharacterReader', 'Swift_MockCharacterReader'
  );
Mock::generate(
  'Swift_CharacterReaderFactory', 'Swift_MockCharacterReaderFactory'
  );
Mock::generate('Swift_OutputByteStream', 'Swift_MockOutputByteStream');

class Swift_CharacterStream_ArrayCharacterStreamTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testValidatorAlgorithmOnImportString()
  {
    $reader = new Swift_MockCharacterReader();
    
    $factory = new Swift_MockCharacterReaderFactory();
    $factory->setReturnValue('getReaderFor', $reader);
    $factory->expectOnce('getReaderFor', array('utf-8'));
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $reader->setReturnValue('getInitialByteSize', 1);
    
    $reader->expectAt(0, 'validateByteSequence', array(array(0xD0)));
    $reader->setReturnValue('validateByteSequence', 1, array(array(0xD0)));
    
    $reader->expectAt(1, 'validateByteSequence', array(array(0xD0)));
    $reader->setReturnValue('validateByteSequence', 1, array(array(0xD0)));
    
    $reader->expectAt(2, 'validateByteSequence', array(array(0xD0)));
    $reader->setReturnValue('validateByteSequence', 1, array(array(0xD0)));
    
    $reader->expectAt(3, 'validateByteSequence', array(array(0xD1)));
    $reader->setReturnValue('validateByteSequence', 1, array(array(0xD1)));
    
    $reader->expectAt(4, 'validateByteSequence', array(array(0xD0)));
    $reader->setReturnValue('validateByteSequence', 1, array(array(0xD0)));
    
    $reader->expectAt(5, 'validateByteSequence', array(array(0xD0)));
    $reader->setReturnValue('validateByteSequence', 1, array(array(0xD0)));
    
    $reader->expectCallCount('validateByteSequence', 6);
    
    $stream->importString(pack('C*',
      0xD0, 0x94,
      0xD0, 0xB6,
      0xD0, 0xBE,
      0xD1, 0x8D,
      0xD0, 0xBB,
      0xD0, 0xB0
      )
    );
  }
  
  public function testCharactersWrittenUseValidator()
  {
    $reader = new Swift_MockCharacterReader();
    
    $factory = new Swift_MockCharacterReaderFactory();
    $factory->setReturnValue('getReaderFor', $reader);
    $factory->expectOnce('getReaderFor', array('utf-8'));
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $reader->setReturnValue('getInitialByteSize', 1);
    
    $reader->expectAt(0, 'validateByteSequence', array(array(0xD0)));
    $reader->setReturnValue('validateByteSequence', 1, array(array(0xD0)));
    
    $reader->expectAt(1, 'validateByteSequence', array(array(0xD0)));
    $reader->setReturnValue('validateByteSequence', 1, array(array(0xD0)));
    
    $reader->expectAt(2, 'validateByteSequence', array(array(0xD0)));
    $reader->setReturnValue('validateByteSequence', 1, array(array(0xD0)));
    
    $reader->expectAt(3, 'validateByteSequence', array(array(0xD0)));
    $reader->setReturnValue('validateByteSequence', 1, array(array(0xD0)));
    
    $reader->expectAt(4, 'validateByteSequence', array(array(0xD1)));
    $reader->setReturnValue('validateByteSequence', 1, array(array(0xD1)));
    
    $reader->expectAt(5, 'validateByteSequence', array(array(0xD0)));
    $reader->setReturnValue('validateByteSequence', 1, array(array(0xD0)));
    
    $reader->expectAt(6, 'validateByteSequence', array(array(0xD1)));
    $reader->setReturnValue('validateByteSequence', 1, array(array(0xD1)));
    
    $reader->expectAt(7, 'validateByteSequence', array(array(0xD1)));
    $reader->setReturnValue('validateByteSequence', 1, array(array(0xD1)));
    
    $reader->expectCallCount('validateByteSequence', 8);
    
    $stream->importString(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE));
    
    $stream->write(pack('C*',
      0xD0, 0xBB,
      0xD1, 0x8E,
      0xD0, 0xB1,
      0xD1, 0x8B,
      0xD1, 0x85
      )
    );
  }
  
  public function testReadCharactersAreInTact()
  {
    $reader = new Swift_MockCharacterReader();
    
    $factory = new Swift_MockCharacterReaderFactory();
    $factory->setReturnValue('getReaderFor', $reader);
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $reader->setReturnValue('getInitialByteSize', 1);
    $reader->setReturnValueAt(0, 'validateByteSequence', 1);
    $reader->setReturnValueAt(1, 'validateByteSequence', 1);
    $reader->setReturnValueAt(2, 'validateByteSequence', 1);
    $reader->setReturnValueAt(3, 'validateByteSequence', 1);
    $reader->setReturnValueAt(4, 'validateByteSequence', 1);
    $reader->setReturnValueAt(5, 'validateByteSequence', 1);
    $reader->setReturnValueAt(6, 'validateByteSequence', 1);
    $reader->setReturnValueAt(7, 'validateByteSequence', 1);
    
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
  }
  
  public function testCharactersCanBeReadAsByteArrays()
  {
    $reader = new Swift_MockCharacterReader();
    
    $factory = new Swift_MockCharacterReaderFactory();
    $factory->setReturnValue('getReaderFor', $reader);
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $reader->setReturnValue('getInitialByteSize', 1);
    $reader->setReturnValueAt(0, 'validateByteSequence', 1);
    $reader->setReturnValueAt(1, 'validateByteSequence', 1);
    $reader->setReturnValueAt(2, 'validateByteSequence', 1);
    $reader->setReturnValueAt(3, 'validateByteSequence', 1);
    $reader->setReturnValueAt(4, 'validateByteSequence', 1);
    $reader->setReturnValueAt(5, 'validateByteSequence', 1);
    $reader->setReturnValueAt(6, 'validateByteSequence', 1);
    $reader->setReturnValueAt(7, 'validateByteSequence', 1);
    
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
  }
  
  public function testRequestingLargeCharCountPastEndOfStream()
  {
    $reader = new Swift_MockCharacterReader();
    
    $factory = new Swift_MockCharacterReaderFactory();
    $factory->setReturnValue('getReaderFor', $reader);
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $reader->setReturnValue('getInitialByteSize', 1);
    $reader->setReturnValueAt(0, 'validateByteSequence', 1);
    $reader->setReturnValueAt(1, 'validateByteSequence', 1);
    $reader->setReturnValueAt(2, 'validateByteSequence', 1);
    
    $stream->importString(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE));
    
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE),
      $stream->read(100)
      );
    
    $this->assertIdentical(false, $stream->read(1));
  }
  
  public function testRequestingByteArrayCountPastEndOfStream()
  {
    $reader = new Swift_MockCharacterReader();
    
    $factory = new Swift_MockCharacterReaderFactory();
    $factory->setReturnValue('getReaderFor', $reader);
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $reader->setReturnValue('getInitialByteSize', 1);
    $reader->setReturnValueAt(0, 'validateByteSequence', 1);
    $reader->setReturnValueAt(1, 'validateByteSequence', 1);
    $reader->setReturnValueAt(2, 'validateByteSequence', 1);
    
    $stream->importString(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE));
    
    $this->assertEqual(array(0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE),
      $stream->readBytes(100)
      );
    
    $this->assertIdentical(false, $stream->readBytes(1));
  }
  
  public function testPointerOffsetCanBeSet()
  {
    $reader = new Swift_MockCharacterReader();
    
    $factory = new Swift_MockCharacterReaderFactory();
    $factory->setReturnValue('getReaderFor', $reader);
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $reader->setReturnValue('getInitialByteSize', 1);
    $reader->setReturnValueAt(0, 'validateByteSequence', 1);
    $reader->setReturnValueAt(1, 'validateByteSequence', 1);
    $reader->setReturnValueAt(2, 'validateByteSequence', 1);
    
    $stream->importString(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE));
    
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0x94), $stream->read(1));
    
    $stream->setPointer(0);
    
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0x94), $stream->read(1));
    
    $stream->setPointer(2);
    
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0xBE), $stream->read(1));
  }
  
  public function testContentsCanBeFlushed()
  {
    $reader = new Swift_MockCharacterReader();
    
    $factory = new Swift_MockCharacterReaderFactory();
    $factory->setReturnValue('getReaderFor', $reader);
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $reader->setReturnValue('getInitialByteSize', 1);
    $reader->setReturnValueAt(0, 'validateByteSequence', 1);
    $reader->setReturnValueAt(1, 'validateByteSequence', 1);
    $reader->setReturnValueAt(2, 'validateByteSequence', 1);
    
    $stream->importString(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE));
    
    $stream->flushContents();
    
    $this->assertIdentical(false, $stream->read(1));
  }
  
  public function testByteStreamCanBeImportingUsesValidator()
  { 
    $reader = new Swift_MockCharacterReader();
    
    $factory = new Swift_MockCharacterReaderFactory();
    $factory->setReturnValue('getReaderFor', $reader);
    $factory->expectOnce('getReaderFor', array('utf-8'));
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $os = new Swift_MockOutputByteStream();
    $os->expectAt(0, 'read', array(1));
    $os->setReturnValueAt(0, 'read', pack('C*', 0xD0));
    $os->expectAt(1, 'read', array(1));
    $os->setReturnValueAt(1, 'read', pack('C*', 0x94));
    $os->expectAt(2, 'read', array(1));
    $os->setReturnValueAt(2, 'read', pack('C*', 0xD0));
    $os->expectAt(3, 'read', array(1));
    $os->setReturnValueAt(3, 'read', pack('C*', 0xB6));
    $os->expectAt(4, 'read', array(1));
    $os->setReturnValueAt(4, 'read', pack('C*', 0xD0));
    $os->expectAt(5, 'read', array(1));
    $os->setReturnValueAt(5, 'read', pack('C*', 0xBE));
    $os->expectAt(6, 'read', array(1));
    $os->setReturnValueAt(6, 'read', false);
    
    $os->expectCallCount('read', 7);
    
    $reader->setReturnValue('getInitialByteSize', 1);
    $reader->expectAt(0, 'validateByteSequence', array(array(0xD0)));
    $reader->setReturnValue('validateByteSequence', 1, array(array(0xD0)));
    
    $reader->expectAt(1, 'validateByteSequence', array(array(0xD0)));
    $reader->setReturnValue('validateByteSequence', 1, array(array(0xD0)));
    
    $reader->expectAt(2, 'validateByteSequence', array(array(0xD0)));
    $reader->setReturnValue('validateByteSequence', 1, array(array(0xD0)));
    
    $stream->importByteStream($os);
  }
  
  public function testImportingStreamProducesCorrectCharArray()
  {
    $reader = new Swift_MockCharacterReader();
    
    $factory = new Swift_MockCharacterReaderFactory();
    $factory->setReturnValue('getReaderFor', $reader);
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $os = new Swift_MockOutputByteStream();
    $os->setReturnValueAt(0, 'read', pack('C*', 0xD0));
    $os->setReturnValueAt(1, 'read', pack('C*', 0x94));
    $os->setReturnValueAt(2, 'read', pack('C*', 0xD0));
    $os->setReturnValueAt(3, 'read', pack('C*', 0xB6));
    $os->setReturnValueAt(4, 'read', pack('C*', 0xD0));
    $os->setReturnValueAt(5, 'read', pack('C*', 0xBE));
    $os->setReturnValueAt(6, 'read', false);
    
    $reader->setReturnValue('getInitialByteSize', 1);
    $reader->setReturnValueAt(0, 'validateByteSequence', 1);
    $reader->setReturnValueAt(1, 'validateByteSequence', 1);
    $reader->setReturnValueAt(2, 'validateByteSequence', 1);
    
    $stream->importByteStream($os);
    
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0x94), $stream->read(1));
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0xB6), $stream->read(1));
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0xBE), $stream->read(1));
    
    $this->assertIdentical(false, $stream->read(1));
  }
  
  public function testAlgorithmWithFixedWidthCharsets()
  {
    $reader = new Swift_MockCharacterReader();
    
    $factory = new Swift_MockCharacterReaderFactory();
    $factory->setReturnValue('getReaderFor', $reader);
    
    $reader->setReturnValue('getInitialByteSize', 2);
    $reader->expectAt(0, 'validateByteSequence', array(array(0xD1, 0x8D)));
    $reader->expectAt(1, 'validateByteSequence', array(array(0xD0, 0xBB)));
    $reader->expectAt(2, 'validateByteSequence', array(array(0xD0, 0xB0)));
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream(
      $factory, 'utf-8'
    );
    $stream->importString(pack('C*', 0xD1, 0x8D, 0xD0, 0xBB, 0xD0, 0xB0));
      
    $this->assertIdenticalBinary(pack('C*', 0xD1, 0x8D), $stream->read(1));
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0xBB), $stream->read(1));
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0xB0), $stream->read(1));
    
    $this->assertIdentical(false, $stream->read(1));
  }
  
}
