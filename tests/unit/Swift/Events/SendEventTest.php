<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Events/SendEvent.php';
require_once 'Swift/Mime/Message.php';
require_once 'Swift/Transport.php';

class Swift_Events_SendEventTest extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testMessageCanBeFetchedViaGetter()
  {
    $message = $this->_createMessage();
    $transport = $this->_createTransport();
    
    $evt = $this->_createEvent($transport, $message);
    
    $ref = $evt->getMessage();
    $this->assertReference($message, $ref,
      '%s: Message should be returned from getMessage()'
      );
  }
  
  public function testTransportCanBeFetchViaGetter()
  {
    $message = $this->_createMessage();
    $transport = $this->_createTransport();
    
    $evt = $this->_createEvent($transport, $message);
    
    $ref = $evt->getTransport();
    $this->assertReference($transport, $ref,
      '%s: Transport should be returned from getTransport()'
      );
  }
  
  public function testTransportCanBeFetchViaGetSource()
  {
    $message = $this->_createMessage();
    $transport = $this->_createTransport();
    
    $evt = $this->_createEvent($transport, $message);
    
    $ref = $evt->getSource();
    $this->assertReference($transport, $ref,
      '%s: Transport should be returned from getSource()'
      );
  }
  
  public function testResultCanBeSetAndGet()
  {
    $message = $this->_createMessage();
    $transport = $this->_createTransport();
    
    $evt = $this->_createEvent($transport, $message);
    
    $evt->setResult(
      Swift_Events_SendEvent::RESULT_SUCCESS | Swift_Events_SendEvent::RESULT_TENTATIVE
      );
    
    $this->assertTrue($evt->getResult() & Swift_Events_SendEvent::RESULT_SUCCESS);
    $this->assertTrue($evt->getResult() & Swift_Events_SendEvent::RESULT_TENTATIVE);
  }
  
  public function testFailedRecipientsCanBeSetAndGet()
  {
    $message = $this->_createMessage();
    $transport = $this->_createTransport();
    
    $evt = $this->_createEvent($transport, $message);
    
    $evt->setFailedRecipients(array('foo@bar', 'zip@button'));
    
    $this->assertEqual(array('foo@bar', 'zip@button'), $evt->getFailedRecipients(),
      '%s: FailedRecipients should be returned from getter'
      );
  }
  
  // -- Creation Methods
  
  private function _createEvent(Swift_Transport $source,
    Swift_Mime_Message $message)
  {
    return new Swift_Events_SendEvent($source, $message);
  }
  
  private function _createTransport()
  {
    return $this->_stub('Swift_Transport');
  }
  
  private function _createMessage()
  {
    return $this->_stub('Swift_Mime_Message');
  }
  
}