<?php

require_once 'Swift/AbstractSwiftUnitTestCase.php';
require_once 'Swift/Mime/Header/VersionHeader.php';

class Swift_Mime_Header_VersionHeaderTest
  extends Swift_AbstractSwiftUnitTestCase
{
  
  public function testVersionCanBeSetAndFetched()
  {
    $header = $this->_getHeader('MIME-Version', '1.0');
    $this->assertEqual('1.0', $header->getVersion());
  }
  
  public function testVersionCanBeSetBySetter()
  {
    $header = $this->_getHeader('MIME-Version');
    $header->setVersion('1.0');
    $this->assertEqual('1.0', $header->getVersion());
  }
  
  public function testVersionMustBeDottedNumber()
  {
    /* -- RFC 2045, 4.
      version := "MIME-Version" ":" 1*DIGIT "." 1*DIGIT
     */
    
    try
    {
      $header = $this->_getHeader('MIME-Version', '1');
      $this->fail(
        'version must be dot-separated digits according to RFC 2045, 4.'
        );
    }
    catch (Exception $e)
    {
      $this->pass();
    }
  }
  
  public function testGetValueReturnsVersion()
  {
    $header = $this->_getHeader('MIME-Version', '1.0');
    $this->assertEqual('1.0', $header->getValue());
  }
  
  public function testToString()
  {
    $header = $this->_getHeader('MIME-Version', '1.0');
    $this->assertEqual('MIME-Version: 1.0' . "\r\n", $header->toString());
  }
  
  public function testSetValueResolvesVersion()
  {
    $header = $this->_getHeader('MIME-Version');
    $header->setValue('1.0');
    $this->assertEqual('1.0', $header->getVersion());
    $this->assertEqual('1.0', $header->getValue());
  }
  
  public function testSetValueIgnoresComments()
  {
    /* -- RFC 2045, 4.
    NOTE TO IMPLEMENTORS:  When checking MIME-Version values any RFC 822
    comment strings that are present must be ignored.  In particular, the
    following four MIME-Version fields are equivalent:

     MIME-Version: 1.0

     MIME-Version: 1.0 (produced by MetaSend Vx.x)

     MIME-Version: (produced by MetaSend Vx.x) 1.0

     MIME-Version: 1.(produced by MetaSend Vx.x)0
     */
    
    $header = $this->_getHeader('MIME-Version');
    
    $header->setValue('1.0 (produced by MetaSend Vx.x)');
    $this->assertEqual('1.0', $header->getVersion());
    $this->assertEqual('1.0 (produced by MetaSend Vx.x)', $header->getValue());
    
    $header->setValue('(produced by MetaSend Vx.x) 1.0');
    $this->assertEqual('1.0', $header->getVersion());
    $this->assertEqual('(produced by MetaSend Vx.x) 1.0', $header->getValue());
    
    $header->setValue('1.(produced by MetaSend Vx.x)0');
    $this->assertEqual('1.0', $header->getVersion());
    $this->assertEqual('1.(produced by MetaSend Vx.x)0', $header->getValue());
  }
  
  // -- Private methods
  
  private function _getHeader($name, $ver = null)
  {
    return new Swift_Mime_Header_VersionHeader($name, $ver);
  }
  
}
