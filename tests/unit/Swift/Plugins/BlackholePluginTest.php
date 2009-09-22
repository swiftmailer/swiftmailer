<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Plugins/BlackholePlugin.php';
require_once 'Swift/Events/SendEvent.php';
require_once 'Swift/Transport.php';

class Swift_Plugins_BlackholePluginTest extends Swift_Tests_SwiftUnitTestCase
{
  public function testPluginStopsDelivery()
  {
    $transport = $this->_createTransport();
    $evt = $this->_createSendEvent($transport);
    $this->_checking(Expectations::create()
      -> never($transport)->start()
      -> never($transport)->stop()
      -> never($transport)->send()
      -> ignoring($transport)
      );
    
    $plugin = new Swift_Plugins_BlackholePlugin();
    $plugin->beforeSendPerformed($evt);
  }
  
  // -- Creation Methods
  
  private function _createTransport()
  {
    return $this->_mock('Swift_Transport');
  }
  
  private function _createSendEvent($transport)
  {
    $evt = $this->_mock('Swift_Events_SendEvent');
    $this->_checking(Expectations::create()
      -> ignoring($evt)->getSource() -> returns($transport)
      -> ignoring($evt)->getTransport() -> returns($transport)
      -> one($evt)->cancelBubble()
      );
    return $evt;
  }
}
