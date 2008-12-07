<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Plugins/Reporters/HitReporter.php';
require_once 'Swift/Mime/Message.php';

class Swift_Plugins_Reporters_HitReporterTest
  extends Swift_Tests_SwiftUnitTestCase
{

  private $_hitReporter;
  private $_message;
  
  public function setUp()
  {
    $this->_hitReporter = new Swift_Plugins_Reporters_HitReporter();
    $this->_message = $this->_mock('Swift_Mime_Message');
  }
  
  public function testReportingFail()
  {
    $this->_hitReporter->notify($this->_message, 'foo@bar.tld',
      Swift_Plugins_Reporter::RESULT_FAIL
      );
    $this->assertEqual(array('foo@bar.tld'),
      $this->_hitReporter->getFailedRecipients()
      );
  }
  
  public function testMultipleReports()
  {
    $this->_hitReporter->notify($this->_message, 'foo@bar.tld',
      Swift_Plugins_Reporter::RESULT_FAIL
      );
    $this->_hitReporter->notify($this->_message, 'zip@button',
      Swift_Plugins_Reporter::RESULT_FAIL
      );
    $this->assertEqual(array('foo@bar.tld', 'zip@button'),
      $this->_hitReporter->getFailedRecipients()
      );
  }
  
  public function testReportingPassIsIgnored()
  {
    $this->_hitReporter->notify($this->_message, 'foo@bar.tld',
      Swift_Plugins_Reporter::RESULT_FAIL
      );
    $this->_hitReporter->notify($this->_message, 'zip@button',
      Swift_Plugins_Reporter::RESULT_PASS
      );
    $this->assertEqual(array('foo@bar.tld'),
      $this->_hitReporter->getFailedRecipients()
      );
  }
  
  public function testBufferCanBeCleared()
  {
    $this->_hitReporter->notify($this->_message, 'foo@bar.tld',
      Swift_Plugins_Reporter::RESULT_FAIL
      );
    $this->_hitReporter->notify($this->_message, 'zip@button',
      Swift_Plugins_Reporter::RESULT_FAIL
      );
    $this->assertEqual(array('foo@bar.tld', 'zip@button'),
      $this->_hitReporter->getFailedRecipients()
      );
    $this->_hitReporter->clear();
    $this->assertEqual(array(), $this->_hitReporter->getFailedRecipients());
  }
  
}
