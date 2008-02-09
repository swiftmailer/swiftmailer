<?php

require_once 'Swift/CharacterReader/GenericFixedWidthReader.php';

class Swift_CharacterReader_GenericFixedWidthReaderTest
  extends UnitTestCase
{
  
  public function testInitialByteSizeMatchesWidth()
  {
    $reader = new Swift_CharacterReader_GenericFixedWidthReader(1);
    $this->assertIdentical(1, $reader->getInitialByteSize());
    
    $reader = new Swift_CharacterReader_GenericFixedWidthReader(4);
    $this->assertIdentical(4, $reader->getInitialByteSize());
  }
  
  public function testValidationValueIsBasedOnOctetCount()
  {
    $reader = new Swift_CharacterReader_GenericFixedWidthReader(4);
    
    $char = pack('C*', 0x01, 0x02, 0x03); //3 octets
    $this->assertIdentical(1, $reader->validateCharacter($char));
    
    $char = pack('C*', 0x01, 0x0A); //2 octets
    $this->assertIdentical(2, $reader->validateCharacter($char));
    
    $char = pack('C*', 0xFE); //1 octet
    $this->assertIdentical(3, $reader->validateCharacter($char));
    
    $char = pack('C*', 0xFE, 0x03, 0x67, 0x9A); //All 4 octets
    $this->assertIdentical(0, $reader->validateCharacter($char));
  }
  
  public function testValidationFailsIfTooManyOctets()
  {
    $reader = new Swift_CharacterReader_GenericFixedWidthReader(6);
    
    $char = pack('C*', 0xFE, 0x03, 0x67, 0x9A, 0x10, 0x09, 0x85); //7 octets
    $this->assertIdentical(-1, $reader->validateCharacter($char));
  }
  
}
