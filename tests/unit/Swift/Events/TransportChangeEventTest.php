<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Events/TransportChangeEvent.php';
require_once 'Swift/Transport.php';

class Swift_Events_TransportChangeEventTest extends Swift_Tests_SwiftUnitTestCase
{

  public function testGetTransportReturnsTransport()
  {
    $transport = $this->_createTransport();
    $evt = $this->_createEvent($transport);
    $ref = $evt->getTransport();
    $this->assertReference($transport, $ref);
  }
  
  public function testSourceIsTransport()
  {
    $transport = $this->_createTransport();
    $evt = $this->_createEvent($transport);
    $ref = $evt->getSource();
    $this->assertReference($transport, $ref);
  }
  
  // -- Creation Methods
  
  private function _createEvent(Swift_Transport $source)
  {
    return new Swift_Events_TransportChangeEvent($source);
  }
  
  private function _createTransport()
  {
    return $this->_stub('Swift_Transport');
  }
  
}
