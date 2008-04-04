<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Mailer.php';
require_once 'Swift/Transport.php';
require_once 'Swift/Mime/Message.php';
require_once 'Swift/Mailer/RecipientIterator.php';

Mock::generate('Swift_Transport', 'Swift_Mailer_MockTransport');
Mock::generate('Swift_Mime_Message', 'Swift_Mime_MockMessage');
Mock::generate('Swift_Mailer_RecipientIterator', 'Swift_Mailer_MockRecipientIterator');

class Swift_MailerTest extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_transport;
  private $_mailer;
  
  public function setUp()
  {
    $this->_transport = new Swift_Mailer_MockTransport();
    $this->_mailer = new Swift_Mailer($this->_transport);
  }
  
  public function testTransportIsStartedWhenSending()
  {
    $context = new Mockery();
    $transport = $context->mock('Swift_Transport');
    $message = $context->mock('Swift_Mime_Message');
    $con = $context->states('Connection')->startsAs('off');
    $context->checking(Expectations::create()
      -> allowing($transport)->isStarted() -> returns(false) -> when($con->is('off'))
      -> allowing($transport)->isStarted() -> returns(false) -> when($con->is('on'))
      -> one($transport)->start() -> when($con->is('off')) -> then($con->is('on'))
      -> ignoring($transport)
      -> ignoring($message)
      );
      
    $mailer = new Swift_Mailer($transport);
    $mailer->send($message);
    
    $context->assertIsSatisfied();
  }
  
  public function testTransportIsOnlyStartedOnce()
  {
    $context = new Mockery();
    $transport = $context->mock('Swift_Transport');
    $message = $context->mock('Swift_Mime_Message');
    $con = $context->states('Connection')->startsAs('off');
    $context->checking(Expectations::create()
      -> allowing($transport)->isStarted() -> returns(false) -> when($con->is('off'))
      -> allowing($transport)->isStarted() -> returns(false) -> when($con->is('on'))
      -> one($transport)->start() -> when($con->is('off')) -> then($con->is('on'))
      -> ignoring($transport)
      -> ignoring($message)
      ); 
    $mailer = new Swift_Mailer($transport);
    for ($i = 0; $i < 10; ++$i)
    {
      $mailer->send($message);
    }
    $context->assertIsSatisfied();
  }
  
  public function testMessageIsPassedToTransport()
  {
    $context = new Mockery();
    $transport = $context->mock('Swift_Transport');
    $message = $context->mock('Swift_Mime_Message');
    $context->checking(Expectations::create()
      -> one($transport)->send($message, optional())
      -> ignoring($transport)
      -> ignoring($message)
      );
      
    $mailer = new Swift_Mailer($transport);
    $mailer->send($message);
    
    $context->assertIsSatisfied();
  }
  
  public function testBatchSendSendsOneMessagePerRecipient()
  {
    $context = new Mockery();
    $transport = $context->mock('Swift_Transport');
    $message = $context->mock('Swift_Mime_Message');
    $context->checking(Expectations::create()
      -> allowing($message)->getTo() -> returns(array(
        'one@domain.com' => 'One',
        'two@domain.com' => 'Two',
        'three@domain.com' => 'Three'
        ))
      -> exactly(3)->of($transport)->send($message, optional())
      -> ignoring($transport)
      -> ignoring($message)
      );
      
    $mailer = new Swift_Mailer($transport);
    $mailer->batchSend($message);
    
    $context->assertIsSatisfied();
  }
  
  public function testBatchSendChangesToFieldForEachRecipientBeforeRestoring()
  {
    $context = new Mockery();
    $transport = $context->mock('Swift_Transport');
    $message = $context->mock('Swift_Mime_Message');
    $context->checking(Expectations::create()
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
      
    $mailer = new Swift_Mailer($transport);
    $mailer->batchSend($message);
    
    $context->assertIsSatisfied();
  }
  
  public function testBatchSendClearsAndRestoresCc()
  {
    $context = new Mockery();
    $transport = $context->mock('Swift_Transport');
    $message = $context->mock('Swift_Mime_Message');
    $context->checking(Expectations::create()
      -> allowing($message)->getTo() -> returns(array('one@domain.com' => 'One'))
      -> allowing($message)->getCc() -> returns(array('four@domain.com' => 'Four'))
      -> one($message)->setCc(array())
      -> one($message)->setCc(array('four@domain.com' => 'Four'))
      -> one($transport)->send($message, optional())
      -> never($transport)->send($message, optional()) //Only To: recipients in batch!
      -> ignoring($transport)
      -> ignoring($message)
      );
      
    $mailer = new Swift_Mailer($transport);
    $mailer->batchSend($message);
    
    $context->assertIsSatisfied();
  }
  
  public function testBatchSendClearsAndRestoresBcc()
  {
    $context = new Mockery();
    $transport = $context->mock('Swift_Transport');
    $message = $context->mock('Swift_Mime_Message');
    $context->checking(Expectations::create()
      -> allowing($message)->getTo() -> returns(array('one@domain.com' => 'One'))
      -> allowing($message)->getBcc() -> returns(array('four@domain.com' => 'Four'))
      -> one($message)->setBcc(array())
      -> one($message)->setBcc(array('four@domain.com' => 'Four'))
      -> one($transport)->send($message, optional())
      -> never($transport)->send($message, optional()) //Only To: recipients in batch!
      -> ignoring($transport)
      -> ignoring($message)
      );
      
    $mailer = new Swift_Mailer($transport);
    $mailer->batchSend($message);
    
    $context->assertIsSatisfied();
  }
  
  public function testSendReturnsCountFromTransport()
  {
    $context = new Mockery();
    $transport = $context->mock('Swift_Transport');
    $message = $context->mock('Swift_Mime_Message');
    $context->checking(Expectations::create()
      -> one($transport)->send($message, optional()) -> returns(57)
      -> ignoring($transport)
      -> ignoring($message)
      );
      
    $mailer = new Swift_Mailer($transport);
    $this->assertEqual(57, $mailer->send($message));
    
    $context->assertIsSatisfied();
  }
  
  public function testBatchSendReturnCummulativeSendCount()
  {
    $context = new Mockery();
    $transport = $context->mock('Swift_Transport');
    $message = $context->mock('Swift_Mime_Message');
    $context->checking(Expectations::create()
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
      
    $mailer = new Swift_Mailer($transport);
    $this->assertEqual(2, $mailer->batchSend($message));
    
    $context->assertIsSatisfied();
  }
  
  public function testFailedRecipientReferenceIsPassedToTransport()
  {
    $failures = array();
    
    $context = new Mockery();
    $message = $context->mock('Swift_Mime_Message');
    $transport = $context->mock('Swift_Transport');
    $context->checking(Expectations::create()
      -> one($transport)->send($message, reference($failures))
      -> ignoring($transport)
      -> ignoring($message)
      );
    
    $mailer = new Swift_Mailer($transport);
    $mailer->send($message, $failures);
    
    $context->assertIsSatisfied();
  }
  
  public function testFailedRecipientReferenceIsPassedToTransportInBatch()
  {
    $failures = array();
    
    $context = new Mockery();
    $message = $context->mock('Swift_Mime_Message');
    $transport = $context->mock('Swift_Transport');
    $context->checking(Expectations::create()
      -> allowing($message)->getTo() -> returns(array('foo@bar' => 'Foo'))
      -> one($transport)->send($message, reference($failures))
      -> ignoring($transport)
      -> ignoring($message)
      );
    
    $mailer = new Swift_Mailer($transport);
    $mailer->batchSend($message, $failures);
    
    $context->assertIsSatisfied();
  }
  
  public function testBatchSendCanReadFromIterator()
  {
    $it = new Swift_Mailer_MockRecipientIterator();
    $it->setReturnValueAt(0, 'hasNext', true);
    $it->setReturnValueAt(0, 'nextRecipient', array('one@domain.com' => 'One'));
    $it->setReturnValueAt(1, 'hasNext', true);
    $it->setReturnValueAt(1, 'nextRecipient', array('two@domain.com' => 'Two'));
    $it->setReturnValueAt(2, 'hasNext', true);
    $it->setReturnValueAt(2, 'nextRecipient', array('three@domain.com' => 'Three'));
    $it->setReturnValueAt(0, 'hasNext', false);
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getTo', array());
    $message->expectAt(0, 'setTo', array(array('one@domain.com' => 'One')));
    $message->expectAt(1, 'setTo', array(array('two@domain.com' => 'Two')));
    $message->expectAt(2, 'setTo', array(array('three@domain.com' => 'Three')));
    //The message needs to remain in the state in which it was provided
    $message->expectAt(3, 'setTo', array(array()));
    
    $message->expectCallCount('setTo', 4);
    
    $this->_transport->expectCallCount('send', 3);
    
    $this->_mailer->batchSend($message, $failures, $it);
  }
  
}
