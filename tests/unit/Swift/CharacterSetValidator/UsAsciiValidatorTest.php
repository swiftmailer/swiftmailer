<?php

require_once 'Swift/CharacterSetValidator/UsAsciiValidator.php';

class Swift_CharacterSetValidator_UsAsciiValidatorTest
  extends UnitTestCase
{
  
  /*
  
  for ($c = '', $size = 1; false !== $bytes = $os->read($size); )
  {
    $c .= $bytes;
    $size = $v->validateCharacter($c);
    if (-1 == $size)
    {
      throw new Exception( ... invalid char .. );
    }
    elseif (0 == $size)
    {
      return $c; //next character in $os
    }
  }
  
  */
  
  private $_validator;
  
  public function setUp()
  {
    $this->_validator = new Swift_CharacterSetValidator_UsAsciiValidator();
  }
  
  public function testAllValidAsciiCharactersReturnZero()
  {
    for ($ordinal = 0x00; $ordinal <= 0x7F; ++$ordinal)
    {
      $char = pack('C', $ordinal);
      $this->assertIdentical(0, $this->_validator->validateCharacter($char));
    }
  }
  
  public function testMultipleBytesAreInvalid()
  {
    for ($ordinal = 0x00; $ordinal <= 0x7F; $ordinal += 2)
    {
      $char = pack('C', $ordinal) . pack('C', $ordinal + 1);
      $this->assertIdentical(-1, $this->_validator->validateCharacter($char));
    }
  }
  
  public function testBytesAboveAsciiRangeAreInvalid()
  {
    for ($ordinal = 0x80; $ordinal <= 0xFF; ++$ordinal)
    {
      $char = pack('C', $ordinal);
      $this->assertIdentical(-1, $this->_validator->validateCharacter($char));
    }
  }
  
}
