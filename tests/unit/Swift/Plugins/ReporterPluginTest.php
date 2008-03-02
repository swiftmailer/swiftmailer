<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Plugins/ReporterPlugin.php';
require_once 'Swift/Plugins/Reporter.php';
require_once 'Swift/Mime/Message.php';
require_once 'Swift/Events/SendEvent.php';

Mock::generate('Swift_Mime_Message', 'Swift_Mime_MockMessage');
Mock::generate('Swift_Events_SendEvent', 'Swift_Events_MockSendEvent');
Mock::generate('Swift_Plugins_Reporter', 'Swift_Plugins_MockReporter');

class Swift_Plugins_ReporterPluginTest extends Swift_Tests_SwiftUnitTestCase
{

  public function testReportingPasses()
  {
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getTo', array('foo@bar.tld' => 'Foo'));
    
    $evt = new Swift_Events_MockSendEvent();
    $evt->setReturnValue('getMessage', $message);
    $evt->setReturnValue('getFailedRecipients', array()); //All passed
    
    $reporter = new Swift_Plugins_MockReporter();
    $reporter->expectOnce('notify',
      array($message, 'foo@bar.tld', Swift_Plugins_Reporter::RESULT_PASS)
      );
    
    $plugin = new Swift_Plugins_ReporterPlugin($reporter);
    $plugin->sendPerformed($evt);
  }
  
  public function testReportingFailedTo()
  {
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getTo', array(
      'foo@bar.tld' => 'Foo',
      'zip@button' => 'Zip'
      ));
    
    $evt = new Swift_Events_MockSendEvent();
    $evt->setReturnValue('getMessage', $message);
    $evt->setReturnValue('getFailedRecipients', array('zip@button'));
    
    $reporter = new Swift_Plugins_MockReporter();
    $reporter->expectAt(0, 'notify',
      array($message, 'foo@bar.tld', Swift_Plugins_Reporter::RESULT_PASS)
      );
    $reporter->expectAt(1, 'notify',
      array($message, 'zip@button', Swift_Plugins_Reporter::RESULT_FAIL)
      );
    $reporter->expectCallCount('notify', 2);
    
    $plugin = new Swift_Plugins_ReporterPlugin($reporter);
    $plugin->sendPerformed($evt);
  }
  
  public function testReportingFailedCc()
  {
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getTo', array('foo@bar.tld' => 'Foo'));
    $message->setReturnValue('getCc', array(
      'zip@button' => 'Zip',
      'test@test.com' => null
      ));
    
    $evt = new Swift_Events_MockSendEvent();
    $evt->setReturnValue('getMessage', $message);
    $evt->setReturnValue('getFailedRecipients', array('zip@button'));
    
    $reporter = new Swift_Plugins_MockReporter();
    $reporter->expectAt(0, 'notify',
      array($message, 'foo@bar.tld', Swift_Plugins_Reporter::RESULT_PASS)
      );
    $reporter->expectAt(1, 'notify',
      array($message, 'zip@button', Swift_Plugins_Reporter::RESULT_FAIL)
      );
    $reporter->expectAt(2, 'notify',
      array($message, 'test@test.com', Swift_Plugins_Reporter::RESULT_PASS)
      );
    $reporter->expectCallCount('notify', 3);
    
    $plugin = new Swift_Plugins_ReporterPlugin($reporter);
    $plugin->sendPerformed($evt);
  }
  
  public function testReportingFailedBcc()
  {
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getTo', array('foo@bar.tld' => 'Foo'));
    $message->setReturnValue('getBcc', array(
      'zip@button' => 'Zip',
      'test@test.com' => null
      ));
    
    $evt = new Swift_Events_MockSendEvent();
    $evt->setReturnValue('getMessage', $message);
    $evt->setReturnValue('getFailedRecipients', array('zip@button'));
    
    $reporter = new Swift_Plugins_MockReporter();
    $reporter->expectAt(0, 'notify',
      array($message, 'foo@bar.tld', Swift_Plugins_Reporter::RESULT_PASS)
      );
    $reporter->expectAt(1, 'notify',
      array($message, 'zip@button', Swift_Plugins_Reporter::RESULT_FAIL)
      );
    $reporter->expectAt(2, 'notify',
      array($message, 'test@test.com', Swift_Plugins_Reporter::RESULT_PASS)
      );
    $reporter->expectCallCount('notify', 3);
    
    $plugin = new Swift_Plugins_ReporterPlugin($reporter);
    $plugin->sendPerformed($evt);
  }
  
}
