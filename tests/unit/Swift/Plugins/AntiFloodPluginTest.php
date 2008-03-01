<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Plugins/AntiFloodPlugin.php';
require_once 'Swift/Events/SendEvent.php';
require_once 'Swift/Transport.php';

Mock::generate('Swift_Events_SendEvent', 'Swift_Events_MockSendEvent');
Mock::generate('Swift_Transport', 'Swift_MockTransport');

class Swift_Plugins_AntiFloodPluginTest extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testThresholdCanBeSetAndFetched()
  {
    $plugin = new Swift_Plugins_AntiFloodPlugin(10);
    $this->assertEqual(10, $plugin->getThreshold());
    $plugin->setThreshold(100);
    $this->assertEqual(100, $plugin->getThreshold());
  }
  
  public function testSleepTimeCanBeSetAndFetched()
  {
    $plugin = new Swift_Plugins_AntiFloodPlugin(10, 5);
    $this->assertEqual(5, $plugin->getSleepTime());
    $plugin->setSleepTime(1);
    $this->assertEqual(1, $plugin->getSleepTime());
  }
  
  public function testPluginStopsConnectionAfterThreshold()
  {
    $transport = new Swift_MockTransport();
    $transport->expectOnce('stop');
    $transport->expectOnce('start');
    
    $evt = new Swift_Events_MockSendEvent();
    $evt->setReturnValue('getSource', $transport);
    $evt->setReturnValue('getTransport', $transport);
    
    $plugin = new Swift_Plugins_AntiFloodPlugin(10);
    for ($i = 0; $i < 12; $i++)
    {
      $plugin->sendPerformed($evt);
    }
  }
  
  public function testPluginCanStopAndStartMultipleTimes()
  {
    $transport = new Swift_MockTransport();
    $transport->expectCallCount('stop', 5);
    $transport->expectCallCount('start', 5);
    
    $evt = new Swift_Events_MockSendEvent();
    $evt->setReturnValue('getSource', $transport);
    $evt->setReturnValue('getTransport', $transport);
    
    $plugin = new Swift_Plugins_AntiFloodPlugin(2);
    for ($i = 0; $i < 11; $i++)
    {
      $plugin->sendPerformed($evt);
    }
  }
  
}
