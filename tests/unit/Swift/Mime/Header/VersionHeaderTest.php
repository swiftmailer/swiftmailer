<?php

require_once 'Swift/AbstractSwiftUnitTestCase.php';
require_once 'Swift/Mime/Header/VersionHeader.php';

class Swift_Mime_Header_VersionHeaderTest
  extends Swift_AbstractSwiftUnitTestCase
{
  
  public function testVersionCanBeSetAndFetched()
  {
    $header = $this->_getHeader('MIME-Version');
    $header->setVersion('1.0');
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
      $header = $this->_getHeader('MIME-Version');
      $header->setVersion('1');
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
    $header = $this->_getHeader('MIME-Version');
    $header->setVersion('1.0');
    $this->assertEqual('1.0', $header->getFieldBody());
  }
  
  public function testToString()
  {
    $header = $this->_getHeader('MIME-Version');
    $header->setVersion('1.0');
    $this->assertEqual('MIME-Version: 1.0' . "\r\n", $header->toString());
  }
  
  // -- Private methods
  
  private function _getHeader($name)
  {
    return new Swift_Mime_Header_VersionHeader($name);
  }
  
}
