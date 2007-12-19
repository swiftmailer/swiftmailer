<?php

require_once 'Swift/CharacterStream/ArrayCharacterStream.php';
require_once 'Swift/CharacterSetValidatorFactory.php';
require_once 'Swift/CharacterSetValidator.php';
require_once 'Swift/ByteStream.php';

Mock::generate(
  'Swift_CharacterSetValidator', 'Swift_MockCharacterSetValidator'
  );
Mock::generate(
  'Swift_CharacterSetValidatorFactory', 'Swift_MockCharacterSetValidatorFactory'
  );
Mock::generate('Swift_ByteStream', 'Swift_MockByteStream');

class Swift_CharacterStream_ArrayCharacterStreamTest
  extends UnitTestCase
{
  
  public function testValidatorAlgorithmOnImportString()
  {
    $validator = new Swift_MockCharacterSetValidator();
    
    $factory = new Swift_MockCharacterSetValidatorFactory();
    $factory->setReturnValue('getValidatorFor', $validator);
    $factory->expectOnce('getValidatorFor', array('utf-8'));
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream();
    $stream->setCharacterSet('utf-8');
    $stream->setCharacterSetValidatorFactory($factory);
    
    $validator->expectAt(0, 'validateCharacter', array(pack('C*', 0xD0)));
    $validator->setReturnValueAt(0, 'validateCharacter', 1);
    $validator->expectAt(1, 'validateCharacter', array(pack('C*', 0xD0, 0x94)));
    $validator->setReturnValueAt(1, 'validateCharacter', 0);
    $validator->expectAt(2, 'validateCharacter', array(pack('C*', 0xD0)));
    $validator->setReturnValueAt(2, 'validateCharacter', 1);
    $validator->expectAt(3, 'validateCharacter', array(pack('C*', 0xD0, 0xB6)));
    $validator->setReturnValueAt(3, 'validateCharacter', 0);
    $validator->expectAt(4, 'validateCharacter', array(pack('C*', 0xD0)));
    $validator->setReturnValueAt(4, 'validateCharacter', 1);
    $validator->expectAt(5, 'validateCharacter', array(pack('C*', 0xD0, 0xBE)));
    $validator->setReturnValueAt(5, 'validateCharacter', 0);
    $validator->expectAt(6, 'validateCharacter', array(pack('C*', 0xD1)));
    $validator->setReturnValueAt(6, 'validateCharacter', 1);
    $validator->expectAt(7, 'validateCharacter', array(pack('C*', 0xD1, 0x8D)));
    $validator->setReturnValueAt(7, 'validateCharacter', 0);
    $validator->expectAt(8, 'validateCharacter', array(pack('C*', 0xD0)));
    $validator->setReturnValueAt(8, 'validateCharacter', 1);
    $validator->expectAt(9, 'validateCharacter', array(pack('C*', 0xD0, 0xBB)));
    $validator->setReturnValueAt(9, 'validateCharacter', 0);
    $validator->expectAt(10, 'validateCharacter', array(pack('C*', 0xD0)));
    $validator->setReturnValueAt(10, 'validateCharacter', 1);
    $validator->expectAt(11, 'validateCharacter', array(pack('C*', 0xD0, 0xB0)));
    $validator->setReturnValueAt(11, 'validateCharacter', 0);
    
    $validator->expectCallCount('validateCharacter', 12);
    
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
    $validator = new Swift_MockCharacterSetValidator();
    
    $factory = new Swift_MockCharacterSetValidatorFactory();
    $factory->setReturnValue('getValidatorFor', $validator);
    $factory->expectOnce('getValidatorFor', array('utf-8'));
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream();
    $stream->setCharacterSet('utf-8');
    $stream->setCharacterSetValidatorFactory($factory);
    
    $validator->expectAt(0, 'validateCharacter', array(pack('C*', 0xD0)));
    $validator->setReturnValueAt(0, 'validateCharacter', 1);
    $validator->expectAt(1, 'validateCharacter', array(pack('C*', 0xD0, 0x94)));
    $validator->setReturnValueAt(1, 'validateCharacter', 0);
    $validator->expectAt(2, 'validateCharacter', array(pack('C*', 0xD0)));
    $validator->setReturnValueAt(2, 'validateCharacter', 1);
    $validator->expectAt(3, 'validateCharacter', array(pack('C*', 0xD0, 0xB6)));
    $validator->setReturnValueAt(3, 'validateCharacter', 0);
    $validator->expectAt(4, 'validateCharacter', array(pack('C*', 0xD0)));
    $validator->setReturnValueAt(4, 'validateCharacter', 1);
    $validator->expectAt(5, 'validateCharacter', array(pack('C*', 0xD0, 0xBE)));
    $validator->setReturnValueAt(5, 'validateCharacter', 0);
    
    $validator->expectAt(6, 'validateCharacter', array(pack('C*', 0xD0)));
    $validator->setReturnValueAt(6, 'validateCharacter', 1);
    $validator->expectAt(7, 'validateCharacter', array(pack('C*', 0xD0, 0xBB)));
    $validator->setReturnValueAt(7, 'validateCharacter', 0);
    $validator->expectAt(8, 'validateCharacter', array(pack('C*', 0xD1)));
    $validator->setReturnValueAt(8, 'validateCharacter', 1);
    $validator->expectAt(9, 'validateCharacter', array(pack('C*', 0xD1, 0x8E)));
    $validator->setReturnValueAt(9, 'validateCharacter', 0);
    $validator->expectAt(10, 'validateCharacter', array(pack('C*', 0xD0)));
    $validator->setReturnValueAt(10, 'validateCharacter', 1);
    $validator->expectAt(11, 'validateCharacter', array(pack('C*', 0xD0, 0xB1)));
    $validator->setReturnValueAt(11, 'validateCharacter', 0);
    $validator->expectAt(12, 'validateCharacter', array(pack('C*', 0xD1)));
    $validator->setReturnValueAt(12, 'validateCharacter', 1);
    $validator->expectAt(13, 'validateCharacter', array(pack('C*', 0xD1, 0x8B)));
    $validator->setReturnValueAt(13, 'validateCharacter', 0);
    $validator->expectAt(14, 'validateCharacter', array(pack('C*', 0xD1)));
    $validator->setReturnValueAt(14, 'validateCharacter', 1);
    $validator->expectAt(15, 'validateCharacter', array(pack('C*', 0xD1, 0x85)));
    $validator->setReturnValueAt(15, 'validateCharacter', 0);
    
    $validator->expectCallCount('validateCharacter', 16);
    
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
    $validator = new Swift_MockCharacterSetValidator();
    
    $factory = new Swift_MockCharacterSetValidatorFactory();
    $factory->setReturnValue('getValidatorFor', $validator);
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream();
    $stream->setCharacterSet('utf-8');
    $stream->setCharacterSetValidatorFactory($factory);
    
    $validator->setReturnValueAt(0, 'validateCharacter', 1);
    $validator->setReturnValueAt(1, 'validateCharacter', 0);
    $validator->setReturnValueAt(2, 'validateCharacter', 1);
    $validator->setReturnValueAt(3, 'validateCharacter', 0);
    $validator->setReturnValueAt(4, 'validateCharacter', 1);
    $validator->setReturnValueAt(5, 'validateCharacter', 0);
    $validator->setReturnValueAt(6, 'validateCharacter', 1);
    $validator->setReturnValueAt(7, 'validateCharacter', 0);
    $validator->setReturnValueAt(8, 'validateCharacter', 1);
    $validator->setReturnValueAt(9, 'validateCharacter', 0);
    $validator->setReturnValueAt(10, 'validateCharacter', 1);
    $validator->setReturnValueAt(11, 'validateCharacter', 0);
    $validator->setReturnValueAt(12, 'validateCharacter', 1);
    $validator->setReturnValueAt(13, 'validateCharacter', 0);
    $validator->setReturnValueAt(14, 'validateCharacter', 1);
    $validator->setReturnValueAt(15, 'validateCharacter', 0);
    
    $stream->importString(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE));
    
    $stream->write(pack('C*',
      0xD0, 0xBB,
      0xD1, 0x8E,
      0xD0, 0xB1,
      0xD1, 0x8B,
      0xD1, 0x85
      )
    );
    
    $this->assertEqual(pack('C*', 0xD0, 0x94), $stream->read(1));
    $this->assertEqual(pack('C*', 0xD0, 0xB6, 0xD0, 0xBE), $stream->read(2));
    $this->assertEqual(pack('C*', 0xD0, 0xBB), $stream->read(1));
    $this->assertEqual(pack('C*', 0xD1, 0x8E, 0xD0, 0xB1, 0xD1, 0x8B), $stream->read(3));
    $this->assertEqual(pack('C*', 0xD1, 0x85), $stream->read(1));
    
    $this->assertIdentical(false, $stream->read(1));
  }
  
  public function testRequestingLargeCharCountPastEndOfStream()
  {
    $validator = new Swift_MockCharacterSetValidator();
    
    $factory = new Swift_MockCharacterSetValidatorFactory();
    $factory->setReturnValue('getValidatorFor', $validator);
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream();
    $stream->setCharacterSet('utf-8');
    $stream->setCharacterSetValidatorFactory($factory);
    
    $validator->setReturnValueAt(0, 'validateCharacter', 1);
    $validator->setReturnValueAt(1, 'validateCharacter', 0);
    $validator->setReturnValueAt(2, 'validateCharacter', 1);
    $validator->setReturnValueAt(3, 'validateCharacter', 0);
    $validator->setReturnValueAt(4, 'validateCharacter', 1);
    $validator->setReturnValueAt(5, 'validateCharacter', 0);
    
    $stream->importString(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE));
    
    $this->assertEqual(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE),
      $stream->read(100)
      );
    
    $this->assertIdentical(false, $stream->read(1));
  }
  
  public function testPointerOffsetCanBeSet()
  {
    $validator = new Swift_MockCharacterSetValidator();
    
    $factory = new Swift_MockCharacterSetValidatorFactory();
    $factory->setReturnValue('getValidatorFor', $validator);
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream();
    $stream->setCharacterSet('utf-8');
    $stream->setCharacterSetValidatorFactory($factory);
    
    $validator->setReturnValueAt(0, 'validateCharacter', 1);
    $validator->setReturnValueAt(1, 'validateCharacter', 0);
    $validator->setReturnValueAt(2, 'validateCharacter', 1);
    $validator->setReturnValueAt(3, 'validateCharacter', 0);
    $validator->setReturnValueAt(4, 'validateCharacter', 1);
    $validator->setReturnValueAt(5, 'validateCharacter', 0);
    
    $stream->importString(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE));
    
    $this->assertEqual(pack('C*', 0xD0, 0x94), $stream->read(1));
    
    $stream->setPointer(0);
    
    $this->assertEqual(pack('C*', 0xD0, 0x94), $stream->read(1));
    
    $stream->setPointer(2);
    
    $this->assertEqual(pack('C*', 0xD0, 0xBE), $stream->read(1));
  }
  
  public function testContentsCanBeFlushed()
  {
    $validator = new Swift_MockCharacterSetValidator();
    
    $factory = new Swift_MockCharacterSetValidatorFactory();
    $factory->setReturnValue('getValidatorFor', $validator);
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream();
    $stream->setCharacterSet('utf-8');
    $stream->setCharacterSetValidatorFactory($factory);
    
    $validator->setReturnValueAt(0, 'validateCharacter', 1);
    $validator->setReturnValueAt(1, 'validateCharacter', 0);
    $validator->setReturnValueAt(2, 'validateCharacter', 1);
    $validator->setReturnValueAt(3, 'validateCharacter', 0);
    $validator->setReturnValueAt(4, 'validateCharacter', 1);
    $validator->setReturnValueAt(5, 'validateCharacter', 0);
    
    $stream->importString(pack('C*', 0xD0, 0x94, 0xD0, 0xB6, 0xD0, 0xBE));
    
    $stream->flushContents();
    
    $this->assertIdentical(false, $stream->read(1));
  }
  
  public function testByteStreamCanBeImportingUsesValidator()
  { 
    $validator = new Swift_MockCharacterSetValidator();
    
    $factory = new Swift_MockCharacterSetValidatorFactory();
    $factory->setReturnValue('getValidatorFor', $validator);
    $factory->expectOnce('getValidatorFor', array('utf-8'));
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream();
    $stream->setCharacterSet('utf-8');
    $stream->setCharacterSetValidatorFactory($factory);
    
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
    
    $validator->expectAt(0, 'validateCharacter', array(pack('C*', 0xD0)));
    $validator->setReturnValueAt(0, 'validateCharacter', 1);
    $validator->expectAt(1, 'validateCharacter', array(pack('C*', 0xD0, 0x94)));
    $validator->setReturnValueAt(1, 'validateCharacter', 0);
    $validator->expectAt(2, 'validateCharacter', array(pack('C*', 0xD0)));
    $validator->setReturnValueAt(2, 'validateCharacter', 1);
    $validator->expectAt(3, 'validateCharacter', array(pack('C*', 0xD0, 0xB6)));
    $validator->setReturnValueAt(3, 'validateCharacter', 0);
    $validator->expectAt(4, 'validateCharacter', array(pack('C*', 0xD0)));
    $validator->setReturnValueAt(4, 'validateCharacter', 1);
    $validator->expectAt(5, 'validateCharacter', array(pack('C*', 0xD0, 0xBE)));
    $validator->setReturnValueAt(5, 'validateCharacter', 0);
    
    $stream->importByteStream($os);
  }
  
  public function testImportingStreamProducesCorrectCharArray()
  {
    $validator = new Swift_MockCharacterSetValidator();
    
    $factory = new Swift_MockCharacterSetValidatorFactory();
    $factory->setReturnValue('getValidatorFor', $validator);
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream();
    $stream->setCharacterSet('utf-8');
    $stream->setCharacterSetValidatorFactory($factory);
    
    $os = new Swift_MockByteStream();
    $os->setReturnValueAt(0, 'read', pack('C*', 0xD0));
    $os->setReturnValueAt(1, 'read', pack('C*', 0x94));
    $os->setReturnValueAt(2, 'read', pack('C*', 0xD0));
    $os->setReturnValueAt(3, 'read', pack('C*', 0xB6));
    $os->setReturnValueAt(4, 'read', pack('C*', 0xD0));
    $os->setReturnValueAt(5, 'read', pack('C*', 0xBE));
    $os->setReturnValueAt(6, 'read', false);
    
    $validator->setReturnValueAt(0, 'validateCharacter', 1);
    $validator->setReturnValueAt(1, 'validateCharacter', 0);
    $validator->setReturnValueAt(2, 'validateCharacter', 1);
    $validator->setReturnValueAt(3, 'validateCharacter', 0);
    $validator->setReturnValueAt(4, 'validateCharacter', 1);
    $validator->setReturnValueAt(5, 'validateCharacter', 0);
    
    $stream->importByteStream($os);
    
    $this->assertEqual(pack('C*', 0xD0, 0x94), $stream->read(1));
    $this->assertEqual(pack('C*', 0xD0, 0xB6), $stream->read(1));
    $this->assertEqual(pack('C*', 0xD0, 0xBE), $stream->read(1));
    
    $this->assertIdentical(false, $stream->read(1));
  }
  
  public function testInitialArrayCanBePassedToConstructor()
  {
    $stream = new Swift_CharacterStream_ArrayCharacterStream(
      array(pack('C*', 0xD1, 0x8D), pack('C*', 0xD0, 0xBB), pack('C*', 0xD0, 0xB0))
      );
    $this->assertEqual(pack('C*', 0xD1, 0x8D), $stream->read(1));
    $this->assertEqual(pack('C*', 0xD0, 0xBB), $stream->read(1));
    $this->assertEqual(pack('C*', 0xD0, 0xB0), $stream->read(1));
    
    $this->assertIdentical(false, $stream->read(1));
  }
  
  public function testStringCanBePassedToConstructor()
  {
    $validator = new Swift_MockCharacterSetValidator();
    
    $factory = new Swift_MockCharacterSetValidatorFactory();
    $factory->setReturnValue('getValidatorFor', $validator);
    
    $validator->setReturnValueAt(0, 'validateCharacter', 1);
    $validator->setReturnValueAt(1, 'validateCharacter', 0);
    $validator->setReturnValueAt(2, 'validateCharacter', 1);
    $validator->setReturnValueAt(3, 'validateCharacter', 0);
    $validator->setReturnValueAt(4, 'validateCharacter', 1);
    $validator->setReturnValueAt(5, 'validateCharacter', 0);
    
    $stream = new Swift_CharacterStream_ArrayCharacterStream(
      pack('C*', 0xD1, 0x8D, 0xD0, 0xBB, 0xD0, 0xB0), 'utf-8', $factory
    );
      
    $this->assertEqual(pack('C*', 0xD1, 0x8D), $stream->read(1));
    $this->assertEqual(pack('C*', 0xD0, 0xBB), $stream->read(1));
    $this->assertEqual(pack('C*', 0xD0, 0xB0), $stream->read(1));
    
    $this->assertIdentical(false, $stream->read(1));
  }
  
}
