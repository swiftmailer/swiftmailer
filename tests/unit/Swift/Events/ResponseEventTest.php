<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Events/ResponseEvent.php';
require_once 'Swift/Transport/EsmtpBufferWrapper.php';

class Swift_Events_ResponseEventTest extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testResponseCanBeInjected()
  {
    $evt = new Swift_Events_ResponseEvent();
    $evt->response = "250 Ok\r\n";
    $this->assertEqual("250 Ok\r\n", $evt->getResponse(),
      '%s: Response should be injectable'
      );
  }
  
  public function testResultCanBeInjected()
  {
    $evt = new Swift_Events_ResponseEvent();
    $evt->result = Swift_Events_ResponseEvent::RESULT_INVALID;
    $this->assertEqual(
      Swift_Events_ResponseEvent::RESULT_INVALID, $evt->getResult(),
      '%s: Result should be injectable'
      );
  }
  
  public function testCleanCloneIsCreated()
  {
    $context = new Mockery();
    $buf = $context->mock('Swift_Transport_EsmtpBufferWrapper');
    
    $evt = new Swift_Events_ResponseEvent();
    $evt->response = "250 Ok\r\n";
    $evt->result = Swift_Events_ResponseEvent::RESULT_INVALID;
    
    $clone = $evt->cloneFor($buf);
    $source = $clone->getSource();
    $this->assertReference($buf, $source);
    $this->assertEqual('', $clone->getResponse());
    $this->assertEqual(Swift_Events_ResponseEvent::RESULT_VALID, $clone->getResult());
  }
  
}
