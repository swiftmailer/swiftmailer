<?php

require_once 'Swift/Encoder/Rfc2231Encoder.php';
require_once 'Swift/CharacterStream.php';
require_once 'Swift/Tests/SwiftUnitTestCase.php';

Mock::Generate('Swift_CharacterStream', 'Swift_MockCharacterStream');

class Swift_Encoder_Rfc2231EncoderTest extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_encoder;
  private $_charStream;
  private $_rfc2045Token = '/^[\x21\x23-\x27\x2A\x2B\x2D\x2E\x30-\x39\x41-\x5A\x5E-\x7E]+$/D';
  
  public function setUp()
  {
    $this->_charStream = new Swift_MockCharacterStream();
    $this->_encoder = new Swift_Encoder_Rfc2231Encoder($this->_charStream);
  }
  
  /* --
  This algorithm is described in RFC 2231, but is barely touched upon except
  for mentioning bytes can be represented as their octet values (e.g. %20 for
  the SPACE character).
  
  The tests here focus on how to use that representation to always generate text
  which matches RFC 2045's definition of "token".
  */
  
  public function testEncodingAsciiCharactersProducesValidToken()
  {
    $string = '';
    $i = 0;
    foreach (range(0x00, 0x7F) as $octet)
    {
      $char = pack('C', $octet);
      $string .= $char;
      $this->_charStream->setReturnValueAt($i++, 'read', $char);
    }
    $this->_charStream->setReturnValueAt($i++, 'read', false);
    $this->_charStream->expectCallCount('read', $i);
    $this->_charStream->expectOnce('importString', array(
      new Swift_Tests_IdenticalBinaryExpectation($string)
      ));
    
    $encoded = $this->_encoder->encodeString($string);
    
    foreach (explode("\r\n", $encoded) as $line)
    {
      $this->assertPattern($this->_rfc2045Token, $line,
        '%s: Encoder should always return a valid RFC 2045 token.');
    }
  }
  
  public function testEncodingNonAsciiCharactersProducesValidToken()
  {
    $string = '';
    $i = 0;
    foreach (range(0x80, 0xFF) as $octet)
    {
      $char = pack('C', $octet);
      $string .= $char;
      $this->_charStream->setReturnValueAt($i++, 'read', $char);
    }
    $this->_charStream->setReturnValueAt($i++, 'read', false);
    $this->_charStream->expectCallCount('read', $i);
    $this->_charStream->expectOnce('importString', array(
      new Swift_Tests_IdenticalBinaryExpectation($string)
      ));
    
    $encoded = $this->_encoder->encodeString($string);
    
    foreach (explode("\r\n", $encoded) as $line)
    {
      $this->assertPattern($this->_rfc2045Token, $line,
        '%s: Encoder should always return a valid RFC 2045 token.');
    }
  }
  
  public function testMaximumLineLengthCanBeSet()
  {
    $string = '';
    $i = 0;
    for ($x = 0; $x < 200; ++$x)
    {
      $char = 'a';
      $string .= $char;
      $this->_charStream->setReturnValueAt($i++, 'read', $char);
    }
    $this->_charStream->setReturnValueAt($i++, 'read', false);
    $this->_charStream->expectCallCount('read', $i);
    $this->_charStream->expectOnce('importString', array($string));
    
    $encoded = $this->_encoder->encodeString($string, 0, 75);
    
    $this->assertEqual(
      str_repeat('a', 75) . "\r\n" .
      str_repeat('a', 75) . "\r\n" .
      str_repeat('a', 50),
      $encoded,
      '%s: Lines should be wrapped at each 75 characters'
      );
  }
  
  public function testFirstLineCanHaveShorterLength()
  {
    $string = '';
    $i = 0;
    for ($x = 0; $x < 200; ++$x)
    {
      $char = 'a';
      $string .= $char;
      $this->_charStream->setReturnValueAt($i++, 'read', $char);
    }
    $this->_charStream->setReturnValueAt($i++, 'read', false);
    $this->_charStream->expectCallCount('read', $i);
    $this->_charStream->expectOnce('importString', array($string));
    
    $encoded = $this->_encoder->encodeString($string, 25, 75);
    
    $this->assertEqual(
      str_repeat('a', 50) . "\r\n" .
      str_repeat('a', 75) . "\r\n" .
      str_repeat('a', 75),
      $encoded,
      '%s: First line should be 25 bytes shorter than the others.'
      );
  }
  
}
