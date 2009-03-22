<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'SwiftX/Charset/UTF8.php';

class SwiftX_Charset_UTF8Test extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_charset;
  
  public function setUp()
  {
    $this->_charset = $this->_createCharset();
  }
  
  public function testNameIsUtf8()
  {
    $this->assertEqual('UTF-8', $this->_charset->getName());
  }
  
  public function testHasNoAliases()
  {
    $this->assertEqual(array(), $this->_charset->getAliases());
  }
  
  // -- Creation Methods
  
  private function _createCharset()
  {
    return new SwiftX_Charset_UTF8();
  }
  
}
