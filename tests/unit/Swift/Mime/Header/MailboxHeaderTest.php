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
  
  public function testFieldChangeObserverCanSetSender()
  {
    $header = $this->_getHeader('Sender',
      new Swift_Mime_MockHeaderEncoder()
      );
    $header->fieldChanged('sender', array('abc@xyz'=>null));
    $this->assertEqual(array('abc@xyz'=>null), $header->getNameAddresses());
  }
  
  public function testSenderFieldChangeIsIgnoredByOtherHeaders()
  {
    $header = $this->_getHeader('To',
      new Swift_Mime_MockHeaderEncoder()
      );
    $header->setNameAddresses(array('abc@xyz'=>'Person'));
    $header->fieldChanged('sender', array('foo@bar'=>'Foobar'));
    $this->assertEqual(array('abc@xyz'=>'Person'), $header->getNameAddresses());
  }
  
  public function testOtherFieldChangesAreIgnoredForSender()
  {
    $header = $this->_getHeader('Sender',
      new Swift_Mime_MockHeaderEncoder()
      );
    $header->setNameAddresses(array('abc@xyz'=>'Person'));
    foreach (array('to', 'cc', 'bcc', 'from', 'replyto') as $field)
    {
      $header->fieldChanged($field, array('xxx@yyy'=>null));
      $this->assertEqual(array('abc@xyz'=>'Person'), $header->getNameAddresses());
    }
  }
  
  public function testFieldChangeObserverCanSetFrom()
  {
    $header = $this->_getHeader('From',
      new Swift_Mime_MockHeaderEncoder()
      );
    $header->fieldChanged('from', array('abc@xyz'=>null));
    $this->assertEqual(array('abc@xyz'=>null), $header->getNameAddresses());
  }
  
  public function testFromFieldChangeIsIgnoredByOtherHeaders()
  {
    $header = $this->_getHeader('To',
      new Swift_Mime_MockHeaderEncoder()
      );
    $header->setNameAddresses(array('abc@xyz'=>'Person'));
    $header->fieldChanged('from', array('foo@bar'=>'Foobar'));
    $this->assertEqual(array('abc@xyz'=>'Person'), $header->getNameAddresses());
  }
  
  public function testOtherFieldChangesAreIgnoredForFrom()
  {
    $header = $this->_getHeader('From',
      new Swift_Mime_MockHeaderEncoder()
      );
    $header->setNameAddresses(array('abc@xyz'=>'Person'));
    foreach (array('to', 'cc', 'bcc', 'sender', 'replyto') as $field)
    {
      $header->fieldChanged($field, array('xxx@yyy'=>null));
      $this->assertEqual(array('abc@xyz'=>'Person'), $header->getNameAddresses());
    }
  }
  
  public function testFieldChangeObserverCanSetReplyTo()
  {
    $header = $this->_getHeader('Reply-To',
      new Swift_Mime_MockHeaderEncoder()
      );
    $header->fieldChanged('replyto', array('abc@xyz'=>null));
    $this->assertEqual(array('abc@xyz'=>null), $header->getNameAddresses());
  }
  
  public function testReplyToFieldChangeIsIgnoredByOtherHeaders()
  {
    $header = $this->_getHeader('To',
      new Swift_Mime_MockHeaderEncoder()
      );
    $header->setNameAddresses(array('abc@xyz'=>'Person'));
    $header->fieldChanged('replyto', array('foo@bar'=>'Foobar'));
    $this->assertEqual(array('abc@xyz'=>'Person'), $header->getNameAddresses());
  }
  
  public function testOtherFieldChangesAreIgnoredForReplyTo()
  {
    $header = $this->_getHeader('Reply-To',
      new Swift_Mime_MockHeaderEncoder()
      );
    $header->setNameAddresses(array('abc@xyz'=>'Person'));
    foreach (array('to', 'cc', 'bcc', 'sender', 'from') as $field)
    {
      $header->fieldChanged($field, array('xxx@yyy'=>null));
      $this->assertEqual(array('abc@xyz'=>'Person'), $header->getNameAddresses());
    }
  }
  
  public function testFieldChangeObserverCanSetTo()
  {
    $header = $this->_getHeader('To',
      new Swift_Mime_MockHeaderEncoder()
      );
    $header->fieldChanged('to', array('abc@xyz'=>null));
    $this->assertEqual(array('abc@xyz'=>null), $header->getNameAddresses());
  }
  
  public function testToFieldChangeIsIgnoredByOtherHeaders()
  {
    $header = $this->_getHeader('Reply-To',
      new Swift_Mime_MockHeaderEncoder()
      );
    $header->setNameAddresses(array('abc@xyz'=>'Person'));
    $header->fieldChanged('to', array('foo@bar'=>'Foobar'));
    $this->assertEqual(array('abc@xyz'=>'Person'), $header->getNameAddresses());
  }
  
  public function testOtherFieldChangesAreIgnoredForTo()
  {
    $header = $this->_getHeader('To',
      new Swift_Mime_MockHeaderEncoder()
      );
    $header->setNameAddresses(array('abc@xyz'=>'Person'));
    foreach (array('replyto', 'cc', 'bcc', 'sender', 'from') as $field)
    {
      $header->fieldChanged($field, array('xxx@yyy'=>null));
      $this->assertEqual(array('abc@xyz'=>'Person'), $header->getNameAddresses());
    }
  }
  
  public function testFieldChangeObserverCanSetCc()
  {
    $header = $this->_getHeader('Cc',
      new Swift_Mime_MockHeaderEncoder()
      );
    $header->fieldChanged('cc', array('abc@xyz'=>null));
    $this->assertEqual(array('abc@xyz'=>null), $header->getNameAddresses());
  }
  
  public function testCcFieldChangeIsIgnoredByOtherHeaders()
  {
    $header = $this->_getHeader('Reply-To',
      new Swift_Mime_MockHeaderEncoder()
      );
    $header->setNameAddresses(array('abc@xyz'=>'Person'));
    $header->fieldChanged('cc', array('foo@bar'=>'Foobar'));
    $this->assertEqual(array('abc@xyz'=>'Person'), $header->getNameAddresses());
  }
  
  public function testOtherFieldChangesAreIgnoredForCc()
  {
    $header = $this->_getHeader('Cc',
      new Swift_Mime_MockHeaderEncoder()
      );
    $header->setNameAddresses(array('abc@xyz'=>'Person'));
    foreach (array('replyto', 'to', 'bcc', 'sender', 'from') as $field)
    {
      $header->fieldChanged($field, array('xxx@yyy'=>null));
      $this->assertEqual(array('abc@xyz'=>'Person'), $header->getNameAddresses());
    }
  }
  
  public function testFieldChangeObserverCanSetBcc()
  {
    $header = $this->_getHeader('Bcc',
      new Swift_Mime_MockHeaderEncoder()
      );
    $header->fieldChanged('bcc', array('abc@xyz'=>null));
    $this->assertEqual(array('abc@xyz'=>null), $header->getNameAddresses());
  }
  
  public function testBccFieldChangeIsIgnoredByOtherHeaders()
  {
    $header = $this->_getHeader('Reply-To',
      new Swift_Mime_MockHeaderEncoder()
      );
    $header->setNameAddresses(array('abc@xyz'=>'Person'));
    $header->fieldChanged('bcc', array('foo@bar'=>'Foobar'));
    $this->assertEqual(array('abc@xyz'=>'Person'), $header->getNameAddresses());
  }
  
  public function testOtherFieldChangesAreIgnoredForBcc()
  {
    $header = $this->_getHeader('Bcc',
      new Swift_Mime_MockHeaderEncoder()
      );
    $header->setNameAddresses(array('abc@xyz'=>'Person'));
    foreach (array('replyto', 'to', 'cc', 'sender', 'from') as $field)
    {
      $header->fieldChanged($field, array('xxx@yyy'=>null));
      $this->assertEqual(array('abc@xyz'=>'Person'), $header->getNameAddresses());
    }
  }
  
  // -- Private methods
  
  private function _getHeader($name, $encoder)
  {
    $header = new Swift_Mime_Header_MailboxHeader($name, $encoder);
    $header->setCharset($this->_charset);
    return $header;
  }
  
}
