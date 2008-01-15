<?php

require_once 'Swift/Mime/Header/ListHeader.php';
require_once 'Swift/Mime/HeaderEncoder.php';

Mock::generate('Swift_Mime_HeaderEncoder', 'Swift_Mime_MockHeaderEncoder');

class Swift_Mime_Header_ListHeaderTest extends UnitTestCase
{
  
  private $_charset = 'utf-8';
  
  /* --
   The tests here cover the definition of the Keywords header in RFC 2822.
   There can be other headers which are simple lists of comma-separated values.
   */
  
  public function testValuesCanBeSetAndFetched()
  {
    $header = $this->_getHeader('Keywords', array('foo', 'bar'));
    $this->assertEqual(array('foo', 'bar'), $header->getValueList());
  }
  
  public function testSetterCanBeUsedToSetValues()
  {
    $header = $this->_getHeader('Keywords');
    $header->setValueList(array('foo', 'bar'));
    $this->assertEqual(array('foo', 'bar'), $header->getValueList());
  }
  
  public function testValuesAppearCommaSeparated()
  {
    $header = $this->_getHeader('Keywords', array('foo', 'bar'));
    $this->assertEqual('foo, bar', $header->getValue());
  }
  
  public function testSpecialCharsInValuesAreQuoted()
  {
    $header = $this->_getHeader('Keywords', array('foo, bar', 'zip, button'));
    $this->assertEqual('"foo\\, bar", "zip\\, button"', $header->getValue());
  }
  
  public function testNonAsciiCharsAreEncoded()
  {
    $encoder = new Swift_Mime_MockHeaderEncoder();
    $encoder->setReturnValue('getName', 'Q');
    $encoder->expectOnce('encodeString',
      array('f' . pack('C', 0x8F) . 'o', '*', '*')
      );
    $encoder->setReturnValue('encodeString', 'f=8Fo');
    
    $header = $this->_getHeader('Keywords',
      array('f' . pack('C', 0x8F) . 'o', 'bar'), $encoder
      );
    $this->assertEqual(array('f' . pack('C', 0x8F) . 'o', 'bar'),
      $header->getValueList()
      );
    $this->assertEqual('=?' . $this->_charset . '?Q?f=8Fo?=, bar',
      $header->getValue()
      );
  }
  
  public function testToString()
  {
    $header = $this->_getHeader('Keywords', array('foo', 'bar'));
    $this->assertEqual('Keywords: foo, bar' . "\r\n", $header->toString());
  }
  
  public function testSetValueAcceptsSinglePhrase()
  {
    $header = $this->_getHeader('Keywords');
    $header->setValue('my word');
    $this->assertEqual('my word', $header->getValue());
    $this->assertEqual(array('my word'), $header->getValueList());
  }
  
  public function testSetValueAcceptsList()
  {
    $header = $this->_getHeader('Keywords');
    $header->setValue('my first word, my second word');
    $this->assertEqual('my first word, my second word', $header->getValue());
    $this->assertEqual(array('my first word', 'my second word'),
      $header->getValueList()
      );
  }
  
  public function testSetValueUnquotesQuotedPairs()
  {
    $header = $this->_getHeader('Keywords');
    $header->setValue('"foo\\, bar", "zip\\, button"');
    $this->assertEqual('"foo\\, bar", "zip\\, button"', $header->getValue());
    $this->assertEqual(array('foo, bar', 'zip, button'),
      $header->getValueList()
      );
  }
  
  public function testSetValueDecodesEncodedWords()
  {
    $header = $this->_getHeader('Keywords');
    $header->setValue('=?utf-8?Q?f=8Fo?=, bar');
    $this->assertEqual('=?utf-8?Q?f=8Fo?=, bar', $header->getValue());
    $this->assertEqual(array('f' . pack('C', 0x8F) . 'o', 'bar'),
      $header->getValueList()
      );
  }
  
  public function testSetValueIgnoresComments()
  {
    $header = $this->_getHeader('Keywords');
    $header->setValue('foo bar (as in fubar), (dip)zip button');
    $this->assertEqual('foo bar (as in fubar), (dip)zip button',
      $header->getValue()
      );
    $this->assertEqual(array('foo bar', 'zip button'),
      $header->getValueList()
      );
  }
  
  public function testSetValueUnfoldsFWS()
  {
    $header = $this->_getHeader('Keywords');
    $header->setValue('foo' . "\r\n " . 'bar, zip' . "\r\n " . 'button');
    $this->assertEqual('foo' . "\r\n " . 'bar, zip' . "\r\n " . 'button',
      $header->getValue()
      );
    $this->assertEqual(array('foo bar', 'zip button'),
      $header->getValueList()
      );
  }
  
  // -- Private methods
  
  private function _getHeader($name, $values = array(), $encoder = null)
  {
    if (!$encoder)
    {
      $encoder = new Swift_Mime_MockHeaderEncoder();
    }
    return new Swift_Mime_Header_ListHeader(
      $name, $values, $this->_charset, $encoder
      );
  }
  
}
