<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Events/TransportExceptionEvent.php';
require_once 'Swift/Transport.php';
require_once 'Swift/Transport/TransportException.php';

Mock::generate('Swift_Transport', 'Swift_MockTransport');

class Swift_Events_TransportExceptionEventTest extends Swift_Tests_SwiftUnitTestCase
{

  public function testExceptionIsInjectable()
  {
    $e = new Swift_Transport_TransportException('foo');
    $evt = new Swift_Events_TransportExceptionEvent();
    $evt->exception = $e;
    $this->assertReference($e, $evt->getException(),
      '%s: Exception should be injectable'
      );
  }
  
  public function testCleanCloneIsGenerated()
  {
    $transport = new Swift_MockTransport();
    $evt = new Swift_Events_TransportExceptionEvent();
    $evt->exception = new Swift_Transport_TransportException('foo');
    
    $clone = $evt->cloneFor($transport);
    
    $this->assertNull($clone->getException());
    $this->assertReference($transport, $clone->getSource(),
      '%s: Transport should be available via getSource()'
      );
  }
  
}
