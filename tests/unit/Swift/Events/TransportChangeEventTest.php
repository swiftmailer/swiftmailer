<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Events/TransportChangeEvent.php';
require_once 'Swift/Transport.php';

class Swift_Events_TransportChangeEventTest extends Swift_Tests_SwiftUnitTestCase
{

  public function testCleanCloneIsGenerated()
  {
    $context = new Mockery();
    $transport = $context->mock('Swift_Transport');
    
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
