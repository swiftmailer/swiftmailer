<?php

require_once 'Swift/Mime/MimeEntity.php';
require_once 'Swift/Mime/MimePart.php';
require_once 'Swift/AbstractSwiftUnitTestCase.php';
require_once 'Swift/Mime/ContentEncoder.php';
require_once 'Swift/Mime/Header.php';
require_once 'Swift/Mime/FieldChangeObserver.php';
require_once 'Swift/InputByteStream.php';
require_once 'Swift/OutputByteStream.php';

Mock::generate('Swift_Mime_ContentEncoder', 'Swift_Mime_MockContentEncoder');
Mock::generate('Swift_Mime_Header', 'Swift_Mime_MockHeader');
Mock::generate('Swift_Mime_FieldChangeObserver',
  'Swift_Mime_MockFieldChangeObserver'
  );
Mock::generate('Swift_InputByteStream', 'Swift_MockInputByteStream');
Mock::generate('Swift_OutputByteStream', 'Swift_MockOutputByteStream');

class Swift_Mime_MimePartTest extends Swift_AbstractSwiftUnitTestCase
{
  private $_encoder;
  
  public function setUp()
  {
    $this->_encoder = new Swift_Mime_MockContentEncoder();
    $this->_encoder->setReturnValue('getName', 'quoted-printable');
  }
  
  public function testNestingLevelIsSubpart()
  {
    $part = $this->_createMimePart(array(), $this->_encoder);
    $this->assertEqual(
      Swift_Mime_MimeEntity::LEVEL_SUBPART, $part->getNestingLevel()
      );
  }
  
  public function testCharsetCanBeSetAndFetched()
  {
    /* -- RFC 2046, 4.1.2.
    A critical parameter that may be specified in the Content-Type field
    for "text/plain" data is the character set.  This is specified with a
    "charset" parameter, as in:

     Content-type: text/plain; charset=iso-8859-1

    Unlike some other parameter values, the values of the charset
    parameter are NOT case sensitive.  The default character set, which
    must be assumed in the absence of a charset parameter, is US-ASCII.
    */
    
    $part = $this->_createMimePart(array(), $this->_encoder);
    $part->setCharset('ucs2');
    $this->assertEqual('ucs2', $part->getCharset());
  }
  
  public function testSettingCharsetNotifiesFieldChangeObservers()
  {
    $observer1 = new Swift_Mime_MockFieldChangeObserver();
    $observer1->expectOnce('fieldChanged', array('charset', 'utf-8'));
    $observer2 = new Swift_Mime_MockFieldChangeObserver();
    $observer2->expectOnce('fieldChanged', array('charset', 'utf-8'));
    
    $part = $this->_createMimePart(array(), $this->_encoder);
    
    $part->registerFieldChangeObserver($observer1);
    $part->registerFieldChangeObserver($observer2);
    
    $part->setCharset('utf-8');
  }
  
  public function testFormatCanBeSetAndFetched()
  {
    /* -- RFC 3676.
     */
    
    $part = $this->_createMimePart(array(), $this->_encoder);
    $part->setFormat('flowed'); //'fixed' is valid too
    $this->assertEqual('flowed', $part->getFormat());
  }
  
  public function testSettingFormatNotifiesFieldChangeObservers()
  {
    $observer1 = new Swift_Mime_MockFieldChangeObserver();
    $observer1->expectOnce('fieldChanged', array('format', 'fixed'));
    $observer2 = new Swift_Mime_MockFieldChangeObserver();
    $observer2->expectOnce('fieldChanged', array('format', 'fixed'));
    
    $part = $this->_createMimePart(array(), $this->_encoder);
    
    $part->registerFieldChangeObserver($observer1);
    $part->registerFieldChangeObserver($observer2);
    
    $part->setFormat('fixed');
  }
  
  public function testDelSpCanBeSetAndFetched()
  {
    /* -- RFC 3676.
     */
    
    $part = $this->_createMimePart(array(), $this->_encoder);
    $part->setDelSp(true); //false is valid too
    $this->assertTrue($part->getDelSp());
  }
  
  public function testSettingDelSpNotifiesFieldChangeObservers()
  {
    $observer1 = new Swift_Mime_MockFieldChangeObserver();
    $observer1->expectOnce('fieldChanged', array('delsp', true));
    $observer2 = new Swift_Mime_MockFieldChangeObserver();
    $observer2->expectOnce('fieldChanged', array('delsp', true));
    
    $part = $this->_createMimePart(array(), $this->_encoder);
    
    $part->registerFieldChangeObserver($observer1);
    $part->registerFieldChangeObserver($observer2);
    
    $part->setDelSp(true);
  }
  
  public function testCanonicalEncodingIsUsedOnStrings()
  {
    //text parts should be presented in the canonical form, and any translation
    // should be handled by the Transport
    
    $part = $this->_createMimePart(array(), $this->_encoder);
    $this->_encoder->expectOnce('canonicEncodeString');
    $part->setBodyAsString('foo');
    $part->toString();
  }
  
  public function testCanonicalEncodingIsUsedOnByteStreams()
  {
    //text parts should be presented in the canonical form, and any translation
    // should be handled by the Transport
    
    $part = $this->_createMimePart(array(), $this->_encoder);
    $this->_encoder->expectOnce('canonicEncodeByteStream');
    $part->setBodyAsByteStream(new Swift_MockOutputByteStream());
    $part->toByteStream(new Swift_MockInputByteStream());
  }
  
  public function testFluidInterface()
  {
    $part = $this->_createMimePart(array(), $this->_encoder);
    $ref = $part
      ->setContentType('text/plain')
      ->setEncoder($this->_encoder)
      ->setId('foo@bar')
      ->setDescription('my description')
      ->setMaxLineLength(998)
      ->setBodyAsString('xx')
      ->setNestingLevel(10)
      ->setBoundary('xyz')
      ->setChildren(array())
      ->setHeaders(array())
      ->setCharset('iso-8859-1')
      ->setFormat('flowed')
      ->setDelSp(false)
      ;
    
    $this->assertReference($part, $ref);
  }
  
  public function testEncoderFieldChangeUpdatesEncoder()
  {
    $part = $this->_createMimePart(array(), $this->_encoder);
    $this->assertReference($this->_encoder, $part->getEncoder());
    $encoder = new Swift_Mime_MockContentEncoder();
    $encoder->setReturnValue('getName', '8bit');
    $part->fieldChanged('encoder', $encoder);
    $this->assertReference($encoder, $part->getEncoder());
  }
  
  // -- Private helpers
  
  private function _createMimePart($headers, $encoder)
  {
    return new Swift_Mime_MimePart($headers, $encoder);
  }
  
}
