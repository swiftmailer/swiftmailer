<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Plugins/AntiFloodPlugin.php';
require_once 'Swift/Events/SendEvent.php';
require_once 'Swift/Transport.php';
require_once 'Swift/Plugins/Sleeper.php';

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
    $transport = $this->_createTransport();
    $evt = $this->_createSendEvent($transport);
    $this->_checking(Expectations::create()
      -> one($transport)->start()
      -> one($transport)->stop()
      -> ignoring($transport)
      );
    
    $plugin = new Swift_Plugins_AntiFloodPlugin(10);
    for ($i = 0; $i < 12; $i++)
    {
      $plugin->sendPerformed($evt);
    }
  }
  
  public function testPluginCanStopAndStartMultipleTimes()
  {
    $transport = $this->_createTransport();
    $evt = $this->_createSendEvent($transport);
    $this->_checking(Expectations::create()
      -> exactly(5)->of($transport)->start()
      -> exactly(5)->of($transport)->stop()
      -> ignoring($transport)
      );
    
    $plugin = new Swift_Plugins_AntiFloodPlugin(2);
    for ($i = 0; $i < 11; $i++)
    {
      $plugin->sendPerformed($evt);
    }
  }
  
  public function testPluginCanSleepDuringRestart()
  {
    $sleeper = $this->_createSleeper();
    $transport = $this->_createTransport();
    $evt = $this->_createSendEvent($transport);
    $this->_checking(Expectations::create()
      -> one($sleeper)->sleep(10)
      -> one($transport)->start()
      -> one($transport)->stop()
      -> ignoring($transport)
      );
    
    $plugin = new Swift_Plugins_AntiFloodPlugin(99, 10, $sleeper);
    for ($i = 0; $i < 101; $i++)
    {
      $plugin->sendPerformed($evt);
    }
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
      );
    return $evt;
  }
  
  private function _createSleeper()
  {
    return $this->_mock('Swift_Plugins_Sleeper');
  }
  
}
