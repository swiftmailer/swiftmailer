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
    $this->_transport->setReturnValueAt(0, 'isStarted', false);
    $this->_transport->setReturnValue('isStarted', true);
    $this->_transport->expectOnce('start');
    
    $this->_mailer->send(new Swift_Mime_MockMessage());
  }
  
  public function testTransportIsOnlyStartedOnce()
  {
    $this->_transport->setReturnValueAt(0, 'isStarted', false);
    $this->_transport->setReturnValue('isStarted', true);
    $this->_transport->expectOnce('start');
    
    for ($i = 0; $i < 10; $i++)
    {
      $this->_mailer->send(new Swift_Mime_MockMessage());
    }
  }
  
  public function testMessageIsPassedToTransport()
  {
    $message = new Swift_Mime_MockMessage();
    $this->_transport->expectOnce('send', array($message));
    
    $this->_mailer->send($message);
  }
  
  public function testBatchSendSendsOneMessagePerRecipient()
  {
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getTo', array(
      'one@domain.com' => 'One',
      'two@domain.com' => 'Two',
      'three@domain.com' => 'Three'
      ));
    
    $this->_transport->expectCallCount('send', 3);
    
    $this->_mailer->batchSend($message);
  }
  
  public function testBatchSendChangesToFieldForEachRecipientBeforeRestoring()
  {
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getTo', array(
      'one@domain.com' => 'One',
      'two@domain.com' => 'Two',
      'three@domain.com' => 'Three'
      ));
    $message->expectAt(0, 'setTo', array(array('one@domain.com' => 'One')));
    $message->expectAt(1, 'setTo', array(array('two@domain.com' => 'Two')));
    $message->expectAt(2, 'setTo', array(array('three@domain.com' => 'Three')));
    //The message needs to remain in the state in which it was provided
    $message->expectAt(3, 'setTo', array(
      array(
        'one@domain.com' => 'One',
        'two@domain.com' => 'Two',
        'three@domain.com' => 'Three'
      )
    ));
    $message->expectCallCount('setTo', 4);
    $this->_transport->expectCallCount('send', 3);
    
    $this->_mailer->batchSend($message);
  }
  
  public function testBatchSendClearsAndRestoresCc()
  {
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getTo', array(
      'one@domain.com' => 'One',
      'two@domain.com' => 'Two',
      'three@domain.com' => 'Three'
      ));
    $message->setReturnValue('getCc', array('four@domain.com' => 'Four'));
    //The message needs to remain in the state in which it was provided
    $message->expectAt(0, 'setCc', array(array()));
    $message->expectAt(1, 'setCc', array(array('four@domain.com' => 'Four')));
    $message->expectCallCount('setCc', 2);
    
    $this->_transport->expectCallCount(
      'send', 3 //batchSend() should only send to To: recipients
      );
    
    $this->_mailer->batchSend($message);
  }
  
  public function testBatchSendClearsAndRestoresBcc()
  {
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getTo', array(
      'one@domain.com' => 'One',
      'two@domain.com' => 'Two',
      'three@domain.com' => 'Three'
      ));
    $message->setReturnValue('getBcc', array('four@domain.com' => 'Four'));
    //The message needs to remain in the state in which it was provided
    $message->expectAt(0, 'setBcc', array(array()));
    $message->expectAt(1, 'setBcc', array(array('four@domain.com' => 'Four')));
    $message->expectCallCount('setBcc', 2);
    
    $this->_transport->expectCallCount(
      'send', 3 //batchSend() should only send to To: recipients
      );
    
    $this->_mailer->batchSend($message);
  }
  
  public function testSendReturnsCountFromTransport()
  {
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getTo', array(
      'one@domain.com' => 'One',
      'two@domain.com' => 'Two',
      'three@domain.com' => 'Three'
      ));
    
    $this->_transport->setReturnValue('send', 2);
    
    $this->assertEqual(2, $this->_mailer->send($message));
  }
  
  public function testBatchSendReturnCummulativeSendCount()
  {
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getTo', array(
      'one@domain.com' => 'One',
      'two@domain.com' => 'Two',
      'three@domain.com' => 'Three',
      'four@domain.com' => 'Four'
      ));
    
    $this->_transport->setReturnValueAt(0, 'send', 1);
    $this->_transport->setReturnValueAt(1, 'send', 0);
    $this->_transport->setReturnValueAt(2, 'send', 1);
    $this->_transport->setReturnValueAt(3, 'send', 1);
    
    $this->assertEqual(3, $this->_mailer->batchSend($message));
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
    
    $this->_mailer->batchSend($message, $it);
  }
  
}
