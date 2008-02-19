<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/PolymorphicBuffer.php';

class Swift_Transport_PolymorphicBuffer_BasicSocketAcceptanceTest
  extends Swift_Tests_SwiftUnitTestCase
{

  private $_buffer;
  
  public function skip()
  {
    $this->skipUnless(SWIFT_SMTP_HOST,
      'Cannot run test without an SMTP host to connect to (define ' .
      'SWIFT_SMTP_HOST in tests/config.php if you wish to run this test)'
      );
  }
  
  public function setUp()
  {
    $this->_buffer = new Swift_Transport_PolymorphicBuffer();
  }
  
  public function testNothing()
  {
    $this->assertFalse(true);
  }
  
}
