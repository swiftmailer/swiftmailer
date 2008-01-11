<?php

require_once 'Swift/Mime/Header/MailboxHeader.php';
require_once 'Swift/Mime/HeaderAttribute.php';
require_once 'Swift/Mime/HeaderAttributeSet.php';
require_once 'Swift/Mime/HeaderEncoder.php';

Mock::generate('Swift_Mime_HeaderAttribute', 'Swift_Mime_MockHeaderAttribute');
Mock::generate('Swift_Mime_HeaderAttributeSet',
  'Swift_Mime_MockHeaderAttributeSet'
  );
Mock::generate('Swift_Mime_HeaderEncoder', 'Swift_Mime_MockHeaderEncoder');

class Swift_Mime_Header_MailboxHeaderTest extends UnitTestCase
{
  
  /* -- RFC 2822, 3.6.2 for all tests.
   */
  
  private $_charset = 'utf-8';
  
  /*
  Not sure about my naming now. Maybe:
  
  setMailbox(string[])
  string[] getMailboxes() //no getMailbox() since always returns an array (email=>name)
  setMailboxes(string[])
  string getMailboxAsString()
  string[] getMailboxesAsString()
  string getAddress()
  string[] getAddresses()
  setAddress(string)
  setAddresses(string[])
  string toString()
  
  */
  
  public function testMailboxIsSetForAddress()
  {
    $header = $this->_getHeader('From', 'chris@swiftmailer.org');
    $this->assertEqual('chris@swiftmailer.org', $header->getMailboxString());
  }
  
  public function testMailboxIsRenderedForNameAddress()
  {
    $header = $this->_getHeader('From', array(
      'chris@swiftmailer.org' => 'Chris Corbyn'
      ));
    $this->assertEqual(
      'Chris Corbyn <chris@swiftmailer.org>', $header->getMailboxString()
      );
  }
  
  public function testAddressCanBeReturnedForAddress()
  {
    $header = $this->_getHeader('From', 'chris@swiftmailer.org');
    $this->assertEqual('chris@swiftmailer.org', $header->getAddress());
  }
  
  public function testAddressCanBeReturnedForNameAddress()
  {
    $header = $this->_getHeader('From', array(
      'chris@swiftmailer.org' => 'Chris Corbyn'
      ));
    $this->assertEqual('chris@swiftmailer.org', $header->getAddress());
  }
  
  public function testSpecialCharsInNameAreQuoted()
  {
    $header = $this->_getHeader('From', array(
      'chris@swiftmailer.org' => 'Chris Corbyn, DHE'
      ));
    $this->assertEqual(
      '"Chris Corbyn\, DHE" <chris@swiftmailer.org>', $header->getMailboxString()
      );
  }
  
  public function testSettingMailboxViaSetter()
  {
    $header = $this->_getHeader('From');
    $header->setMailbox(array(
      'chris@swiftmailer.org' => 'Chris Corbyn'
      ));
    $this->assertEqual(
      'Chris Corbyn <chris@swiftmailer.org>', $header->getMailboxString()
      );
  }
  
  public function testGetMailboxesReturnsNameValuePairs()
  {
    $header = $this->_getHeader('From', array(
      'chris@swiftmailer.org' => 'Chris Corbyn, DHE'
      ));
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris Corbyn, DHE'), $header->getMailboxes()
      );
  }
  
  public function testMultipleAddressesCanBeSetAndFetched()
  {
    $header = $this->_getHeader('From', array(
      'chris@swiftmailer.org', 'mark@swiftmailer.org'
      ));
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
  }
  
  public function testMultipleAddressesAsMailboxes()
  {
    $header = $this->_getHeader('From', array(
      'chris@swiftmailer.org', 'mark@swiftmailer.org'
      ));
    $this->assertEqual(
      array('chris@swiftmailer.org'=>null, 'mark@swiftmailer.org'=>null),
      $header->getMailboxes()
      );
  }
  
  public function testMultipleAddressesAsMailboxStrings()
  {
    $header = $this->_getHeader('From', array(
      'chris@swiftmailer.org', 'mark@swiftmailer.org'
      ));
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getMailboxStrings()
      );
  }
  
  public function testGetAddressOnlyReturnsFirstAddress()
  {
    $header = $this->_getHeader('From', array(
      'chris@swiftmailer.org', 'mark@swiftmailer.org'
      ));
    $this->assertEqual('chris@swiftmailer.org', $header->getAddress());
  }
  
  public function testMultipleNamedMailboxesReturnsMultipleAddresses()
  {
    $header = $this->_getHeader('From', array(
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
    $header = $this->_getHeader('From', array(
      'chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'
      ));
    $this->assertEqual(array(
        'chris@swiftmailer.org' => 'Chris Corbyn',
        'mark@swiftmailer.org' => 'Mark Corbyn'
        ),
      $header->getMailboxes()
      );
  }
  
  public function testMultipleMailboxesProducesMultipleMailboxStrings()
  {
    $header = $this->_getHeader('From', array(
      'chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'
      ));
    $this->assertEqual(array(
        'Chris Corbyn <chris@swiftmailer.org>',
        'Mark Corbyn <mark@swiftmailer.org>'
        ),
      $header->getMailboxStrings()
      );
  }
  
  public function testGetMailboxStringReturnsFirstMailboxOnly()
  {
    $header = $this->_getHeader('From', array(
      'chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'
      ));
    $this->assertEqual('Chris Corbyn <chris@swiftmailer.org>',
      $header->getMailboxString()
      );
  }
  
  public function testSetAddressOverwritesAnyMailboxes()
  {
    $header = $this->_getHeader('From');
    $header->setMailboxes(array(
      'chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'
      ));
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'),
      $header->getMailboxes()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
    
    $header->setAddress('mark@swiftmailer.org');
    
    $this->assertEqual(array('mark@swiftmailer.org' => null),
      $header->getMailboxes()
      );
    $this->assertEqual('mark@swiftmailer.org', $header->getAddress());
    $this->assertEqual(array('mark@swiftmailer.org'), $header->getAddresses());
  }
  
  public function testSetAddressesOverwritesAnyMailboxes()
  {
    $header = $this->_getHeader('From');
    $header->setMailboxes(array(
      'chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'
      ));
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'),
      $header->getMailboxes()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
    
    $header->setAddresses(array('chris@swiftmailer.org', 'mark@swiftmailer.org'));
    
    $this->assertEqual(
      array('chris@swiftmailer.org' => null, 'mark@swiftmailer.org' => null),
      $header->getMailboxes()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
  }
  
  public function testNameIsEncodedIfNonAscii()
  {
    $name = 'Chris C' . pack('C', 0x8F) . 'rbyn';
    $encoder = new Swift_Mime_MockHeaderEncoder();
    $encoder->setReturnValue('getName', 'Q');
    $encoder->expectOnce('encodeString', array($name, '*', '*'));
    $encoder->setReturnValue('encodeString', 'Chris_C=8Frbyn');
    
    $header = $this->_getHeader('From', array('chris@swiftmailer.org'=>$name),
      $encoder
      );
    
    $this->assertEqual(
      '=?' . $this->_charset . '?Q?Chris_C=8Frbyn?= <chris@swiftmailer.org>',
      $header->getMailboxString()
      );
  }
  
  public function testEncodingLineLengthCalculations()
  {
    /* -- RFC 2047, 2.
    An 'encoded-word' may not be more than 75 characters long, including
    'charset', 'encoding', 'encoded-text', and delimiters.
    */
    
    $name = 'Chris C' . pack('C', 0x8F) . 'rbyn';
    $encoder = new Swift_Mime_MockHeaderEncoder();
    $encoder->setReturnValue('getName', 'Q');
    //'From: ' = 6, '=?utf-8?Q??=' = 12
    $encoder->expectOnce('encodeString', array($name, 18, 75));
    $encoder->setReturnValue('encodeString', 'Chris_C=8Frbyn');
    
    $header = $this->_getHeader('From', array('chris@swiftmailer.org'=>$name),
      $encoder
      );
    
    $header->getMailboxString();
  }
  
  public function testGetValueReturnsMailboxStringValue()
  {
    $header = $this->_getHeader('From', array(
      'chris@swiftmailer.org' => 'Chris Corbyn'
      ));
    $this->assertEqual(
      'Chris Corbyn <chris@swiftmailer.org>', $header->getValue()
      );
  }
  
  public function testGetValueReturnsMailboxStringValueForMultipleMailboxes()
  {
    $header = $this->_getHeader('From', array(
      'chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'
      ));
    $this->assertEqual(
      'Chris Corbyn <chris@swiftmailer.org>, Mark Corbyn <mark@swiftmailer.org>',
      $header->getValue()
      );
  }
  
  public function testSetValueAcceptsAddrSpec()
  {
    $header = $this->_getHeader('Sender');
    $header->setValue('chris@swiftmailer.org');
    $this->assertEqual('chris@swiftmailer.org', $header->getValue());
    $this->assertEqual('chris@swiftmailer.org', $header->getAddress());
    $this->assertEqual(
      array('chris@swiftmailer.org' => null),
      $header->getMailboxes()
      );
    $this->assertEqual('chris@swiftmailer.org', $header->getMailboxString());
  }
  
  public function testSetValueAcceptsNameAddr()
  {
    $header = $this->_getHeader('Sender');
    $header->setValue('Chris Corbyn <chris@swiftmailer.org>');
    $this->assertEqual('Chris Corbyn <chris@swiftmailer.org>', $header->getValue());
    $this->assertEqual('chris@swiftmailer.org', $header->getAddress());
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris Corbyn'),
      $header->getMailboxes()
      );
    $this->assertEqual('Chris Corbyn <chris@swiftmailer.org>',
      $header->getMailboxString()
      );
  }
  
  //TODO: test toString()
  // test setValue()
  
  // -- Private methods
  
  private function _getHeader($name, $value = null, $encoder = null)
  {
    if (!$encoder)
    {
      $encoder = new Swift_Mime_MockHeaderEncoder();
    }
    return new Swift_Mime_Header_MailboxHeader(
      $name, $value, $this->_charset, $encoder
      );
  }
  
}
