<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Plugins/HitReporter.php';
require_once 'Swift/Mime/Message.php';

Mock::generate('Swift_Mime_Message', 'Swift_Mime_MockMessage');

class Swift_Plugins_HitReporterTest extends Swift_Tests_SwiftUnitTestCase
{

  private $_hitReporter;
  
  public function setUp()
  {
    $this->_hitReporter = new Swift_Plugins_HitReporter();
  }
  
  public function testReportingFail()
  {
    $this->_hitReporter->notify(new Swift_Mime_MockMessage(), 'foo@bar.tld',
      Swift_Plugins_Reporter::RESULT_FAIL
      );
    $this->assertEqual(array('foo@bar.tld'),
      $this->_hitReporter->getFailedRecipients()
      );
  }
  
  public function testMultipleReports()
  {
    $this->_hitReporter->notify(new Swift_Mime_MockMessage(), 'foo@bar.tld',
      Swift_Plugins_Reporter::RESULT_FAIL
      );
    $this->_hitReporter->notify(new Swift_Mime_MockMessage(), 'zip@button',
      Swift_Plugins_Reporter::RESULT_FAIL
      );
    $this->assertEqual(array('foo@bar.tld', 'zip@button'),
      $this->_hitReporter->getFailedRecipients()
      );
  }
  
  public function testReportingPassIsIgnored()
  {
    $this->_hitReporter->notify(new Swift_Mime_MockMessage(), 'foo@bar.tld',
      Swift_Plugins_Reporter::RESULT_FAIL
      );
    $this->_hitReporter->notify(new Swift_Mime_MockMessage(), 'zip@button',
      Swift_Plugins_Reporter::RESULT_PASS
      );
    $this->assertEqual(array('foo@bar.tld'),
      $this->_hitReporter->getFailedRecipients()
      );
  }
  
  public function testBufferCanBeCleared()
  {
    $this->_hitReporter->notify(new Swift_Mime_MockMessage(), 'foo@bar.tld',
      Swift_Plugins_Reporter::RESULT_FAIL
      );
    $this->_hitReporter->notify(new Swift_Mime_MockMessage(), 'zip@button',
      Swift_Plugins_Reporter::RESULT_FAIL
      );
    $this->assertEqual(array('foo@bar.tld', 'zip@button'),
      $this->_hitReporter->getFailedRecipients()
      );
    $this->_hitReporter->clear();
    $this->assertEqual(array(), $this->_hitReporter->getFailedRecipients());
  }
  
}
