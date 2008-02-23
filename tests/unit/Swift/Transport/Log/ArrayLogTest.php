<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/Log/ArrayLog.php';

class Swift_Transport_Log_ArrayLogTest extends Swift_Tests_SwiftUnitTestCase
{

  private $_log;
  
  public function setUp()
  {
    $this->_log = new Swift_Transport_Log_ArrayLog();
  }
  
  public function testNothingIsAddedIfNotEnabled()
  {
    $this->_log->setLogEnabled(false);
    $this->_log->addLogEntry('foo');
    $this->_log->addLogEntry('bar');
    
    $this->assertNoPattern('/foo/', $this->_log->dump());
    $this->assertNoPattern('/bar/', $this->_log->dump());
  }
  
  public function testLogEntriesAreAddedIfEnabled()
  {
    $this->_log->setLogEnabled(true);
    $this->_log->addLogEntry('foo');
    $this->_log->addLogEntry('bar');
    
    $this->assertPattern('/foo/', $this->_log->dump());
    $this->assertPattern('/bar/', $this->_log->dump());
  }
  
  public function testLogCanBeCleared()
  {
    $this->_log->setLogEnabled(true);
    $this->_log->addLogEntry('foo');
    $this->_log->addLogEntry('bar');
    
    $this->_log->clearLog();
    
    $this->_log->addLogEntry('test');
    
    $this->assertNoPattern('/foo/', $this->_log->dump());
    $this->assertNoPattern('/bar/', $this->_log->dump());
    
    $this->assertPattern('/test/', $this->_log->dump());
  }
  
}
