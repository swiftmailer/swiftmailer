<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Plugins/Reporters/HitReporter.php';
require_once 'Swift/Mime/Message.php';

class Swift_Plugins_Reporters_SentReporterTest
  extends Swift_Tests_SwiftUnitTestCase
{

  private $_sentReporter;
  private $_message;
  
  public function setUp()
  {
    $this->_sentReporter = new Swift_Plugins_Reporters_SentReporter();
    $this->_message = $this->_mock('Swift_Mime_Message');
  }

  function createEvent($address, $result) {
    $this->_sentReporter->notify($this->_message, $address,$result );
  }
  function createFailure($address) {
    $this->createEvent($address, Swift_Plugins_Reporter::RESULT_FAIL);
  }
  function createSuccess($address) {
    $this->createEvent($address, Swift_Plugins_Reporter::RESULT_PASS);
  }
  
  public function testReportingFail()
  {
    $this->createFailure("foo@bar.ltd");
    $failures = $this->_sentReporter->getFailures();

    $this->assertEqual('foo@bar.ltd',$failures[0]->address);
  }
  public function testReportingSuccess()
  {
    $this->createSuccess("soo@bar.ltd");
    $successes = $this->_sentReporter->getSuccesses();
    $this->assertEqual('soo@bar.ltd',$successes[0]->address);
  }
  
  public function testMultipleReports()
  {

    $this->createFailure("foo@bar.ltd");
    $this->createFailure("zip@button");
    $this->assertEqual(2, $this->_sentReporter->getSendCount());
    $failures = $this->_sentReporter->getFailures();

    $this->assertEqual('foo@bar.ltd',$failures[0]->address);
    $this->assertEqual('zip@button',$failures[1]->address);
  }
  
  public function testReportingPassIsIgnored()
  {
    $this->_sentReporter->notify($this->_message, 'foo@bar.tld',
      Swift_Plugins_Reporter::RESULT_FAIL
      );
    $this->_sentReporter->notify($this->_message, 'zip@button',
      Swift_Plugins_Reporter::RESULT_PASS
      );
    $failures = $this->_sentReporter->getFailures();
    $this->assertEqual('foo@bar.tld',$failures[0]->address);
    $this->assertEqual(1,
      count($this->_sentReporter->getFailures())
      );
  }
  
  public function testBufferCanBeCleared()
  {
    $this->_sentReporter->notify($this->_message, 'foo@bar.tld',
      Swift_Plugins_Reporter::RESULT_FAIL
      );
    $this->_sentReporter->notify($this->_message, 'zip@button',
      Swift_Plugins_Reporter::RESULT_FAIL
      );
    $this->assertEqual(2 ,count( $this->_sentReporter->getFailures()));
    $this->_sentReporter->clear();
    $this->assertEqual(array(), $this->_sentReporter->getFailures());
    $this->assertEqual(0, $this->_sentReporter->getSendCount());
  }
  
}
