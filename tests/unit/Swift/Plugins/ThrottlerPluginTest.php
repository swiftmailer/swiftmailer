<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Plugins/ThrottlerPlugin.php';
require_once 'Swift/Events/SendEvent.php';
require_once 'Swift/Plugins/Sleeper.php';
require_once 'Swift/Plugins/Timer.php';
require_once 'Swift/Mime/Message.php';

class Swift_Plugins_ThrottlerPluginTest extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testBytesPerMinuteThrottling()
  {
    $sleeper = $this->_createSleeper();
    $timer = $this->_createTimer();
    
    //10MB/min
    $plugin = new Swift_Plugins_ThrottlerPlugin(
      10000000, Swift_Plugins_ThrottlerPlugin::BYTES_PER_MINUTE,
      $sleeper, $timer
      );
    
    $this->_checking(Expectations::create()
      -> one($timer)->getTimestamp() -> returns(0)
      -> one($timer)->getTimestamp() -> returns(1) //expected 0.6
      -> one($timer)->getTimestamp() -> returns(1) //expected 1.2 (sleep 1)
      -> one($timer)->getTimestamp() -> returns(2) //expected 1.8
      -> one($timer)->getTimestamp() -> returns(2) //expected 2.4 (sleep 1)
      -> ignoring($timer)
      
      -> exactly(2)->of($sleeper)->sleep(1)
      );
    
    //10,000,000 bytes per minute
    //100,000 bytes per email
    
    // .: (10,000,000/100,000)/60 emails per second = 1.667 emais/sec
    
    $message = $this->_createMessageWithByteCount(100000); //100KB
    
    $evt = $this->_createSendEvent($message);
    
    for ($i = 0; $i < 5; ++$i)
    {
      $plugin->beforeSendPerformed($evt);
      $plugin->sendPerformed($evt);
    }
  }
  
  public function testMessagesPerMinuteThrottling()
  {
    $sleeper = $this->_createSleeper();
    $timer = $this->_createTimer();
    
    //60/min
    $plugin = new Swift_Plugins_ThrottlerPlugin(
      60, Swift_Plugins_ThrottlerPlugin::MESSAGES_PER_MINUTE,
      $sleeper, $timer
      );
    
    $this->_checking(Expectations::create()
      -> one($timer)->getTimestamp() -> returns(0)
      -> one($timer)->getTimestamp() -> returns(0) //expected 1 (sleep 1)
      -> one($timer)->getTimestamp() -> returns(2) //expected 2
      -> one($timer)->getTimestamp() -> returns(2) //expected 3 (sleep 1)
      -> one($timer)->getTimestamp() -> returns(4) //expected 4
      -> ignoring($timer)
      
      -> exactly(2)->of($sleeper)->sleep(1)
      );
      
    //60 messages per minute
    //1 message per second
    
    $message = $this->_createMessageWithByteCount(10);
    
    $evt = $this->_createSendEvent($message);
    
    for ($i = 0; $i < 5; ++$i)
    {
      $plugin->beforeSendPerformed($evt);
      $plugin->sendPerformed($evt);
    }
  }
  
  // -- Creation Methods
  
  private function _createSleeper()
  {
    return $this->_mock('Swift_Plugins_Sleeper');
  }
  
  private function _createTimer()
  {
    return $this->_mock('Swift_Plugins_Timer');
  }
  
  private function _createMessageWithByteCount($bytes)
  {
    $this->_bytes = $bytes;
    $msg = $this->_mock('Swift_Mime_Message');
    $this->_checking(Expectations::create()
      -> ignoring($msg)->toByteStream(any()) -> calls(array($this, '_write'))
    );
    return $msg;
  }
  
  private function _createSendEvent($message)
  {
    $evt = $this->_mock('Swift_Events_SendEvent');
    $this->_checking(Expectations::create()
      -> ignoring($evt)->getMessage() -> returns($message)
      );
    return $evt;
  }
  
  private $_bytes = 0;
  public function _write($invocation)
  {
    $args = $invocation->getArguments();
    $is = $args[0];
    for ($i = 0; $i < $this->_bytes; ++$i)
    {
      $is->write('x');
    }
  }
  
}
