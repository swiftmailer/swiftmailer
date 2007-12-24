<?php

require_once 'Swift/Mime/UnstructuredHeader.php';
require_once 'Swift/Mime/HeaderAttribute.php';
require_once 'Swift/Mime/HeaderAttributeSet.php';

Mock::generate('Swift_Mime_HeaderAttribute', 'Swift_Mime_MockHeaderAttribute');
Mock::generate('Swift_Mime_HeaderAttributeSet',
  'Swift_Mime_MockHeaderAttributeSet'
  );

class Swift_Mime_UnstructuredHeaderTest extends UnitTestCase
{
  
  public function testGetNameReturnsNameVerbatim()
  {
    $header = new Swift_Mime_UnstructuredHeader('Subject', 'Test');
    $this->assertEqual('Subject', $header->getName());
  }
  
  public function testGetValueReturnsValueVerbatim()
  {
    $header = new Swift_Mime_UnstructuredHeader('Subject', 'Test');
    $this->assertEqual('Test', $header->getValue());
  }
  
  public function testValueCanBeSet()
  {
    $header = new Swift_Mime_UnstructuredHeader('Subject', '');
    $header->setValue('Something');
    $this->assertEqual('Something', $header->getValue());
  }
  
  public function testAttributesCanBeSet()
  {
    $header = new Swift_Mime_UnstructuredHeader('Content-Type', 'text/html');
    
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
    $header = new Swift_Mime_UnstructuredHeader('Subject', 'Test');
    $this->assertEqual('Subject: Test' . "\r\n", $header->toString());
  }
  
  public function testLongHeadersAreFoldedAtWordBoundary()
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
    
    $value = 'The quick brown fox jumped over the fence, he was a very very ' .
      'scary brown fox with a bushy tail';
    $header = new Swift_Mime_UnstructuredHeader('X-Custom-Header', $value);
    $header->setMaxLineLength(78); //A safe [RFC 2822, 2.2.3] default
    /*
    X-Custom-Header: The quick brown fox jumped over the fence, he was a very very
     scary brown fox with a bushy tail
    */
    $this->assertEqual(
      'X-Custom-Header: The quick brown fox jumped over the fence, he was a' .
      ' very very' . "\r\n" . //Folding
      ' scary brown fox with a bushy tail' . "\r\n",
      $header->toString(), '%s: The header should have been folded at 78th char'
      );
  }
  
  public function testAttributesAreAppended()
  {
    $attributes = array();
    $att1 = new Swift_Mime_MockHeaderAttribute();
    $att1->setReturnValue('toString', 'charset=utf-8');
    $attributes[] = $att1;
    $att2 = new Swift_Mime_MockHeaderAttribute();
    $att2->setReturnValue('toString', 'lang=en');
    $attributes[] = $att2;
    
    $attSet = new Swift_Mime_MockHeaderAttributeSet();
    $attSet->setReturnValue('toArray', $attributes);
    
    $header = new Swift_Mime_UnstructuredHeader('Content-Type', 'text/plain');
    $header->setAttributes($attSet);
    
    $this->assertEqual(
      'Content-Type: text/plain; charset=utf-8; lang=en' . "\r\n",
      $header->toString()
      );
  }
  
  public function testAttributesAreFoldedInLongHeaders()
  {
    $attributes = array();
    $att1 = new Swift_Mime_MockHeaderAttribute();
    $att1->setReturnValue('toString',
      'attrib*0="first line of attrib";' . "\r\n" .
      'attrib*1="second line of attrib"'
      );
    $attributes[] = $att1;
    $att2 = new Swift_Mime_MockHeaderAttribute();
    $att2->setReturnValue('toString', 'test=nothing');
    $attributes[] = $att2;
    
    $attSet = new Swift_Mime_MockHeaderAttributeSet();
    $attSet->setReturnValue('toArray', $attributes);
    
    $header = new Swift_Mime_UnstructuredHeader('X-Anything-Header',
      'something with a fairly long value'
      );
    $header->setAttributes($attSet);
    $header->setMaxLineLength(78);
    
    $this->assertEqual(
      'X-Anything-Header: something with a fairly long value;' . "\r\n" .
      ' attrib*0="first line of attrib";' . "\r\n" .
      ' attrib*1="second line of attrib"; test=nothing' . "\r\n",
      $header->toString()
      );
  }
  
}
