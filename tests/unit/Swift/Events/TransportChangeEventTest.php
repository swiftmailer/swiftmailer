<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Events/TransportChangeEvent.php';
require_once 'Swift/Transport.php';

Mock::generate('Swift_Transport', 'Swift_MockTransport');

class Swift_Events_TransportChangeEventTest extends Swift_Tests_SwiftUnitTestCase
{

  public function testCleanCloneIsGenerated()
  {
    $transport = new Swift_MockTransport();
    $evt = new Swift_Events_TransportChangeEvent();
    
    $clone = $evt->cloneFor($transport);
    
    $this->assertReference($transport, $clone->getSource(),
      '%s: Transport should be available via getSource()'
      );
    $this->assertReference($transport, $clone->getTransport(),
      '%s: Transport should be available via getTransport()'
      );
  }
  
}
