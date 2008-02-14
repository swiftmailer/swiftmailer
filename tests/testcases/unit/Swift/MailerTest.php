<?php

require_once 'Swift/AbstractSwiftUnitTestCase.php';
require_once 'Swift/Mailer.php';
require_once 'Swift/Mailer/Transport.php';
require_once 'Swift/Mime/Message.php';

Mock::generate('Swift_Mailer_Transport', 'Swift_Mailer_MockTransport');
Mock::generate('Swift_Mime_Message', 'Swift_Mime_MockMessage');

class Swift_MailerTest extends Swift_AbstractSwiftUnitTestCase
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
  
}
