<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/PolymorphicBuffer.php';

class Swift_Transport_PolymorphicBufferAcceptanceTest
  extends Swift_Tests_SwiftUnitTestCase
{

  private $_buffer;
  
  public function setUp()
  {
    $this->_buffer = new Swift_Transport_PolymorphicBuffer();
  }
  
  public function testNothing()
  {
    $this->assertFalse(true);
  }
  
}
