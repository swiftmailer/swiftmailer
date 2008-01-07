<?php

require_once 'Swift/Mime/StructuredHeader.php';
require_once 'Swift/Mime/HeaderAttribute.php';
require_once 'Swift/Mime/HeaderAttributeSet.php';

Mock::generate('Swift_Mime_HeaderAttribute', 'Swift_Mime_MockHeaderAttribute');
Mock::generate('Swift_Mime_HeaderAttributeSet',
  'Swift_Mime_MockHeaderAttributeSet'
  );

class Swift_Mime_StructuredHeaderTest extends UnitTestCase
{

  public function testGetNameReturnsNameVerbatim()
  {
    $header = new Swift_Mime_StructuredHeader('Subject', 'Test');
    $this->assertEqual('Subject', $header->getName());
  }
  
  public function testGetValueReturnsValueVerbatim()
  {
    $header = new Swift_Mime_StructuredHeader('Subject', 'Test');
    $this->assertEqual('Test', $header->getValue());
  }
  
  public function testValueCanBeSet()
  {
    $header = new Swift_Mime_StructuredHeader('Subject', '');
    $header->setValue('Something');
    $this->assertEqual('Something', $header->getValue());
  }
  
  public function testAttributesCanBeSet()
  {
    $header = new Swift_Mime_StructuredHeader('Content-Type', 'text/html');
    
    $attributes = array();
    $charsetAttribute = new Swift_Mime_MockHeaderAttribute();
    $charsetAttribute->setReturnValue('getName', 'charset');
    $charsetAttribute->setReturnValue('getValue', 'utf-8');
    $attributes[] = $charsetAttribute;
    
    $attributeSet = new Swift_Mime_MockHeaderAttributeSet();
    $attributeSet->setReturnValue('toArray', $attributes);
    
    $header->setAttributes($attributeSet);
    
    $testAttributes = $header->getAttributes()->toArray();
    
    $this->assertEqual($attributes, $testAttributes);
  }
  
  public function testBasicStructureIsKeyValuePair()
  {
    /* -- RFC 2822, 2.2
    Header fields are lines composed of a field name, followed by a colon
    (":"), followed by a field body, and terminated by CRLF.
    */
    $header = new Swift_Mime_StructuredHeader('Subject', 'Test');
    $this->assertEqual('Subject: Test' . "\r\n", $header->toString());
  }
  
  public function testLongHeadersAreWrappedAtWordBoundary()
  {
    /* -- RFC 2822, 2.2.3
    Each header field is logically a single line of characters comprising
    the field name, the colon, and the field body.  For convenience
    however, and to deal with the 998/78 character limitations per line,
    the field body portion of a header field can be split into a multiple
    line representation; this is called "folding".  The general rule is
    that wherever this standard allows for folding white space (not
    simply WSP characters), a CRLF may be inserted before any WSP.
    */
    
    $value = 'The quick brown fox jumped over the fence he was a very very ' .
      'scary brown fox with a bushy tail';
    $header = new Swift_Mime_StructuredHeader('X-Custom-Header', $value);
    $header->setMaxLineLength(78); //A safe [RFC 2822, 2.2.3] default
    /*
    X-Custom-Header: The quick brown fox jumped over the fence he was a very very
     scary brown fox with a bushy tail
    */
    $this->assertEqual(
      'X-Custom-Header: The quick brown fox jumped over the fence he was a' .
      ' very very' . "\r\n" . //Folding
      ' scary brown fox with a bushy tail' . "\r\n",
      $header->toString(), '%s: The header should have been folded at 78th char'
      );
  }
  
  public function testSpecialCharactersAreEscapedAsQuotedPair()
  {return; //Not sure about this anymore
    $specials = array(
      '(', ')', '<', '>', '[', ']', ':', ';', '@', '\\', ',', '.', '"'
      );
    foreach ($specials as $char)
    {
      $header = new Swift_Mime_StructuredHeader('Test', 'a' . $char . 'b');
      $rendered = $header->toString();
      $this->assertEqual('Test: a\\' . $char . 'b' . "\r\n", $rendered);
    }
  }
  
}
