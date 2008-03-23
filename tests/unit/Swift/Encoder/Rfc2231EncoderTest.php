<?php

require_once 'Swift/Encoder/Rfc2231Encoder.php';
require_once 'Swift/CharacterStream.php';
require_once 'Swift/Tests/SwiftUnitTestCase.php';

class Swift_Encoder_Rfc2231EncoderTest extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_rfc2045Token = '/^[\x21\x23-\x27\x2A\x2B\x2D\x2E\x30-\x39\x41-\x5A\x5E-\x7E]+$/D';
  
  /* --
  This algorithm is described in RFC 2231, but is barely touched upon except
  for mentioning bytes can be represented as their octet values (e.g. %20 for
  the SPACE character).
  
  The tests here focus on how to use that representation to always generate text
  which matches RFC 2045's definition of "token".
  */
  
  public function testEncodingAsciiCharactersProducesValidToken()
  {
    $context = new Mockery();
    $charStream = $context->mock('Swift_CharacterStream');
    $seq = $context->sequence('byte-sequence');
    
    $string = '';
    foreach (range(0x00, 0x7F) as $octet)
    {
      $char = pack('C', $octet);
      $string .= $char;
      $context->checking(Expectations::create()
        -> one($charStream)->read(optional()) -> inSequence($seq) -> returns($char)
        );
    }
    $context->checking(Expectations::create()
      -> atLeast(1)->of($charStream)->read(optional()) -> inSequence($seq) -> returns(false)
      -> one($charStream)->importString($string)
      -> ignoring($charStream)->flushContents()
      );
    
    $encoder = new Swift_Encoder_Rfc2231Encoder($charStream);
    $encoded = $encoder->encodeString($string);
    
    foreach (explode("\r\n", $encoded) as $line)
    {
      $this->assertPattern($this->_rfc2045Token, $line,
        '%s: Encoder should always return a valid RFC 2045 token.');
    }
    
    $context->assertIsSatisfied();
  }
  
  public function testEncodingNonAsciiCharactersProducesValidToken()
  {
    $context = new Mockery();
    $charStream = $context->mock('Swift_CharacterStream');
    $seq = $context->sequence('byte-sequence');
    
    $string = '';
    foreach (range(0x80, 0xFF) as $octet)
    {
      $char = pack('C', $octet);
      $string .= $char;
      $context->checking(Expectations::create()
        -> one($charStream)->read(optional()) -> inSequence($seq) -> returns($char)
        );
    }
    $context->checking(Expectations::create()
      -> atLeast(1)->of($charStream)->read(optional()) -> inSequence($seq) -> returns(false)
      -> one($charStream)->importString($string)
      -> ignoring($charStream)->flushContents()
      );
    $encoder = new Swift_Encoder_Rfc2231Encoder($charStream);
    
    $encoded = $encoder->encodeString($string);
    
    foreach (explode("\r\n", $encoded) as $line)
    {
      $this->assertPattern($this->_rfc2045Token, $line,
        '%s: Encoder should always return a valid RFC 2045 token.');
    }
    
    $context->assertIsSatisfied();
  }
  
  public function testMaximumLineLengthCanBeSet()
  {
    $context = new Mockery();
    $charStream = $context->mock('Swift_CharacterStream');
    $seq = $context->sequence('byte-sequence');
    
    $string = '';
    for ($x = 0; $x < 200; ++$x)
    {
      $char = 'a';
      $string .= $char;
      $context->checking(Expectations::create()
        -> one($charStream)->read(optional()) -> inSequence($seq) -> returns($char)
        );
    }
    $context->checking(Expectations::create()
      -> atLeast(1)->of($charStream)->read(optional()) -> inSequence($seq) -> returns(false)
      -> one($charStream)->importString($string)
      -> ignoring($charStream)->flushContents()
      );
    $encoder = new Swift_Encoder_Rfc2231Encoder($charStream);
    
    $encoded = $encoder->encodeString($string, 0, 75);
    
    $this->assertEqual(
      str_repeat('a', 75) . "\r\n" .
      str_repeat('a', 75) . "\r\n" .
      str_repeat('a', 50),
      $encoded,
      '%s: Lines should be wrapped at each 75 characters'
      );
      
    $context->assertIsSatisfied();
  }
  
  public function testFirstLineCanHaveShorterLength()
  {
    $context = new Mockery();
    $charStream = $context->mock('Swift_CharacterStream');
    $seq = $context->sequence('byte-sequence');
    
    $string = '';
    for ($x = 0; $x < 200; ++$x)
    {
      $char = 'a';
      $string .= $char;
      $context->checking(Expectations::create()
        -> one($charStream)->read(optional()) -> inSequence($seq) -> returns($char)
        );
    }
    $context->checking(Expectations::create()
      -> atLeast(1)->of($charStream)->read(optional()) -> inSequence($seq) -> returns(false)
      -> one($charStream)->importString($string)
      -> ignoring($charStream)->flushContents()
      );
    $encoder = new Swift_Encoder_Rfc2231Encoder($charStream);
    $encoded = $encoder->encodeString($string, 25, 75);
    
    $this->assertEqual(
      str_repeat('a', 50) . "\r\n" .
      str_repeat('a', 75) . "\r\n" .
      str_repeat('a', 75),
      $encoded,
      '%s: First line should be 25 bytes shorter than the others.'
      );
    
    $context->assertIsSatisfied();
  }
  
}
