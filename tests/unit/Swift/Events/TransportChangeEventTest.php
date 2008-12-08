<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Events/TransportChangeEvent.php';
require_once 'Swift/Transport.php';

class Swift_Events_TransportChangeEventTest extends Swift_Tests_SwiftUnitTestCase
{

  public function testCleanCloneIsGenerated()
  {
    $transport = $this->_mock('Swift_Transport');
    
    $evt = new Swift_Events_TransportChangeEvent();
    
    $clone = $evt->cloneFor($transport);
    
    $source = $clone->getSource();
    $this->assertReference($transport, $source,
      '%s: Transport should be available via getSource()'
      );
    $ref = $clone->getTransport();
    $this->assertReference($transport, $ref,
      '%s: Transport should be available via getTransport()'
      );
  }
  
}
