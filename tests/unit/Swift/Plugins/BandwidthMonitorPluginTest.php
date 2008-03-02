<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Plugins/BandwidthMonitorPlugin.php';
require_once 'Swift/Events/SendEvent.php';
require_once 'Swift/Events/CommandEvent.php';
require_once 'Swift/Events/ResponseEvent.php';
require_once 'Swift/Mime/Message.php';

Mock::generate('Swift_Events_SendEvent', 'Swift_Events_MockSendEvent');
Mock::generate('Swift_Events_CommandEvent', 'Swift_Events_MockCommandEvent');
Mock::generate('Swift_Events_ResponseEvent', 'Swift_Events_MockResponseEvent');
Mock::generate('Swift_Mime_Message', 'Swift_Mime_MockMessage');

class Swift_Mime_MockMessageWriter extends Swift_Mime_MockMessage {
  public $write = array();
  public function toByteStream(Swift_InputByteStream $is) {
    foreach ($this->write as $chunk) {
      $is->write($chunk);
    }
  }
}

class Swift_Plugins_BandwidthMonitorPluginTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  public function setUp()
  {
    $this->_monitor = new Swift_Plugins_BandwidthMonitorPlugin();
  }
  
  public function testBytesOutIncreasesAccordingToMessageLength()
  {
    $message = new Swift_Mime_MockMessageWriter();
    $message->write = array('abc', 'def');
    
    $evt = new Swift_Events_MockSendEvent();
    $evt->setReturnValue('getMessage', $message);
    
    $this->assertEqual(0, $this->_monitor->getBytesOut());
    $this->_monitor->sendPerformed($evt);
    $this->assertEqual(6, $this->_monitor->getBytesOut());
    $this->_monitor->sendPerformed($evt);
    $this->assertEqual(12, $this->_monitor->getBytesOut());
  }
  
  public function testBytesOutIncreasesWhenCommandsSent()
  {
    $evt = new Swift_Events_MockCommandEvent();
    $evt->setReturnValue('getCommand', "RCPT TO: <foo@bar.com>\r\n");
    
    $this->assertEqual(0, $this->_monitor->getBytesOut());
    $this->_monitor->commandSent($evt);
    $this->assertEqual(24, $this->_monitor->getBytesOut());
    $this->_monitor->commandSent($evt);
    $this->assertEqual(48, $this->_monitor->getBytesOut());
  }
  
  public function testBytesInIncreasesWhenResponsesReceived()
  {
    $evt = new Swift_Events_MockResponseEvent();
    $evt->setReturnValue('getResponse', "250 Ok\r\n");
    
    $this->assertEqual(0, $this->_monitor->getBytesIn());
    $this->_monitor->responseReceived($evt);
    $this->assertEqual(8, $this->_monitor->getBytesIn());
    $this->_monitor->responseReceived($evt);
    $this->assertEqual(16, $this->_monitor->getBytesIn());
  }
  
  public function testCountersCanBeReset()
  {
    $evt = new Swift_Events_MockResponseEvent();
    $evt->setReturnValue('getResponse', "250 Ok\r\n");
    
    $this->assertEqual(0, $this->_monitor->getBytesIn());
    $this->_monitor->responseReceived($evt);
    $this->assertEqual(8, $this->_monitor->getBytesIn());
    $this->_monitor->responseReceived($evt);
    $this->assertEqual(16, $this->_monitor->getBytesIn());
    
    $evt = new Swift_Events_MockCommandEvent();
    $evt->setReturnValue('getCommand', "RCPT TO: <foo@bar.com>\r\n");
    
    $this->assertEqual(0, $this->_monitor->getBytesOut());
    $this->_monitor->commandSent($evt);
    $this->assertEqual(24, $this->_monitor->getBytesOut());
    $this->_monitor->commandSent($evt);
    $this->assertEqual(48, $this->_monitor->getBytesOut());
    
    $this->_monitor->reset();
    
    $this->assertEqual(0, $this->_monitor->getBytesOut());
    $this->assertEqual(0, $this->_monitor->getBytesIn());
  }
  
}