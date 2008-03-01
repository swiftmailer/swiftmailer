<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Events/CommandEvent.php';
require_once 'Swift/Transport/EsmtpBufferWrapper.php';

Mock::generate('Swift_Transport_EsmtpBufferWrapper',
  'Swift_Transport_MockEsmtpBufferWrapper'
  );

class Swift_Events_CommandEventTest extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testCommandCanBeInjected()
  {
    $evt = new Swift_Events_CommandEvent();
    $evt->command = "HELO foobar.net\r\n";
    $this->assertEqual("HELO foobar.net\r\n", $evt->getCommand());
  }
  
  public function testSuccessCodesCanBeInjected()
  {
    $evt = new Swift_Events_CommandEvent();
    $evt->successCodes = array(250, 251);
    $this->assertEqual(array(250, 251), $evt->getSuccessCodes());
  }
  
  public function testCleanCloneIsGenerated()
  {
    $buf = new Swift_Transport_MockEsmtpBufferWrapper();
    
    $evt = new Swift_Events_CommandEvent();
    $evt->command = "HELO foobar.net\r\n";
    $evt->successCodes = array(250);
    
    $clone = $evt->cloneFor($buf);
    
    $this->assertEqual('', $clone->getCommand());
    $this->assertEqual(array(), $clone->getSuccessCodes());
    $this->assertReference($buf, $clone->getSource());
  }
  
}
