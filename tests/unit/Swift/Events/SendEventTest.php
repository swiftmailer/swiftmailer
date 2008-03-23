<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Events/SendEvent.php';
require_once 'Swift/Mime/Message.php';
require_once 'Swift/Transport.php';

class Swift_Events_SendEventTest extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testMessageCanBeInjected()
  {
    $context = new Mockery();
    $message = $context->mock('Swift_Mime_Message');
    
    $evt = new Swift_Events_SendEvent();
    $evt->message = $message;
    
    $this->assertReference($message, $evt->getMessage(),
      '%s: Message should be injectable'
      );
  }
  
  public function testResultCanBeInjected()
  {
    $evt = new Swift_Events_SendEvent();
    $evt->result = (
      Swift_Events_SendEvent::RESULT_SUCCESS | Swift_Events_SendEvent::RESULT_TENTATIVE
      );
    
    $this->assertTrue($evt->getResult() & Swift_Events_SendEvent::RESULT_SUCCESS);
    $this->assertTrue($evt->getResult() & Swift_Events_SendEvent::RESULT_TENTATIVE);
  }
  
  public function testFailedRecipientsCanBeInjected()
  {
    $evt = new Swift_Events_SendEvent();
    $evt->failedRecipients = array('foo@bar', 'zip@button');
    
    $this->assertEqual(array('foo@bar', 'zip@button'), $evt->getFailedRecipients(),
      '%s: FailedRecipients should be injectable'
      );
  }
  
  public function testTransportCanBeInjected()
  {
    $context = new Mockery();
    $transport = $context->mock('Swift_Transport');
    
    $evt = new Swift_Events_SendEvent();
    $evt->transport = $transport;
    
    $this->assertReference($transport, $evt->getTransport(),
      '%s: Transport should be injectable'
      );
  }
  
  public function testCloneIsGeneratedWithCleanDefaults()
  {
    $context = new Mockery();
    $transport = $context->mock('Swift_Transport');
    $message = $context->mock('Swift_Mime_Message');
    
    $evt = new Swift_Events_SendEvent();
    $evt->message = $message;
    $evt->transport = $transport;
    $evt->failedRecipients = array('foo@bar', 'zip@button');
    $evt->result = Swift_Events_SendEvent::RESULT_FAILED;
    
    $obj = new stdClass();
    
    $cloned = $evt->cloneFor($obj);
    
    $this->assertReference($obj, $cloned->getSource());
    $this->assertEqual(Swift_Events_SendEvent::RESULT_PENDING, $cloned->getResult());
    $this->assertEqual(array(), $cloned->getFailedRecipients());
    $this->assertNull($cloned->getMessage());
    $this->assertNull($cloned->getTransport());
  }
  
}