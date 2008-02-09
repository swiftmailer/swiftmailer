<?php

require_once 'Swift/AbstractSwiftUnitTestCase.php';
require_once 'Swift/CharacterStream/ArrayCharacterStream.php';
require_once 'Swift/CharacterReaderFactory.php';
require_once 'Swift/CharacterReader.php';
require_once 'Swift/ByteStream.php';

Mock::generate(
  'Swift_CharacterReader', 'Swift_MockCharacterReader'
  );
Mock::generate(
  'Swift_CharacterReaderFactory', 'Swift_MockCharacterReaderFactory'
  );
Mock::generate('Swift_ByteStream', 'Swift_MockByteStream');

class Swift_CharacterStream_ArrayCharacterStreamTest
  extends Swift_AbstractSwiftUnitTestCase
{
  
  public function testValidatorAlgorithmOnImportString()
  {
    $reader = new Swift_MockCharacterReader();
    
    $factory = new Swift_MockCharacterReaderFactory();
    $factory->setReturnValue('getReaderFor', $reader);
    $factory->expectOnce('getReaderFor', array('utf-8'));
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $reader->setReturnValue('getInitialByteSize', 1);
    $reader->expectAt(0, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0))
      ));
    $reader->setReturnValueAt(0, 'validateCharacter', 1);
    $reader->expectAt(1, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0, 0x94))
      ));
    $reader->setReturnValueAt(1, 'validateCharacter', 0);
    $reader->expectAt(2, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0))
      ));
    $reader->setReturnValueAt(2, 'validateCharacter', 1);
    $reader->expectAt(3, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0, 0xB6))
      ));
    $reader->setReturnValueAt(3, 'validateCharacter', 0);
    $reader->expectAt(4, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0))
      ));
    $reader->setReturnValueAt(4, 'validateCharacter', 1);
    $reader->expectAt(5, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0, 0xBE))
      ));
    $reader->setReturnValueAt(5, 'validateCharacter', 0);
    $reader->expectAt(6, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD1))
      ));
    $reader->setReturnValueAt(6, 'validateCharacter', 1);
    $reader->expectAt(7, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD1, 0x8D))
      ));
    $reader->setReturnValueAt(7, 'validateCharacter', 0);
    $reader->expectAt(8, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0))
      ));
    $reader->setReturnValueAt(8, 'validateCharacter', 1);
    $reader->expectAt(9, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0, 0xBB))
      ));
    $reader->setReturnValueAt(9, 'validateCharacter', 0);
    $reader->expectAt(10, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0))
      ));
    $reader->setReturnValueAt(10, 'validateCharacter', 1);
    $reader->expectAt(11, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0, 0xB0))
      ));
    $reader->setReturnValueAt(11, 'validateCharacter', 0);
    
    $reader->expectCallCount('validateCharacter', 12);
    
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
    $reader->expectAt(0, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0))
      ));
    $reader->setReturnValueAt(0, 'validateCharacter', 1);
    $reader->expectAt(1, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0, 0x94))
      ));
    $reader->setReturnValueAt(1, 'validateCharacter', 0);
    $reader->expectAt(2, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0))
      ));
    $reader->setReturnValueAt(2, 'validateCharacter', 1);
    $reader->expectAt(3, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0, 0xB6))
      ));
    $reader->setReturnValueAt(3, 'validateCharacter', 0);
    $reader->expectAt(4, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0))
      ));
    $reader->setReturnValueAt(4, 'validateCharacter', 1);
    $reader->expectAt(5, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0, 0xBE))
      ));
    $reader->setReturnValueAt(5, 'validateCharacter', 0);
    
    $reader->expectAt(6, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0))
      ));
    $reader->setReturnValueAt(6, 'validateCharacter', 1);
    $reader->expectAt(7, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0, 0xBB))
      ));
    $reader->setReturnValueAt(7, 'validateCharacter', 0);
    $reader->expectAt(8, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD1))
      ));
    $reader->setReturnValueAt(8, 'validateCharacter', 1);
    $reader->expectAt(9, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD1, 0x8E))
      ));
    $reader->setReturnValueAt(9, 'validateCharacter', 0);
    $reader->expectAt(10, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0))
      ));
    $reader->setReturnValueAt(10, 'validateCharacter', 1);
    $reader->expectAt(11, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0, 0xB1))
      ));
    $reader->setReturnValueAt(11, 'validateCharacter', 0);
    $reader->expectAt(12, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD1))
      ));
    $reader->setReturnValueAt(12, 'validateCharacter', 1);
    $reader->expectAt(13, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD1, 0x8B))
      ));
    $reader->setReturnValueAt(13, 'validateCharacter', 0);
    $reader->expectAt(14, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD1))
      ));
    $reader->setReturnValueAt(14, 'validateCharacter', 1);
    $reader->expectAt(15, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD1, 0x85))
      ));
    $reader->setReturnValueAt(15, 'validateCharacter', 0);
    
    $reader->expectCallCount('validateCharacter', 16);
    
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
  
  public function testReadCharacterAreInTact()
  {
    $reader = new Swift_MockCharacterReader();
    
    $factory = new Swift_MockCharacterReaderFactory();
    $factory->setReturnValue('getReaderFor', $reader);
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $reader->setReturnValue('getInitialByteSize', 1);
    $reader->setReturnValueAt(0, 'validateCharacter', 1);
    $reader->setReturnValueAt(1, 'validateCharacter', 0);
    $reader->setReturnValueAt(2, 'validateCharacter', 1);
    $reader->setReturnValueAt(3, 'validateCharacter', 0);
    $reader->setReturnValueAt(4, 'validateCharacter', 1);
    $reader->setReturnValueAt(5, 'validateCharacter', 0);
    $reader->setReturnValueAt(6, 'validateCharacter', 1);
    $reader->setReturnValueAt(7, 'validateCharacter', 0);
    $reader->setReturnValueAt(8, 'validateCharacter', 1);
    $reader->setReturnValueAt(9, 'validateCharacter', 0);
    $reader->setReturnValueAt(10, 'validateCharacter', 1);
    $reader->setReturnValueAt(11, 'validateCharacter', 0);
    $reader->setReturnValueAt(12, 'validateCharacter', 1);
    $reader->setReturnValueAt(13, 'validateCharacter', 0);
    $reader->setReturnValueAt(14, 'validateCharacter', 1);
    $reader->setReturnValueAt(15, 'validateCharacter', 0);
    
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
  
  public function testRequestingLargeCharCountPastEndOfStream()
  {
    $reader = new Swift_MockCharacterReader();
    
    $factory = new Swift_MockCharacterReaderFactory();
    $factory->setReturnValue('getReaderFor', $reader);
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $reader->setReturnValue('getInitialByteSize', 1);
    $reader->setReturnValueAt(0, 'validateCharacter', 1);
    $reader->setReturnValueAt(1, 'validateCharacter', 0);
    $reader->setReturnValueAt(2, 'validateCharacter', 1);
    $reader->setReturnValueAt(3, 'validateCharacter', 0);
    $reader->setReturnValueAt(4, 'validateCharacter', 1);
    $reader->setReturnValueAt(5, 'validateCharacter', 0);
    
    $stream->importString(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE));
    
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE),
      $stream->read(100)
      );
    
    $this->assertIdentical(false, $stream->read(1));
  }
  
  public function testPointerOffsetCanBeSet()
  {
    $reader = new Swift_MockCharacterReader();
    
    $factory = new Swift_MockCharacterReaderFactory();
    $factory->setReturnValue('getReaderFor', $reader);
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $reader->setReturnValue('getInitialByteSize', 1);
    $reader->setReturnValueAt(0, 'validateCharacter', 1);
    $reader->setReturnValueAt(1, 'validateCharacter', 0);
    $reader->setReturnValueAt(2, 'validateCharacter', 1);
    $reader->setReturnValueAt(3, 'validateCharacter', 0);
    $reader->setReturnValueAt(4, 'validateCharacter', 1);
    $reader->setReturnValueAt(5, 'validateCharacter', 0);
    
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
    $reader->setReturnValueAt(0, 'validateCharacter', 1);
    $reader->setReturnValueAt(1, 'validateCharacter', 0);
    $reader->setReturnValueAt(2, 'validateCharacter', 1);
    $reader->setReturnValueAt(3, 'validateCharacter', 0);
    $reader->setReturnValueAt(4, 'validateCharacter', 1);
    $reader->setReturnValueAt(5, 'validateCharacter', 0);
    
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
    
    $os = new Swift_MockByteStream();
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
    $reader->expectAt(0, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0))
      ));
    $reader->setReturnValueAt(0, 'validateCharacter', 1);
    $reader->expectAt(1, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0, 0x94))
      ));
    $reader->setReturnValueAt(1, 'validateCharacter', 0);
    $reader->expectAt(2, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0))
      ));
    $reader->setReturnValueAt(2, 'validateCharacter', 1);
    $reader->expectAt(3, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0, 0xB6))
      ));
    $reader->setReturnValueAt(3, 'validateCharacter', 0);
    $reader->expectAt(4, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0))
      ));
    $reader->setReturnValueAt(4, 'validateCharacter', 1);
    $reader->expectAt(5, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0, 0xBE))
      ));
    $reader->setReturnValueAt(5, 'validateCharacter', 0);
    
    $stream->importByteStream($os);
  }
  
  public function testImportingStreamProducesCorrectCharArray()
  {
    $reader = new Swift_MockCharacterReader();
    
    $factory = new Swift_MockCharacterReaderFactory();
    $factory->setReturnValue('getReaderFor', $reader);
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8');
    
    $os = new Swift_MockByteStream();
    $os->setReturnValueAt(0, 'read', pack('C*', 0xD0));
    $os->setReturnValueAt(1, 'read', pack('C*', 0x94));
    $os->setReturnValueAt(2, 'read', pack('C*', 0xD0));
    $os->setReturnValueAt(3, 'read', pack('C*', 0xB6));
    $os->setReturnValueAt(4, 'read', pack('C*', 0xD0));
    $os->setReturnValueAt(5, 'read', pack('C*', 0xBE));
    $os->setReturnValueAt(6, 'read', false);
    
    $reader->setReturnValue('getInitialByteSize', 1);
    $reader->setReturnValueAt(0, 'validateCharacter', 1);
    $reader->setReturnValueAt(1, 'validateCharacter', 0);
    $reader->setReturnValueAt(2, 'validateCharacter', 1);
    $reader->setReturnValueAt(3, 'validateCharacter', 0);
    $reader->setReturnValueAt(4, 'validateCharacter', 1);
    $reader->setReturnValueAt(5, 'validateCharacter', 0);
    
    $stream->importByteStream($os);
    
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0x94), $stream->read(1));
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0xB6), $stream->read(1));
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0xBE), $stream->read(1));
    
    $this->assertIdentical(false, $stream->read(1));
  }
  
  public function testInitialArrayCanBePassedToConstructor()
  {
    $reader = new Swift_MockCharacterReader();
    
    $factory = new Swift_MockCharacterReaderFactory();
    $factory->setReturnValue('getReaderFor', $reader);
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream(
      $factory, 'utf-8',
      array(pack('C*', 0xD1, 0x8D), pack('C*', 0xD0, 0xBB), pack('C*', 0xD0, 0xB0))
      );
    $this->assertIdenticalBinary(pack('C*', 0xD1, 0x8D), $stream->read(1));
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0xBB), $stream->read(1));
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0xB0), $stream->read(1));
    
    $this->assertIdentical(false, $stream->read(1));
  }
  
  public function testStringCanBePassedToConstructor()
  {
    $reader = new Swift_MockCharacterReader();
    
    $factory = new Swift_MockCharacterReaderFactory();
    $factory->setReturnValue('getReaderFor', $reader);
    
    $reader->setReturnValue('getInitialByteSize', 1);
    $reader->setReturnValueAt(0, 'validateCharacter', 1);
    $reader->setReturnValueAt(1, 'validateCharacter', 0);
    $reader->setReturnValueAt(2, 'validateCharacter', 1);
    $reader->setReturnValueAt(3, 'validateCharacter', 0);
    $reader->setReturnValueAt(4, 'validateCharacter', 1);
    $reader->setReturnValueAt(5, 'validateCharacter', 0);
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream(
      $factory, 'utf-8', pack('C*', 0xD1, 0x8D, 0xD0, 0xBB, 0xD0, 0xB0)
    );
      
    $this->assertIdenticalBinary(pack('C*', 0xD1, 0x8D), $stream->read(1));
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0xBB), $stream->read(1));
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0xB0), $stream->read(1));
    
    $this->assertIdentical(false, $stream->read(1));
  }
  
  public function testAlgorithmWithFixedWidthCharsets()
  {
    $reader = new Swift_MockCharacterReader();
    
    $factory = new Swift_MockCharacterReaderFactory();
    $factory->setReturnValue('getReaderFor', $reader);
    
    $reader->setReturnValue('getInitialByteSize', 2);
    $reader->expectAt(0, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD1, 0x8D))
      ));
    $reader->setReturnValueAt(0, 'validateCharacter', 0);
    $reader->expectAt(1, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0, 0xBB))
      ));
    $reader->setReturnValueAt(1, 'validateCharacter', 0);
    $reader->expectAt(2, 'validateCharacter', array(
      new Swift_IdenticalBinaryExpectation(pack('C*', 0xD0, 0xB0))
      ));
    $reader->setReturnValueAt(2, 'validateCharacter', 0);
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream(
      $factory, 'utf-8', pack('C*', 0xD1, 0x8D, 0xD0, 0xBB, 0xD0, 0xB0)
    );
      
    $this->assertIdenticalBinary(pack('C*', 0xD1, 0x8D), $stream->read(1));
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0xBB), $stream->read(1));
    $this->assertIdenticalBinary(pack('C*', 0xD0, 0xB0), $stream->read(1));
    
    $this->assertIdentical(false, $stream->read(1));
  }
  
}
