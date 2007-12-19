<?php

require_once 'Swift/CharacterSetValidator/Utf8Validator.php';

class Swift_CharacterSetValidator_Utf8ValidatorTest
  extends UnitTestCase
{
  
  private $_validator;
  
  public function setUp()
  {
    $this->_validator = new Swift_CharacterSetValidator_Utf8Validator();
  }
  
  public function testLeading7BitOctetCausesReturnZero()
  { 
    for ($ordinal = 0x00; $ordinal <= 0x7F; ++$ordinal)
    {
      $char = pack('C', $ordinal);
      $this->assertIdentical(0, $this->_validator->validateCharacter($char));
    }
  }
  
  public function testLeadingByteOf2OctetCharCausesReturn1()
  {
    for ($octet = 0xC0; $octet <= 0xDF; ++$octet)
    {
      $char = pack('C', $octet);
      $this->assertIdentical(1, $this->_validator->validateCharacter($char));
    }
  }
  
  public function testLeadingByteOf3OctetCharCausesReturn2()
  {
    for ($octet = 0xE0; $octet <= 0xEF; ++$octet)
    {
      $char = pack('C', $octet);
      $this->assertIdentical(2, $this->_validator->validateCharacter($char));
    }
  }
  
  public function testLeadingByteOf4OctetCharCausesReturn3()
  {
    for ($octet = 0xF0; $octet <= 0xF7; ++$octet)
    {
      $char = pack('C', $octet);
      $this->assertIdentical(3, $this->_validator->validateCharacter($char));
    }
  }
  
  public function testLeadingByteOf5OctetCharCausesReturn4()
  {
    for ($octet = 0xF8; $octet <= 0xFB; ++$octet)
    {
      $char = pack('C', $octet);
      $this->assertIdentical(4, $this->_validator->validateCharacter($char));
    }
  }
  
  public function testLeadingByteOf6OctetCharCausesReturn5()
  {
    for ($octet = 0xFC; $octet <= 0xFD; ++$octet)
    {
      $char = pack('C', $octet);
      $this->assertIdentical(5, $this->_validator->validateCharacter($char));
    }
  }
  
  public function testOctetsFEandFFAreInvalid()
  {
    $char = pack('C', 0xFE);
    $this->assertIdentical(-1, $this->_validator->validateCharacter($char));
    
    $char = pack('C', 0xFF);
    $this->assertIdentical(-1, $this->_validator->validateCharacter($char));
  }
  
}
