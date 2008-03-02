<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Plugins/LoggerPlugin.php';
require_once 'Swift/Plugins/Logger.php';
require_once 'Swift/Events/CommandEvent.php';
require_once 'Swift/Events/ResponseEvent.php';
require_once 'Swift/Events/TransportChangeEvent.php';
require_once 'Swift/Transport.php';

Mock::generate('Swift_Transport', 'Swift_MockTransport');
Mock::generate('Swift_Plugins_Logger', 'Swift_Plugins_MockLogger');
Mock::generate('Swift_Events_CommandEvent', 'Swift_Events_MockCommandEvent');
Mock::generate('Swift_Events_ResponseEvent', 'Swift_Events_MockResponseEvent');
Mock::generate('Swift_Events_TransportChangeEvent', 'Swift_Events_MockTransportChangeEvent');

class Swift_Plugins_LoggerPluginTest extends Swift_Tests_SwiftUnitTestCase
{

  public function testLoggerDelegatesAddingEntries()
  {
    $logger = new Swift_Plugins_MockLogger();
    $logger->expectOnce('add', array('foo'));
    
    $plugin = new Swift_Plugins_LoggerPlugin($logger);
    $plugin->add('foo');
  }
  
  public function testLoggerDelegatesDumpingEntries()
  {
    $logger = new Swift_Plugins_MockLogger();
    $logger->expectOnce('dump');
    $logger->setReturnValue('dump', 'foobar');
    
    $plugin = new Swift_Plugins_LoggerPlugin($logger);
    $dump = $plugin->dump();
    $this->assertEqual('foobar', $dump);
  }
  
  public function testLoggerDelegatesClearingEntries()
  {
    $logger = new Swift_Plugins_MockLogger();
    $logger->expectOnce('clear');
    
    $plugin = new Swift_Plugins_LoggerPlugin($logger);
    $plugin->clear();
  }
  
  public function testCommandIsSentToLogger()
  {
    $evt = new Swift_Events_MockCommandEvent();
    $evt->setReturnValue('getCommand', "foo\r\n");
    
    $logger = new Swift_Plugins_MockLogger();
    $logger->expectOnce('add', array(new PatternExpectation('~foo\r\n~')));
    
    $plugin = new Swift_Plugins_LoggerPlugin($logger);
    $plugin->commandSent($evt);
  }
  
  public function testResponseIsSentToLogger()
  {
    $evt = new Swift_Events_MockResponseEvent();
    $evt->setReturnValue('getResponse', "354 Go ahead\r\n");
    
    $logger = new Swift_Plugins_MockLogger();
    $logger->expectOnce('add', array(new PatternExpectation('~354 Go ahead\r\n~')));
    
    $plugin = new Swift_Plugins_LoggerPlugin($logger);
    $plugin->responseReceived($evt);
  }
  
  public function testTransportStartChangeIsSentToLogger()
  {
    $transport = new Swift_MockTransport();
    
    $evt = new Swift_Events_MockTransportChangeEvent();
    $evt->setReturnValue('getSource', $transport);
    
    $logger = new Swift_Plugins_MockLogger();
    $logger->expectOnce('add');
    
    $plugin = new Swift_Plugins_LoggerPlugin($logger);
    $plugin->transportStarted($evt);
  }
  
  public function testTransportStopChangeIsSentToLogger()
  {
    $transport = new Swift_MockTransport();
    
    $evt = new Swift_Events_MockTransportChangeEvent();
    $evt->setReturnValue('getSource', $transport);
    
    $logger = new Swift_Plugins_MockLogger();
    $logger->expectOnce('add');
    
    $plugin = new Swift_Plugins_LoggerPlugin($logger);
    $plugin->transportStopped($evt);
  }
  
}
