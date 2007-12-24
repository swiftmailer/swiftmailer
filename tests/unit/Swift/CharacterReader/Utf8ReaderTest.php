<?php

require_once 'Swift/CharacterReader/Utf8Reader.php';

class Swift_CharacterReader_Utf8ReaderTest
  extends UnitTestCase
{
  
  private $_reader;
  
  public function setUp()
  {
    $this->_reader = new Swift_CharacterReader_Utf8Reader();
  }
  
  public function testLeading7BitOctetCausesReturnZero()
  { 
    for ($ordinal = 0x00; $ordinal <= 0x7F; ++$ordinal)
    {
      $char = pack('C', $ordinal);
      $this->assertIdentical(0, $this->_reader->validateCharacter($char));
    }
  }
  
  public function testLeadingByteOf2OctetCharCausesReturn1()
  {
    for ($octet = 0xC0; $octet <= 0xDF; ++$octet)
    {
      $char = pack('C', $octet);
      $this->assertIdentical(1, $this->_reader->validateCharacter($char));
    }
  }
  
  public function testLeadingByteOf3OctetCharCausesReturn2()
  {
    for ($octet = 0xE0; $octet <= 0xEF; ++$octet)
    {
      $char = pack('C', $octet);
      $this->assertIdentical(2, $this->_reader->validateCharacter($char));
    }
  }
  
  public function testLeadingByteOf4OctetCharCausesReturn3()
  {
    for ($octet = 0xF0; $octet <= 0xF7; ++$octet)
    {
      $char = pack('C', $octet);
      $this->assertIdentical(3, $this->_reader->validateCharacter($char));
    }
  }
  
  public function testLeadingByteOf5OctetCharCausesReturn4()
  {
    for ($octet = 0xF8; $octet <= 0xFB; ++$octet)
    {
      $char = pack('C', $octet);
      $this->assertIdentical(4, $this->_reader->validateCharacter($char));
    }
  }
  
  public function testLeadingByteOf6OctetCharCausesReturn5()
  {
    for ($octet = 0xFC; $octet <= 0xFD; ++$octet)
    {
      $char = pack('C', $octet);
      $this->assertIdentical(5, $this->_reader->validateCharacter($char));
    }
  }
  
}
