<?php

require_once 'Swift/AbstractSwiftUnitTestCase.php';
require_once 'Swift/Mime/Header/MailboxHeader.php';
require_once 'Swift/Mime/HeaderEncoder.php';

Mock::generate('Swift_Mime_HeaderEncoder', 'Swift_Mime_MockHeaderEncoder');

class Swift_Mime_Header_MailboxHeaderTest
  extends Swift_AbstractSwiftUnitTestCase
{
  
  /* -- RFC 2822, 3.6.2 for all tests.
   */
  
  private $_charset = 'utf-8';
  
  public function testMailboxIsSetForAddress()
  {
    $header = $this->_getHeader('From', new Swift_Mime_MockHeaderEncoder());
    $header->setAddresses('chris@swiftmailer.org');
    $this->assertEqual(array('chris@swiftmailer.org'),
      $header->getNameAddressStrings()
      );
  }
  
  public function testMailboxIsRenderedForNameAddress()
  {
    $header = $this->_getHeader('From', new Swift_Mime_MockHeaderEncoder());
    $header->setNameAddresses(array('chris@swiftmailer.org' => 'Chris Corbyn'));
    $this->assertEqual(
      array('Chris Corbyn <chris@swiftmailer.org>'), $header->getNameAddressStrings()
      );
  }
  
  public function testAddressCanBeReturnedForAddress()
  {
    $header = $this->_getHeader('From', new Swift_Mime_MockHeaderEncoder());
    $header->setAddresses('chris@swiftmailer.org');
    $this->assertEqual(array('chris@swiftmailer.org'), $header->getAddresses());
  }
  
  public function testAddressCanBeReturnedForNameAddress()
  {
    $header = $this->_getHeader('From', new Swift_Mime_MockHeaderEncoder());
    $header->setNameAddresses(array('chris@swiftmailer.org' => 'Chris Corbyn'));
    $this->assertEqual(array('chris@swiftmailer.org'), $header->getAddresses());
  }
  
  public function testSpecialCharsInNameAreQuoted()
  {
    $header = $this->_getHeader('From', new Swift_Mime_MockHeaderEncoder());
    $header->setNameAddresses(array(
      'chris@swiftmailer.org' => 'Chris Corbyn, DHE'
      ));
    $this->assertEqual(
      array('"Chris Corbyn\, DHE" <chris@swiftmailer.org>'),
      $header->getNameAddressStrings()
      );
  }
  
  public function testGetMailboxesReturnsNameValuePairs()
  {
    $header = $this->_getHeader('From', new Swift_Mime_MockHeaderEncoder());
    $header->setNameAddresses(array(
      'chris@swiftmailer.org' => 'Chris Corbyn, DHE'
      ));
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris Corbyn, DHE'), $header->getNameAddresses()
      );
  }
  
  public function testMultipleAddressesCanBeSetAndFetched()
  {
    $header = $this->_getHeader('From', new Swift_Mime_MockHeaderEncoder());
    $header->setAddresses(array(
      'chris@swiftmailer.org', 'mark@swiftmailer.org'
      ));
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
  }
  
  public function testMultipleAddressesAsMailboxes()
  {
    $header = $this->_getHeader('From', new Swift_Mime_MockHeaderEncoder());
    $header->setAddresses(array(
      'chris@swiftmailer.org', 'mark@swiftmailer.org'
      ));
    $this->assertEqual(
      array('chris@swiftmailer.org'=>null, 'mark@swiftmailer.org'=>null),
      $header->getNameAddresses()
      );
  }
  
  public function testMultipleAddressesAsMailboxStrings()
  {
    $header = $this->_getHeader('From', new Swift_Mime_MockHeaderEncoder());
    $header->setAddresses(array(
      'chris@swiftmailer.org', 'mark@swiftmailer.org'
      ));
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getNameAddressStrings()
      );
  }
  
  public function testMultipleNamedMailboxesReturnsMultipleAddresses()
  {
    $header = $this->_getHeader('From', new Swift_Mime_MockHeaderEncoder());
    $header->setNameAddresses(array(
      'chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'
      ));
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
  }
  
  public function testMultipleNamedMailboxesReturnsMultipleMailboxes()
  {
    $header = $this->_getHeader('From', new Swift_Mime_MockHeaderEncoder());
    $header->setNameAddresses(array(
      'chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'
      ));
    $this->assertEqual(array(
        'chris@swiftmailer.org' => 'Chris Corbyn',
        'mark@swiftmailer.org' => 'Mark Corbyn'
        ),
      $header->getNameAddresses()
      );
  }
  
  public function testMultipleMailboxesProducesMultipleMailboxStrings()
  {
    $header = $this->_getHeader('From', new Swift_Mime_MockHeaderEncoder());
    $header->setNameAddresses(array(
      'chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'
      ));
    $this->assertEqual(array(
        'Chris Corbyn <chris@swiftmailer.org>',
        'Mark Corbyn <mark@swiftmailer.org>'
        ),
      $header->getNameAddressStrings()
      );
  }
  
  public function testSetAddressesOverwritesAnyMailboxes()
  {
    $header = $this->_getHeader('From', new Swift_Mime_MockHeaderEncoder());
    $header->setNameAddresses(array(
      'chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'
      ));
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'),
      $header->getNameAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
    
    $header->setAddresses(array('chris@swiftmailer.org', 'mark@swiftmailer.org'));
    
    $this->assertEqual(
      array('chris@swiftmailer.org' => null, 'mark@swiftmailer.org' => null),
      $header->getNameAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
  }
  
  public function testNameIsEncodedIfNonAscii()
  {
    $name = 'C' . pack('C', 0x8F) . 'rbyn';
    $encoder = new Swift_Mime_MockHeaderEncoder();
    $encoder->setReturnValue('getName', 'Q');
    $encoder->expectOnce('encodeString', array($name, '*', '*'));
    $encoder->setReturnValue('encodeString', 'C=8Frbyn');
    
    $header = $this->_getHeader('From', $encoder);
    $header->setNameAddresses(array('chris@swiftmailer.org'=>'Chris ' . $name));
    
    $this->assertEqual(
      'Chris =?' . $this->_charset . '?Q?C=8Frbyn?= <chris@swiftmailer.org>',
      array_shift($header->getNameAddressStrings())
      );
  }
  
  public function testEncodingLineLengthCalculations()
  {
    /* -- RFC 2047, 2.
    An 'encoded-word' may not be more than 75 characters long, including
    'charset', 'encoding', 'encoded-text', and delimiters.
    */
    
    $name = 'C' . pack('C', 0x8F) . 'rbyn';
    $encoder = new Swift_Mime_MockHeaderEncoder();
    $encoder->setReturnValue('getName', 'Q');
    $encoder->expectOnce('encodeString', array($name, 18, 75));
    $encoder->setReturnValue('encodeString', 'C=8Frbyn');
    
    $header = $this->_getHeader('From', $encoder);
    $header->setNameAddresses(array('chris@swiftmailer.org'=>'Chris ' . $name));
    
    $header->getNameAddressStrings();
  }
  
  public function testGetValueReturnsMailboxStringValue()
  {
    $header = $this->_getHeader('From', new Swift_Mime_MockHeaderEncoder());
    $header->setNameAddresses(array(
      'chris@swiftmailer.org' => 'Chris Corbyn'
      ));
    $this->assertEqual(
      'Chris Corbyn <chris@swiftmailer.org>', $header->getFieldBody()
      );
  }
  
  public function testGetValueReturnsMailboxStringValueForMultipleMailboxes()
  {
    $header = $this->_getHeader('From', new Swift_Mime_MockHeaderEncoder());
    $header->setNameAddresses(array(
      'chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'
      ));
    $this->assertEqual(
      'Chris Corbyn <chris@swiftmailer.org>, Mark Corbyn <mark@swiftmailer.org>',
      $header->getFieldBody()
      );
  }
  
  public function testRemoveAddressesWithSingleValue()
  {
    $header = $this->_getHeader('From', new Swift_Mime_MockHeaderEncoder());
    $header->setNameAddresses(array(
      'chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'
      ));
    $header->removeAddresses('chris@swiftmailer.org');
    $this->assertEqual(array('mark@swiftmailer.org'),
      $header->getAddresses()
      );
  }
  
  public function testRemoveAddressesWithList()
  {
    $header = $this->_getHeader('From', new Swift_Mime_MockHeaderEncoder());
    $header->setNameAddresses(array(
      'chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'
      ));
    $header->removeAddresses(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org')
      );
    $this->assertEqual(array(), $header->getAddresses());
  }
  
  public function testToString()
  {
    $header = $this->_getHeader('From', new Swift_Mime_MockHeaderEncoder());
    $header->setNameAddresses(array(
      'chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'
      ));
    $this->assertEqual(
        'From: Chris Corbyn <chris@swiftmailer.org>, ' .
        'Mark Corbyn <mark@swiftmailer.org>' . "\r\n",
      $header->toString()
      );
  }
  
  //TODO: test toString()
  
  // -- Private methods
  
  private function _getHeader($name, $encoder)
  {
    $header = new Swift_Mime_Header_MailboxHeader($name, $encoder);
    $header->setCharset($this->_charset);
    return $header;
  }
  
}
