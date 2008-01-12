<?php

require_once 'Swift/AbstractSwiftUnitTestCase.php';
require_once 'Swift/Mime/Header/MailboxHeader.php';
require_once 'Swift/Mime/HeaderAttribute.php';
require_once 'Swift/Mime/HeaderAttributeSet.php';
require_once 'Swift/Mime/HeaderEncoder.php';

Mock::generate('Swift_Mime_HeaderAttribute', 'Swift_Mime_MockHeaderAttribute');
Mock::generate('Swift_Mime_HeaderAttributeSet',
  'Swift_Mime_MockHeaderAttributeSet'
  );
Mock::generate('Swift_Mime_HeaderEncoder', 'Swift_Mime_MockHeaderEncoder');

class Swift_Mime_Header_MailboxHeaderTest
  extends Swift_AbstractSwiftUnitTestCase
{
  
  /* -- RFC 2822, 3.6.2 for all tests.
   */
  
  private $_charset = 'utf-8';
  
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
  
  public function testSetValueAcceptsAddressList()
  {
    $header = $this->_getHeader('From');
    $header->setValue(
      'chris@swiftmailer.org,mark@swiftmailer.org'
      );
    $this->assertEqual(
      'chris@swiftmailer.org,mark@swiftmailer.org',
      $header->getValue()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org' => null,
      'mark@swiftmailer.org' => null),
      $header->getMailboxes()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getMailboxStrings()
      );
  }
  
  public function testSetValueAcceptsNameAddressList()
  {
    $header = $this->_getHeader('From');
    $header->setValue(
      'Chris Corbyn <chris@swiftmailer.org>,' . "\r\n " .
      'Mark Corbyn <mark@swiftmailer.org>'
      );
    $this->assertEqual(
      'Chris Corbyn <chris@swiftmailer.org>,' . "\r\n " .
      'Mark Corbyn <mark@swiftmailer.org>',
      $header->getValue()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'),
      $header->getMailboxes()
      );
    $this->assertEqual(
      array('Chris Corbyn <chris@swiftmailer.org>',
      'Mark Corbyn <mark@swiftmailer.org>'),
      $header->getMailboxStrings()
      );
  }
  
  public function testSetValueWithAngleBrackets()
  {
    $header = $this->_getHeader('From');
    $header->setValue(
      '<chris@swiftmailer.org>, <mark@swiftmailer.org>'
      );
    $this->assertEqual(
      '<chris@swiftmailer.org>, <mark@swiftmailer.org>',
      $header->getValue()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org' => null,
      'mark@swiftmailer.org' => null),
      $header->getMailboxes()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getMailboxStrings(),
      '%s: getMailboxStrings() should return the simplest representation.'
      );
  }
  
  public function testSetValueWithComments()
  {
    $header = $this->_getHeader('From');
    $header->setValue(
      'Chris Corbyn (Mail Guru) <chris@swiftmailer.org>,' . "\r\n " .
      'Mark Corbyn <mark@swiftmailer.org> (Brother)'
      );
    $this->assertEqual(
      'Chris Corbyn (Mail Guru) <chris@swiftmailer.org>,' . "\r\n " .
      'Mark Corbyn <mark@swiftmailer.org> (Brother)',
      $header->getValue()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'),
      $header->getMailboxes()
      );
    $this->assertEqual(
      array('Chris Corbyn <chris@swiftmailer.org>',
      'Mark Corbyn <mark@swiftmailer.org>'),
      $header->getMailboxStrings()
      );
  }
  
  public function testSetValueWithEncodedComments()
  {
    $header = $this->_getHeader('From');
    $header->setValue(
      'Chris Corbyn (=?utf-8?Q?Mail_Guru?=) <chris@swiftmailer.org>,' . "\r\n " .
      'Mark Corbyn <mark@swiftmailer.org> (=?utf-8?Q?Lil_Brother?=)'
      );
    $this->assertEqual(
      'Chris Corbyn (=?utf-8?Q?Mail_Guru?=) <chris@swiftmailer.org>,' . "\r\n " .
      'Mark Corbyn <mark@swiftmailer.org> (=?utf-8?Q?Lil_Brother?=)',
      $header->getValue()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'),
      $header->getMailboxes()
      );
    $this->assertEqual(
      array('Chris Corbyn <chris@swiftmailer.org>',
      'Mark Corbyn <mark@swiftmailer.org>'),
      $header->getMailboxStrings()
      );
  }
  
  public function testSetValueWithQuotedPairs()
  {
    $header = $this->_getHeader('From');
    $header->setValue(
      '"Chris Corbyn\\, DHE" <chris@swiftmailer.org>,' . "\r\n " .
      '"Mark Corbyn\\, BSc Comp\\. Sci\\." <mark@swiftmailer.org>'
      );
    
    $this->assertEqual(
      '"Chris Corbyn\\, DHE" <chris@swiftmailer.org>,' . "\r\n " .
      '"Mark Corbyn\\, BSc Comp\\. Sci\\." <mark@swiftmailer.org>',
      $header->getValue()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris Corbyn, DHE',
      'mark@swiftmailer.org' => 'Mark Corbyn, BSc Comp. Sci.'),
      $header->getMailboxes()
      );
    $this->assertEqual(
      array('"Chris Corbyn\\, DHE" <chris@swiftmailer.org>',
      '"Mark Corbyn\\, BSc Comp\\. Sci\\." <mark@swiftmailer.org>'),
      $header->getMailboxStrings()
      );
  }
  
  public function testSetValueWithQEncodedWords()
  {
    $encoder = new Swift_Mime_MockHeaderEncoder();
    $encoder->setReturnValue('getName', 'Q');
    $encoder->expectOnce('encodeString', array(
      new Swift_IdenticalBinaryExpectation(
        'Chris C' . pack('C', 0x8F) . 'rbyn Mail Guru'
        ),
      '*',
      '*'
      ));
    $encoder->setReturnValue('encodeString', 'Chris_C=8Frbyn_Mail_Guru');
    
    $header = $this->_getHeader('From', null, $encoder);
    $header->setValue(
      '=?utf-8?Q?Chris_C=8Frbyn?=' . "\r\n " . 
      ' =?utf-8?Q?_Mail_Guru?= <chris@swiftmailer.org>,' . "\r\n " .
      'Mark Corbyn <mark@swiftmailer.org>'
      );
    
    $this->assertEqual(
      '=?utf-8?Q?Chris_C=8Frbyn?=' . "\r\n " . 
      ' =?utf-8?Q?_Mail_Guru?= <chris@swiftmailer.org>,' . "\r\n " .
      'Mark Corbyn <mark@swiftmailer.org>',
      $header->getValue()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris C'. pack('C', 0x8F) . 'rbyn Mail Guru',
      'mark@swiftmailer.org' => 'Mark Corbyn'),
      $header->getMailboxes(),
      '%s: Encoded words should be decoded'
      );
    $this->assertEqual(
      array('=?utf-8?Q?Chris_C=8Frbyn_Mail_Guru?= <chris@swiftmailer.org>',
      'Mark Corbyn <mark@swiftmailer.org>'),
      $header->getMailboxStrings()
      );
  }
  
  public function testSetValueWithBEncodedWords()
  {
    $encoder = new Swift_Mime_MockHeaderEncoder();
    $encoder->setReturnValue('getName', 'B');
    $encoder->expectOnce('encodeString', array(
      new Swift_IdenticalBinaryExpectation(
        'Chris C' . pack('C', 0x8F) . 'rbyn Mail Guru'
        ),
      '*',
      '*'
      ));
    $encoder->setReturnValue('encodeString', 'Q2hyaXMgQ49yYnluIE1haWwgR3VydQ==');
    
    $header = $this->_getHeader('From', null, $encoder);
    $header->setValue(
      '=?utf-8?B?Q2hyaXMgQ49y?=' . "\r\n " . 
      ' =?utf-8?B?YnluIE1haWwgR3VydQ==?= <chris@swiftmailer.org>,' . "\r\n " .
      'Mark Corbyn <mark@swiftmailer.org>'
      );
    
    $this->assertEqual(
      '=?utf-8?B?Q2hyaXMgQ49y?=' . "\r\n " . 
      ' =?utf-8?B?YnluIE1haWwgR3VydQ==?= <chris@swiftmailer.org>,' . "\r\n " .
      'Mark Corbyn <mark@swiftmailer.org>',
      $header->getValue()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris C'. pack('C', 0x8F) . 'rbyn Mail Guru',
      'mark@swiftmailer.org' => 'Mark Corbyn'),
      $header->getMailboxes(),
      '%s: Encoded words should be decoded'
      );
    $this->assertEqual(
      array('=?utf-8?B?Q2hyaXMgQ49yYnluIE1haWwgR3VydQ==?= <chris@swiftmailer.org>',
      'Mark Corbyn <mark@swiftmailer.org>'),
      $header->getMailboxStrings()
      );
  }
  
  public function testToString()
  {
    $header = $this->_getHeader('From', array(
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
