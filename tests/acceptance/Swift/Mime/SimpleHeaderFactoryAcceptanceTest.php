<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Mime/SimpleHeaderFactory.php';

class Swift_Mime_SimpleHeaderFactoryAcceptanceTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_factory;
  
  public function setUp()
  {
    $this->_factory = $this->_createFactory();
  }
  
  public function testNothing()
  {
    $this->assertFalse(true, 'Just a nothing test');
  }
  
  // -- Creation methods
  
  private function _createFactory()
  {
    return new Swift_Mime_SimpleHeaderFactory();
  }
  
}
