<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Plugins/LoggerPlugin.php';
require_once 'Swift/Plugins/Logger.php';
require_once 'Swift/Events/CommandEvent.php';
require_once 'Swift/Events/ResponseEvent.php';
require_once 'Swift/Events/TransportChangeEvent.php';
require_once 'Swift/Events/TransportExceptionEvent.php';
require_once 'Swift/Transport.php';
require_once 'Swift/TransportException.php';

class Swift_Plugins_LoggerPluginTest extends Swift_Tests_SwiftUnitTestCase
{

  public function testLoggerDelegatesAddingEntries()
  {
    $logger = $this->_createLogger();
    $this->_checking(Expectations::create()
      -> one($logger)->add('foo')
      );
    
    $plugin = $this->_createPlugin($logger);
    $plugin->add('foo');
  }
  
  public function testLoggerDelegatesDumpingEntries()
  {
    $logger = $this->_createLogger();
    $this->_checking(Expectations::create()
      -> one($logger)->dump() -> returns('foobar')
      );
    
    $plugin = $this->_createPlugin($logger);
    $this->assertEqual('foobar', $plugin->dump());
  }
  
  public function testLoggerDelegatesClearingEntries()
  {
    $logger = $this->_createLogger();
    $this->_checking(Expectations::create()
      -> one($logger)->clear()
      );
    
    $plugin = $this->_createPlugin($logger);
    $plugin->clear();
  }
  
  public function testCommandIsSentToLogger()
  {
    $evt = $this->_createCommandEvent("foo\r\n");
    $logger = $this->_createLogger();
    $this->_checking(Expectations::create()
      -> one($logger)->add(pattern('~foo\r\n~'))
      );
    
    $plugin = $this->_createPlugin($logger);
    $plugin->commandSent($evt);
  }
  
  public function testResponseIsSentToLogger()
  {
    $evt = $this->_createResponseEvent("354 Go ahead\r\n");
    $logger = $this->_createLogger();
    $this->_checking(Expectations::create()
      -> one($logger)->add(pattern('~354 Go ahead\r\n~'))
      );
    
    $plugin = $this->_createPlugin($logger);
    $plugin->responseReceived($evt);
  }
  
  public function testTransportBeforeStartChangeIsSentToLogger()
  {
    $evt = $this->_createTransportChangeEvent();
    $logger = $this->_createLogger();
    $this->_checking(Expectations::create()
      -> one($logger)->add(any())
      );
    
    $plugin = $this->_createPlugin($logger);
    $plugin->beforeTransportStarted($evt);
  }
  
  public function testTransportStartChangeIsSentToLogger()
  {
    $evt = $this->_createTransportChangeEvent();
    $logger = $this->_createLogger();
    $this->_checking(Expectations::create()
      -> one($logger)->add(any())
      );
    
    $plugin = $this->_createPlugin($logger);
    $plugin->transportStarted($evt);
  }
  
  public function testTransportStopChangeIsSentToLogger()
  {
    $evt = $this->_createTransportChangeEvent();
    $logger = $this->_createLogger();
    $this->_checking(Expectations::create()
      -> one($logger)->add(any())
      );
    
    $plugin = $this->_createPlugin($logger);
    $plugin->transportStopped($evt);
  }
  
  public function testTransportBeforeStopChangeIsSentToLogger()
  {
    $evt = $this->_createTransportChangeEvent();
    $logger = $this->_createLogger();
    $this->_checking(Expectations::create()
      -> one($logger)->add(any())
      );
    
    $plugin = $this->_createPlugin($logger);
    $plugin->beforeTransportStopped($evt);
  }
  
  public function testExceptionsArePassedToDelegateAndLeftToBubbleUp()
  {
    $transport = $this->_createTransport();
    $evt = $this->_createTransportExceptionEvent();
    $logger = $this->_createLogger();
    $this->_checking(Expectations::create()
      -> one($logger)->add(any())
      -> allowing($logger)
      );
    
    $plugin = $this->_createPlugin($logger);
    try
    {
      $plugin->exceptionThrown($evt);
      $this->fail('Exception should bubble up.');
    }
    catch (Swift_TransportException $ex)
    {
    }
  }
  
  // -- Creation Methods
  
  private function _createLogger()
  {
    return $this->_mock('Swift_Plugins_Logger');
  }
  
  private function _createPlugin($logger)
  {
    return new Swift_Plugins_LoggerPlugin($logger);
  }
  
  private function _createCommandEvent($command)
  {
    $evt = $this->_mock('Swift_Events_CommandEvent');
    $this->_checking(Expectations::create()
      -> ignoring($evt)->getCommand() -> returns($command)
      -> ignoring($evt)
      );
    return $evt;
  }
  
  private function _createResponseEvent($response)
  {
    $evt = $this->_mock('Swift_Events_ResponseEvent');
    $this->_checking(Expectations::create()
      -> ignoring($evt)->getResponse() -> returns($response)
      -> ignoring($evt)
      );
    return $evt;
  }
  
  private function _createTransport()
  {
    return $this->_mock('Swift_Transport');
  }
  
  private function _createTransportChangeEvent()
  {
    $evt = $this->_mock('Swift_Events_TransportChangeEvent');
    $this->_checking(Expectations::create()
      -> ignoring($evt)->getSource() -> returns($this->_createTransport())
      -> ignoring($evt)
      );
    return $evt;
  }
  
  private function _createTransportExceptionEvent()
  {
    $evt = $this->_mock('Swift_Events_TransportExceptionEvent');
    $this->_checking(Expectations::create()
      -> ignoring($evt)->getException() -> returns(new Swift_TransportException(''))
      -> ignoring($evt)
      );
    return $evt;
  }
  
}
