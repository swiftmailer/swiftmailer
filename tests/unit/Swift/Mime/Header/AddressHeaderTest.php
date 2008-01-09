<?php

require_once 'Swift/Mime/Header/AddressHeader.php';
require_once 'Swift/Mime/HeaderAttribute.php';
require_once 'Swift/Mime/HeaderAttributeSet.php';
require_once 'Swift/Mime/HeaderEncoder.php';

Mock::generate('Swift_Mime_HeaderAttribute', 'Swift_Mime_MockHeaderAttribute');
Mock::generate('Swift_Mime_HeaderAttributeSet',
  'Swift_Mime_MockHeaderAttributeSet'
  );
Mock::generate('Swift_Mime_HeaderEncoder', 'Swift_Mime_MockHeaderEncoder');

class Swift_Mime_Header_AddressHeaderTest extends UnitTestCase
{
  
  private $_charset = 'utf-8';
  
  /*private $_regex;
  
  public function setUp()
  {
    $parenStr = '(\((?:\w|(?1))+?\))';
    $this->_regex = '/^' . $parenStr . '$/';
  }
  
  public function testMatching()
  {
    $this->assertPattern($this->_regex, '(abc_123)');
    $this->assertPattern($this->_regex, '(abc_123(cde_456)789)');
    $this->assertPattern($this->_regex, '(abc(xyz(123)89)(foo(bar(zip(button))))test)');
  }*/
  
  /*
  $this->_getHeader('From', array('chris@swiftmailer.org'=>'Chris Corbyn'));
  $this->_getHeader('To', array(
    'chris@swiftmailer.org'=>'Chris Corbyn',
    'mark.corbyn@swiftmailer.org'=>'Mark Corbyn'
    ));
    
  //setAddresses(), getAddresses(), setName()
  */
  
  public function testEmailAddressIsReturned()
  {
    $header = $this->_getHeader('From', 'chris@swiftmailer.org');
    $this->assertEqual('chris@swiftmailer.org', $header->getAddress());
  }
  
  public function testEmailAddressIsReturnedInValue()
  {
    $header = $this->_getHeader('From', 'chris@swiftmailer.org');
    $this->assertEqual('chris@swiftmailer.org', $header->getValue());
  }
  
  // -- Private methods
  
  private function _getHeader($name, $value = null, $encoder = null)
  {
    if (!$encoder)
    {
      $encoder = new Swift_Mime_MockHeaderEncoder();
    }
    return new Swift_Mime_Header_AddressHeader(
      $name, $value, $this->_charset, $encoder
      );
  }
  
}
