<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Plugins/ThrottlerPlugin.php';
require_once 'Swift/Events/SendEvent.php';
require_once 'Swift/Events/CommandEvent.php';
require_once 'Swift/Events/ResponseEvent.php';
require_once 'Swift/Plugins/Sleeper.php';
require_once 'Swift/Plugins/Timer.php';
require_once 'Swift/Mime/Message.php';

Mock::generate('Swift_Events_SendEvent', 'Swift_Events_MockSendEvent');
Mock::generate('Swift_Events_CommandEvent', 'Swift_Events_MockCommandEvent');
Mock::generate('Swift_Events_ResponseEvent', 'Swift_Events_MockResponseEvent');
Mock::generate('Swift_Mime_Message', 'Swift_Mime_MockMessage');
Mock::generate('Swift_Plugins_Sleeper', 'Swift_Plugins_MockSleeper');
Mock::generate('Swift_Plugins_Timer', 'Swift_Plugins_MockTimer');

class Swift_Mime_MockMessageWriter extends Swift_Mime_MockMessage {
  public $write = array();
  public function toByteStream(Swift_InputByteStream $is) {
    foreach ($this->write as $chunk) {
      $is->write($chunk);
    }
  }
}

class Swift_Plugins_ThrottlerPluginTest extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testBytesPerMinuteThrottling()
  {
    $sleeper = new Swift_Plugins_MockSleeper();
    $timer = new Swift_Plugins_MockTimer();
    
    //10MB/min
    $plugin = new Swift_Plugins_ThrottlerPlugin(
      10000000, Swift_Plugins_ThrottlerPlugin::BYTES_PER_MINUTE,
      $sleeper, $timer
      );
    
    $timer->setReturnValueAt(0, 'getTimestamp', 0);
    $timer->setReturnValueAt(1, 'getTimestamp', 1); //expected 0.6
    $timer->setReturnValueAt(2, 'getTimestamp', 1); //expected 1.2 (sleep 1)
    $timer->setReturnValueAt(3, 'getTimestamp', 2); //expected 1.8
    $timer->setReturnValueAt(4, 'getTimestamp', 2); //expected 2.4 (sleep 1)
    $sleeper->expectAt(0, 'sleep', array(1));
    $sleeper->expectAt(1, 'sleep', array(1));
    $sleeper->expectCallCount('sleep', 2);
    
    //10,000,000 bytes per minute
    //100,000 bytes per email
    
    // .: (10,000,000/100,000)/60 emails per second = 1.667 emais/sec
    
    $message = new Swift_Mime_MockMessageWriter();
    $message->write = array(str_repeat('x', 100000)); //100KB
    $evt = new Swift_Events_MockSendEvent();
    $evt->setReturnValue('getMessage', $message);
    
    for ($i = 0; $i < 5; ++$i)
    {
      $plugin->beforeSendPerformed($evt);
      $plugin->sendPerformed($evt);
    }
  }
  
  public function testMessagesPerMinuteThrottling()
  {
    $sleeper = new Swift_Plugins_MockSleeper();
    $timer = new Swift_Plugins_MockTimer();
    
    //10MB/min
    $plugin = new Swift_Plugins_ThrottlerPlugin(
      60, Swift_Plugins_ThrottlerPlugin::MESSAGES_PER_MINUTE,
      $sleeper, $timer
      );
    
    $timer->setReturnValueAt(0, 'getTimestamp', 0);
    $timer->setReturnValueAt(1, 'getTimestamp', 0); //expected 1 (sleep 1)
    $timer->setReturnValueAt(2, 'getTimestamp', 2); //expected 2
    $timer->setReturnValueAt(3, 'getTimestamp', 2); //expected 3 (sleep 1)
    $timer->setReturnValueAt(4, 'getTimestamp', 4); //expected 4
    $sleeper->expectAt(0, 'sleep', array(1));
    $sleeper->expectAt(1, 'sleep', array(1));
    $sleeper->expectCallCount('sleep', 2);
    
    //60 messages per minute
    //1 message per second
    
    $message = new Swift_Mime_MockMessage();
    $evt = new Swift_Events_MockSendEvent();
    $evt->setReturnValue('getMessage', $message);
    
    for ($i = 0; $i < 5; ++$i)
    {
      $plugin->beforeSendPerformed($evt);
      $plugin->sendPerformed($evt);
    }
  }
  
}
