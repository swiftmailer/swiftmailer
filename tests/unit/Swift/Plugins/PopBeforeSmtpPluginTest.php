<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Plugins/AntiFloodPlugin.php';
require_once 'Swift/Events/TransportChangeEvent.php';
require_once 'Swift/Transport.php';

class Swift_Plugins_PopBeforeSmtpPluginTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testPluginConnectsToPop3HostBeforeTransportStarts()
  {
    $connection = $this->_createConnection();
    
    $plugin = $this->_createPlugin('pop.host.tld', 110);
    $plugin->setConnection($connection);
    
    $transport = $this->_createTransport();
    $evt = $this->_createTransportChangeEvent($transport);
    
    $this->_checking(Expectations::create()
      -> one($connection)->connect()
      -> ignoring($connection)
    );
    
    $plugin->beforeTransportStarted($evt);
  }
  
  public function testPluginDisconnectsFromPop3HostBeforeTransportStarts()
  {
    $connection = $this->_createConnection();
    
    $plugin = $this->_createPlugin('pop.host.tld', 110);
    $plugin->setConnection($connection);
    
    $transport = $this->_createTransport();
    $evt = $this->_createTransportChangeEvent($transport);
    
    $this->_checking(Expectations::create()
      -> one($connection)->disconnect()
      -> ignoring($connection)
    );
    
    $plugin->beforeTransportStarted($evt);
  }
  
  public function testPluginDoesNotConnectToSmtpIfBoundToDifferentTransport()
  {
    $connection = $this->_createConnection();
    
    $smtp = $this->_createTransport();
    
    $plugin = $this->_createPlugin('pop.host.tld', 110);
    $plugin->setConnection($connection);
    $plugin->bindSmtp($smtp);
    
    $transport = $this->_createTransport();
    $evt = $this->_createTransportChangeEvent($transport);
    
    $this->_checking(Expectations::create()
      -> never($connection)
    );
    
    $plugin->beforeTransportStarted($evt);
  }
  
  public function testPluginCanBindToSpecificTransport()
  {
    $connection = $this->_createConnection();
    
    $smtp = $this->_createTransport();
    
    $plugin = $this->_createPlugin('pop.host.tld', 110);
    $plugin->setConnection($connection);
    $plugin->bindSmtp($smtp);
    
    $evt = $this->_createTransportChangeEvent($smtp);
    
    $this->_checking(Expectations::create()
      -> one($connection)->connect()
      -> ignoring($connection)
    );
    
    $plugin->beforeTransportStarted($evt);
  }
  
  // -- Creation Methods
  
  private function _createTransport()
  {
    return $this->_mock('Swift_Transport');
  }
  
  private function _createTransportChangeEvent($transport)
  {
    $evt = $this->_mock('Swift_Events_TransportChangeEvent');
    $this->_checking(Expectations::create()
      -> ignoring($evt)->getSource() -> returns($transport)
      -> ignoring($evt)->getTransport() -> returns($transport)
      );
    return $evt;
  }
  
  public function _createConnection()
  {
    return $this->_mock('Swift_Plugins_Pop_Pop3Connection');
  }
  
  public function _createPlugin($host, $port, $crypto = null)
  {
    return new Swift_Plugins_PopBeforeSmtpPlugin($host, $port, $crypto);
  }
  
}
