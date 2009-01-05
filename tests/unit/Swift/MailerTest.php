<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Mailer.php';
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
  
  public function testBatchSendSendsOneMessagePerRecipient()
  {
    $transport = $this->_createTransport();
    $message = $this->_createMessage();
    $this->_checking(Expectations::create()
      -> allowing($message)->getTo() -> returns(array(
        'one@domain.com' => 'One',
        'two@domain.com' => 'Two',
        'three@domain.com' => 'Three'
        ))
      -> exactly(3)->of($transport)->send($message, optional())
      -> ignoring($transport)
      -> ignoring($message)
      );
      
    $mailer = $this->_createMailer($transport);
    $mailer->batchSend($message);
  }
  
  public function testBatchSendChangesToFieldForEachRecipientBeforeRestoring()
  {
    $transport = $this->_createTransport();
    $message = $this->_createMessage();
    $this->_checking(Expectations::create()
      -> allowing($message)->getTo() -> returns(array(
        'one@domain.com' => 'One',
        'two@domain.com' => 'Two',
        'three@domain.com' => 'Three'
        ))
      -> one($message)->setTo(array('one@domain.com' => 'One'))
      -> one($message)->setTo(array('two@domain.com' => 'Two'))
      -> one($message)->setTo(array('three@domain.com' => 'Three'))
      -> one($message)->setTo(array( //Restore message
        'one@domain.com' => 'One',
        'two@domain.com' => 'Two',
        'three@domain.com' => 'Three'
        ))
      -> exactly(3)->of($transport)->send($message, optional())
      -> ignoring($transport)
      -> ignoring($message)
      );
      
    $mailer = $this->_createMailer($transport);
    $mailer->batchSend($message);
  }
  
  public function testBatchSendClearsAndRestoresCc()
  {
    $transport = $this->_createTransport();
    $message = $this->_createMessage();
    $this->_checking(Expectations::create()
      -> allowing($message)->getTo() -> returns(array('one@domain.com' => 'One'))
      -> allowing($message)->getCc() -> returns(array('four@domain.com' => 'Four'))
      -> one($message)->setCc(array())
      -> one($message)->setCc(array('four@domain.com' => 'Four'))
      -> one($transport)->send($message, optional())
      -> never($transport)->send($message, optional()) //Only To: recipients in batch!
      -> ignoring($transport)
      -> ignoring($message)
      );
      
    $mailer = $this->_createMailer($transport);
    $mailer->batchSend($message);
  }
  
  public function testBatchSendClearsAndRestoresBcc()
  {
    $transport = $this->_createTransport();
    $message = $this->_createMessage();
    $this->_checking(Expectations::create()
      -> allowing($message)->getTo() -> returns(array('one@domain.com' => 'One'))
      -> allowing($message)->getBcc() -> returns(array('four@domain.com' => 'Four'))
      -> one($message)->setBcc(array())
      -> one($message)->setBcc(array('four@domain.com' => 'Four'))
      -> one($transport)->send($message, optional())
      -> never($transport)->send($message, optional()) //Only To: recipients in batch!
      -> ignoring($transport)
      -> ignoring($message)
      );
      
    $mailer = $this->_createMailer($transport);
    $mailer->batchSend($message);
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
  
  public function testBatchSendReturnCummulativeSendCount()
  {
    $transport = $this->_createTransport();
    $message = $this->_createMessage();
    $this->_checking(Expectations::create()
      -> allowing($message)->getTo() -> returns(array(
        'one@domain.com' => 'One',
        'two@domain.com' => 'Two',
        'three@domain.com' => 'Three'
        ))
      -> one($message)->setTo(array('one@domain.com' => 'One'))
      -> one($message)->setTo(array('two@domain.com' => 'Two'))
      -> one($message)->setTo(array('three@domain.com' => 'Three'))
      -> one($message)->setTo(array( //Restore message
        'one@domain.com' => 'One',
        'two@domain.com' => 'Two',
        'three@domain.com' => 'Three'
        ))
      -> one($transport)->send($message, optional()) -> returns(1)
      -> one($transport)->send($message, optional()) -> returns(0)
      -> one($transport)->send($message, optional()) -> returns(1)
      -> ignoring($transport)
      -> ignoring($message)
      );
      
    $mailer = $this->_createMailer($transport);
    $this->assertEqual(2, $mailer->batchSend($message));
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
  
  public function testFailedRecipientReferenceIsPassedToTransportInBatch()
  {
    $failures = array();
    
    $transport = $this->_createTransport();
    $message = $this->_createMessage();
    $this->_checking(Expectations::create()
      -> allowing($message)->getTo() -> returns(array('foo@bar' => 'Foo'))
      -> one($transport)->send($message, reference($failures))
      -> ignoring($transport)
      -> ignoring($message)
      );
    
    $mailer = $this->_createMailer($transport);
    $mailer->batchSend($message, $failures);
  }
  
  public function testBatchSendCanReadFromIterator()
  {
    $it = $this->_createIterator();
    $message = $this->_createMessage();
    $transport = $this->_createTransport();
    
    $this->_checking(Expectations::create()
      -> one($it)->hasNext() -> returns(true)
      -> one($it)->nextRecipient() -> returns(array('one@domain.com' => 'One'))
      -> one($it)->hasNext() -> returns(true)
      -> one($it)->nextRecipient() -> returns(array('two@domain.com' => 'Two'))
      -> one($it)->hasNext() -> returns(true)
      -> one($it)->nextRecipient() -> returns(array('three@domain.com' => 'Three'))
      -> allowing($it)->hasNext() -> returns(false)
      
      -> ignoring($message)->getTo() -> returns(array())
      -> one($message)->setTo(array('one@domain.com' => 'One'))
      -> one($message)->setTo(array('two@domain.com' => 'Two'))
      -> one($message)->setTo(array('three@domain.com' => 'Three'))
      -> one($message)->setTo(array()) //Message restoration to original state
      
      -> exactly(3)->of($transport)->send($message)
      
      -> ignoring($transport)
      -> ignoring($message)
      );
    
    $mailer = $this->_createMailer($transport);
    $mailer->batchSend($message, $failures, $it);
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
