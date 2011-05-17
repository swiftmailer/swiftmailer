<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Mailer.php';
require_once 'Swift/RfcComplianceException.php';
require_once 'Swift/Transport.php';
require_once 'Swift/Mime/Message.php';
require_once 'Swift/Mailer/RecipientIterator.php';
require_once 'Swift/Events/EventListener.php';

class Swift_MailerTest extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testTransportIsStartedWhenSending()
  {
    $transport = $this->_createTransport();
    $message = $this->_createMessage();
    $con = $this->_states('Connection')->startsAs('off');
    $this->_checking(Expectations::create()
      -> allowing($transport)->isStarted() -> returns(false) -> when($con->is('off'))
      -> allowing($transport)->isStarted() -> returns(false) -> when($con->is('on'))
      -> one($transport)->start() -> when($con->is('off')) -> then($con->is('on'))
      -> ignoring($transport)
      -> ignoring($message)
      );
      
    $mailer = $this->_createMailer($transport);
    $mailer->send($message);
  }
  
  public function testTransportIsOnlyStartedOnce()
  {
    $transport = $this->_createTransport();
    $message = $this->_createMessage();
    $con = $this->_states('Connection')->startsAs('off');
    $this->_checking(Expectations::create()
      -> allowing($transport)->isStarted() -> returns(false) -> when($con->is('off'))
      -> allowing($transport)->isStarted() -> returns(false) -> when($con->is('on'))
      -> one($transport)->start() -> when($con->is('off')) -> then($con->is('on'))
      -> ignoring($transport)
      -> ignoring($message)
      ); 
    $mailer = $this->_createMailer($transport);
    for ($i = 0; $i < 10; ++$i)
    {
      $mailer->send($message);
    }
  }
  
  public function testMessageIsPassedToTransport()
  {
    $transport = $this->_createTransport();
    $message = $this->_createMessage();
    $this->_checking(Expectations::create()
      -> one($transport)->send($message, optional())
      -> ignoring($transport)
      -> ignoring($message)
      );
      
    $mailer = $this->_createMailer($transport);
    $mailer->send($message);
  }
  
  public function testSendReturnsCountFromTransport()
  {
    $transport = $this->_createTransport();
    $message = $this->_createMessage();
    $this->_checking(Expectations::create()
      -> one($transport)->send($message, optional()) -> returns(57)
      -> ignoring($transport)
      -> ignoring($message)
      );
      
    $mailer = $this->_createMailer($transport);
    $this->assertEqual(57, $mailer->send($message));
  }
  
  public function testFailedRecipientReferenceIsPassedToTransport()
  {
    $failures = array();
    
    $transport = $this->_createTransport();
    $message = $this->_createMessage();
    $this->_checking(Expectations::create()
      -> one($transport)->send($message, reference($failures))
      -> ignoring($transport)
      -> ignoring($message)
      );
    
    $mailer = $this->_createMailer($transport);
    $mailer->send($message, $failures);
  }
  
  public function testSendRecordsRfcComplianceExceptionAsEntireSendFailure()
  {
    $failures = array();
    
    $rfcException = new Swift_RfcComplianceException('test');
    $transport = $this->_createTransport();
    $message = $this->_createMessage();
    $this->_checking(Expectations::create()
      -> allowing($message)->getTo() -> returns(array('foo&invalid' => 'Foo', 'bar@valid.tld' => 'Bar'))
      -> one($transport)->send($message, reference($failures)) -> throws($rfcException)
      -> ignoring($transport)
      -> ignoring($message)
      );
    
    $mailer = $this->_createMailer($transport);
    $this->assertEqual(0, $mailer->send($message, $failures), '%s: Should return 0');
    $this->assertEqual(array('foo&invalid', 'bar@valid.tld'), $failures, '%s: Failures should contain all addresses since the entire message failed to compile');
  }
  
  public function testRegisterPluginDelegatesToTransport()
  {
    $plugin = $this->_createPlugin();
    $transport = $this->_createTransport();
    $mailer = $this->_createMailer($transport);
    
    $this->_checking(Expectations::create()
      -> one($transport)->registerPlugin($plugin)
      );
    $mailer->registerPlugin($plugin);
  }
  
  // -- Creation methods
  
  private function _createPlugin()
  {
    return $this->_mock('Swift_Events_EventListener');
  }
  
  private function _createTransport()
  {
    return $this->_mock('Swift_Transport');
  }
  
  private function _createMessage()
  {
    return $this->_mock('Swift_Mime_Message');
  }
  
  private function _createIterator()
  {
    return $this->_mock('Swift_Mailer_RecipientIterator');
  }
  
  private function _createMailer(Swift_Transport $transport)
  {
    return new Swift_Mailer($transport);
  }
  
}
