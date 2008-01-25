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
    $header = $this->_getHeader('From', 'chris@swiftmailer.org');
    $this->assertEqual(array('chris@swiftmailer.org'),
      $header->getNameAddressStrings()
      );
  }
  
  public function testMailboxIsRenderedForNameAddress()
  {
    $header = $this->_getHeader('From', array(
      'chris@swiftmailer.org' => 'Chris Corbyn'
      ));
    $this->assertEqual(
      array('Chris Corbyn <chris@swiftmailer.org>'), $header->getNameAddressStrings()
      );
  }
  
  public function testAddressCanBeReturnedForAddress()
  {
    $header = $this->_getHeader('From', 'chris@swiftmailer.org');
    $this->assertEqual(array('chris@swiftmailer.org'), $header->getAddresses());
  }
  
  public function testAddressCanBeReturnedForNameAddress()
  {
    $header = $this->_getHeader('From', array(
      'chris@swiftmailer.org' => 'Chris Corbyn'
      ));
    $this->assertEqual(array('chris@swiftmailer.org'), $header->getAddresses());
  }
  
  public function testSpecialCharsInNameAreQuoted()
  {
    $header = $this->_getHeader('From', array(
      'chris@swiftmailer.org' => 'Chris Corbyn, DHE'
      ));
    $this->assertEqual(
      array('"Chris Corbyn\, DHE" <chris@swiftmailer.org>'),
      $header->getNameAddressStrings()
      );
  }
  
  public function testSettingMailboxViaSetter()
  {
    $header = $this->_getHeader('From');
    $header->setNameAddresses(array(
      'chris@swiftmailer.org' => 'Chris Corbyn'
      ));
    $this->assertEqual(
      array('Chris Corbyn <chris@swiftmailer.org>'),
      $header->getNameAddressStrings()
      );
  }
  
  public function testGetMailboxesReturnsNameValuePairs()
  {
    $header = $this->_getHeader('From', array(
      'chris@swiftmailer.org' => 'Chris Corbyn, DHE'
      ));
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris Corbyn, DHE'), $header->getNameAddresses()
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
      $header->getNameAddresses()
      );
  }
  
  public function testMultipleAddressesAsMailboxStrings()
  {
    $header = $this->_getHeader('From', array(
      'chris@swiftmailer.org', 'mark@swiftmailer.org'
      ));
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getNameAddressStrings()
      );
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
      $header->getNameAddresses()
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
      $header->getNameAddressStrings()
      );
  }
  
  public function testSetAddressesOverwritesAnyMailboxes()
  {
    $header = $this->_getHeader('From');
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
    
    $header = $this->_getHeader('From',
      array('chris@swiftmailer.org'=>'Chris ' . $name),
      $encoder
      );
    
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
    
    $header = $this->_getHeader('From',
      array('chris@swiftmailer.org'=>'Chris ' . $name),
      $encoder
      );
    
    $header->getNameAddressStrings();
  }
  
  public function testGetValueReturnsMailboxStringValue()
  {
    $header = $this->_getHeader('From', array(
      'chris@swiftmailer.org' => 'Chris Corbyn'
      ));
    $this->assertEqual(
      'Chris Corbyn <chris@swiftmailer.org>', $header->getPreparedValue()
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
      $header->getPreparedValue()
      );
  }
  
  public function testSetValueAcceptsAddrSpec()
  {
    $header = $this->_getHeader('Sender');
    $header->setPreparedValue('chris@swiftmailer.org');
    $this->assertEqual('chris@swiftmailer.org', $header->getPreparedValue());
    $this->assertEqual('chris@swiftmailer.org',
      array_shift($header->getAddresses())
      );
    $this->assertEqual(
      array('chris@swiftmailer.org' => null),
      $header->getNameAddresses()
      );
    $this->assertEqual('chris@swiftmailer.org',
      array_shift($header->getNameAddressStrings())
      );
  }
  
  public function testSetValueAcceptsNameAddr()
  {
    $header = $this->_getHeader('Sender');
    $header->setPreparedValue('Chris Corbyn <chris@swiftmailer.org>');
    $this->assertEqual('Chris Corbyn <chris@swiftmailer.org>', $header->getPreparedValue());
    $this->assertEqual('chris@swiftmailer.org',
      array_shift($header->getAddresses())
      );
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris Corbyn'),
      $header->getNameAddresses()
      );
    $this->assertEqual('Chris Corbyn <chris@swiftmailer.org>',
      array_shift($header->getNameAddressStrings())
      );
  }
  
  public function testSetValueAcceptsAddressList()
  {
    $header = $this->_getHeader('From');
    $header->setPreparedValue(
      'chris@swiftmailer.org,mark@swiftmailer.org'
      );
    $this->assertEqual(
      'chris@swiftmailer.org,mark@swiftmailer.org',
      $header->getPreparedValue()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org' => null,
      'mark@swiftmailer.org' => null),
      $header->getNameAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getNameAddressStrings()
      );
  }
  
  public function testSetValueAcceptsNameAddressList()
  {
    $header = $this->_getHeader('From');
    $header->setPreparedValue(
      'Chris Corbyn <chris@swiftmailer.org>,' . "\r\n " .
      'Mark Corbyn <mark@swiftmailer.org>'
      );
    $this->assertEqual(
      'Chris Corbyn <chris@swiftmailer.org>,' . "\r\n " .
      'Mark Corbyn <mark@swiftmailer.org>',
      $header->getPreparedValue()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'),
      $header->getNameAddresses()
      );
    $this->assertEqual(
      array('Chris Corbyn <chris@swiftmailer.org>',
      'Mark Corbyn <mark@swiftmailer.org>'),
      $header->getNameAddressStrings()
      );
  }
  
  public function testSetValueWithAngleBrackets()
  {
    $header = $this->_getHeader('From');
    $header->setPreparedValue(
      '<chris@swiftmailer.org>, <mark@swiftmailer.org>'
      );
    $this->assertEqual(
      '<chris@swiftmailer.org>, <mark@swiftmailer.org>',
      $header->getPreparedValue()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org' => null,
      'mark@swiftmailer.org' => null),
      $header->getNameAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getNameAddressStrings(),
      '%s: getNameAddressStrings() should return the simplest representation.'
      );
  }
  
  public function testSetValueWithComments()
  {
    $header = $this->_getHeader('From');
    $header->setPreparedValue(
      'Chris Corbyn (Mail Guru) <chris@swiftmailer.org>,' . "\r\n " .
      'Mark Corbyn <mark@swiftmailer.org> (Brother)'
      );
    $this->assertEqual(
      'Chris Corbyn (Mail Guru) <chris@swiftmailer.org>,' . "\r\n " .
      'Mark Corbyn <mark@swiftmailer.org> (Brother)',
      $header->getPreparedValue()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'),
      $header->getNameAddresses()
      );
    $this->assertEqual(
      array('Chris Corbyn <chris@swiftmailer.org>',
      'Mark Corbyn <mark@swiftmailer.org>'),
      $header->getNameAddressStrings()
      );
  }
  
  public function testSetValueWithEncodedComments()
  {
    $header = $this->_getHeader('From');
    $header->setPreparedValue(
      'Chris Corbyn (=?utf-8?Q?Mail_Guru?=) <chris@swiftmailer.org>,' . "\r\n " .
      'Mark Corbyn <mark@swiftmailer.org> (=?utf-8?Q?Lil_Brother?=)'
      );
    $this->assertEqual(
      'Chris Corbyn (=?utf-8?Q?Mail_Guru?=) <chris@swiftmailer.org>,' . "\r\n " .
      'Mark Corbyn <mark@swiftmailer.org> (=?utf-8?Q?Lil_Brother?=)',
      $header->getPreparedValue()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris Corbyn',
      'mark@swiftmailer.org' => 'Mark Corbyn'),
      $header->getNameAddresses()
      );
    $this->assertEqual(
      array('Chris Corbyn <chris@swiftmailer.org>',
      'Mark Corbyn <mark@swiftmailer.org>'),
      $header->getNameAddressStrings()
      );
  }
  
  public function testSetValueWithQuotedPairs()
  {
    $header = $this->_getHeader('From');
    $header->setPreparedValue(
      '"Chris Corbyn\\, DHE" <chris@swiftmailer.org>,' . "\r\n " .
      '"Mark Corbyn\\, BSc Comp\\. Sci\\." <mark@swiftmailer.org>'
      );
    
    $this->assertEqual(
      '"Chris Corbyn\\, DHE" <chris@swiftmailer.org>,' . "\r\n " .
      '"Mark Corbyn\\, BSc Comp\\. Sci\\." <mark@swiftmailer.org>',
      $header->getPreparedValue()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris Corbyn, DHE',
      'mark@swiftmailer.org' => 'Mark Corbyn, BSc Comp. Sci.'),
      $header->getNameAddresses()
      );
    $this->assertEqual(
      array('"Chris Corbyn\\, DHE" <chris@swiftmailer.org>',
      '"Mark Corbyn\\, BSc Comp\\. Sci\\." <mark@swiftmailer.org>'),
      $header->getNameAddressStrings()
      );
  }
  
  public function testSetValueWithCommasInQuotedString()
  {
    $header = $this->_getHeader('From');
    $header->setPreparedValue(
      '"Chris Corbyn, DHE" <chris@swiftmailer.org>,' . "\r\n " .
      '"Mark Corbyn, BSc Comp. Sci." <mark@swiftmailer.org>'
      );
    
    $this->assertEqual(
      '"Chris Corbyn, DHE" <chris@swiftmailer.org>,' . "\r\n " .
      '"Mark Corbyn, BSc Comp. Sci." <mark@swiftmailer.org>',
      $header->getPreparedValue()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris Corbyn, DHE',
      'mark@swiftmailer.org' => 'Mark Corbyn, BSc Comp. Sci.'),
      $header->getNameAddresses()
      );
    $this->assertEqual(
      array('"Chris Corbyn\\, DHE" <chris@swiftmailer.org>',
      '"Mark Corbyn\\, BSc Comp\\. Sci\\." <mark@swiftmailer.org>'),
      $header->getNameAddressStrings()
      );
  }
  
  public function testSetValueWithAddrSpecInQuotedString()
  {
    $header = $this->_getHeader('From');
    $header->setPreparedValue(
      '"Chris <c@a.b> Corbyn" <chris@swiftmailer.org>'
      );
    
    $this->assertEqual(
      '"Chris <c@a.b> Corbyn" <chris@swiftmailer.org>',
      $header->getPreparedValue()
      );
    $this->assertEqual(array('chris@swiftmailer.org'), $header->getAddresses());
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris <c@a.b> Corbyn'),
      $header->getNameAddresses()
      );
    $this->assertEqual(
      array('"Chris \\<c\\@a\\.b\\> Corbyn" <chris@swiftmailer.org>'),
      $header->getNameAddressStrings()
      );
  }
  
  public function testSetValueWithQEncodedWords()
  {
    $encoder = new Swift_Mime_MockHeaderEncoder();
    $encoder->setReturnValue('getName', 'Q');
    $encoder->expectOnce('encodeString', array(
      new Swift_IdenticalBinaryExpectation(
        'C' . pack('C', 0x8F) . 'rbyn'
        ),
      '*',
      '*'
      ));
    $encoder->setReturnValue('encodeString', 'C=8Frbyn');
    
    $header = $this->_getHeader('From', null, $encoder);
    $header->setPreparedValue(
      '=?utf-8?Q?Chris_C=8Frbyn?=' . "\r\n " . 
      ' =?utf-8?Q?_Mail_Guru?= <chris@swiftmailer.org>,' . "\r\n " .
      'Mark Corbyn <mark@swiftmailer.org>'
      );
    
    $this->assertEqual(
      '=?utf-8?Q?Chris_C=8Frbyn?=' . "\r\n " . 
      ' =?utf-8?Q?_Mail_Guru?= <chris@swiftmailer.org>,' . "\r\n " .
      'Mark Corbyn <mark@swiftmailer.org>',
      $header->getPreparedValue()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris C'. pack('C', 0x8F) . 'rbyn Mail Guru',
      'mark@swiftmailer.org' => 'Mark Corbyn'),
      $header->getNameAddresses(),
      '%s: Encoded words should be decoded'
      );
    $this->assertEqual(
      array('Chris =?utf-8?Q?C=8Frbyn?= Mail Guru <chris@swiftmailer.org>',
      'Mark Corbyn <mark@swiftmailer.org>'),
      $header->getNameAddressStrings()
      );
  }
  
  public function testSetValueWithBEncodedWords()
  {
    $encoder = new Swift_Mime_MockHeaderEncoder();
    $encoder->setReturnValue('getName', 'B');
    $encoder->expectOnce('encodeString', array(
      new Swift_IdenticalBinaryExpectation(
        'C' . pack('C', 0x8F) . 'rbyn'
        ),
      '*',
      '*'
      ));
    $encoder->setReturnValue('encodeString', 'Q49yYnlu');
    
    $header = $this->_getHeader('From', null, $encoder);
    $header->setPreparedValue(
      '=?utf-8?B?Q2hyaXMgQ49y?=' . "\r\n " . 
      ' =?utf-8?B?YnluIE1haWwgR3VydQ==?= <chris@swiftmailer.org>,' . "\r\n " .
      'Mark Corbyn <mark@swiftmailer.org>'
      );
    
    $this->assertEqual(
      '=?utf-8?B?Q2hyaXMgQ49y?=' . "\r\n " . 
      ' =?utf-8?B?YnluIE1haWwgR3VydQ==?= <chris@swiftmailer.org>,' . "\r\n " .
      'Mark Corbyn <mark@swiftmailer.org>',
      $header->getPreparedValue()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
      $header->getAddresses()
      );
    $this->assertEqual(
      array('chris@swiftmailer.org' => 'Chris C'. pack('C', 0x8F) . 'rbyn Mail Guru',
      'mark@swiftmailer.org' => 'Mark Corbyn'),
      $header->getNameAddresses(),
      '%s: Encoded words should be decoded'
      );
    $this->assertEqual(
      array('Chris =?utf-8?B?Q49yYnlu?= Mail Guru <chris@swiftmailer.org>',
      'Mark Corbyn <mark@swiftmailer.org>'),
      $header->getNameAddressStrings()
      );
  }
  
  public function testRemoveAddressesWithSingleValue()
  {
    $header = $this->_getHeader('From', array(
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
    $header = $this->_getHeader('From', array(
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
