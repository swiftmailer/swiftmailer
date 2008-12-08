<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Plugins/ReporterPlugin.php';
require_once 'Swift/Plugins/Reporter.php';
require_once 'Swift/Mime/Message.php';
require_once 'Swift/Events/SendEvent.php';

class Swift_Plugins_ReporterPluginTest extends Swift_Tests_SwiftUnitTestCase
{

  public function testReportingPasses()
  {
    $message = $this->_createMessage();
    $evt = $this->_createSendEvent();
    $reporter = $this->_createReporter();
    
    $this->_checking(Expectations::create()
      -> allowing($message)->getTo() -> returns(array('foo@bar.tld' => 'Foo'))
      -> allowing($evt)->getMessage() -> returns($message)
      -> allowing($evt)->getFailedRecipients() -> returns(array())
      -> one($reporter)->notify($message, 'foo@bar.tld', Swift_Plugins_Reporter::RESULT_PASS)
      -> ignoring($message)
      -> ignoring($evt)
      );
    
    $plugin = new Swift_Plugins_ReporterPlugin($reporter);
    $plugin->sendPerformed($evt);
  }
  
  public function testReportingFailedTo()
  {
    $message = $this->_createMessage();
    $evt = $this->_createSendEvent();
    $reporter = $this->_createReporter();
    
    $this->_checking(Expectations::create()
      -> allowing($message)->getTo() -> returns(array(
        'foo@bar.tld' => 'Foo', 'zip@button' => 'Zip'
        ))
      -> allowing($evt)->getMessage() -> returns($message)
      -> allowing($evt)->getFailedRecipients() -> returns(array('zip@button'))
      -> one($reporter)->notify($message, 'foo@bar.tld', Swift_Plugins_Reporter::RESULT_PASS)
      -> one($reporter)->notify($message, 'zip@button', Swift_Plugins_Reporter::RESULT_FAIL)
      -> ignoring($message)
      -> ignoring($evt)
      );
    
    $plugin = new Swift_Plugins_ReporterPlugin($reporter);
    $plugin->sendPerformed($evt);
  }
  
  public function testReportingFailedCc()
  {
    $message = $this->_createMessage();
    $evt = $this->_createSendEvent();
    $reporter = $this->_createReporter();
    
    $this->_checking(Expectations::create()
      -> allowing($message)->getTo() -> returns(array(
        'foo@bar.tld' => 'Foo'
        ))
      -> allowing($message)->getCc() -> returns(array(
        'zip@button' => 'Zip', 'test@test.com' => 'Test'
        ))
      -> allowing($evt)->getMessage() -> returns($message)
      -> allowing($evt)->getFailedRecipients() -> returns(array('zip@button'))
      -> one($reporter)->notify($message, 'foo@bar.tld', Swift_Plugins_Reporter::RESULT_PASS)
      -> one($reporter)->notify($message, 'zip@button', Swift_Plugins_Reporter::RESULT_FAIL)
      -> one($reporter)->notify($message, 'test@test.com', Swift_Plugins_Reporter::RESULT_PASS)
      -> ignoring($message)
      -> ignoring($evt)
      );
    
    $plugin = new Swift_Plugins_ReporterPlugin($reporter);
    $plugin->sendPerformed($evt);
  }
  
  public function testReportingFailedBcc()
  {
    $message = $this->_createMessage();
    $evt = $this->_createSendEvent();
    $reporter = $this->_createReporter();
    
    $this->_checking(Expectations::create()
      -> allowing($message)->getTo() -> returns(array(
        'foo@bar.tld' => 'Foo'
        ))
      -> allowing($message)->getBcc() -> returns(array(
        'zip@button' => 'Zip', 'test@test.com' => 'Test'
        ))
      -> allowing($evt)->getMessage() -> returns($message)
      -> allowing($evt)->getFailedRecipients() -> returns(array('zip@button'))
      -> one($reporter)->notify($message, 'foo@bar.tld', Swift_Plugins_Reporter::RESULT_PASS)
      -> one($reporter)->notify($message, 'zip@button', Swift_Plugins_Reporter::RESULT_FAIL)
      -> one($reporter)->notify($message, 'test@test.com', Swift_Plugins_Reporter::RESULT_PASS)
      -> ignoring($message)
      -> ignoring($evt)
      );
    
    $plugin = new Swift_Plugins_ReporterPlugin($reporter);
    $plugin->sendPerformed($evt);
  }
  
  // -- Creation Methods
  
  private function _createMessage()
  {
    return $this->_mock('Swift_Mime_Message');
  }
  
  private function _createSendEvent()
  {
    return $this->_mock('Swift_Events_SendEvent');
  }
  
  private function _createReporter()
  {
    return $this->_mock('Swift_Plugins_Reporter');
  }
  
}
