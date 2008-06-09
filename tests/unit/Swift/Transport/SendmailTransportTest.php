<?php

require_once 'Swift/Transport/AbstractSmtpEventSupportTest.php';
require_once 'Swift/Transport/SendmailTransport.php';
require_once 'Swift/Mime/Message.php';
require_once 'Swift/Events/EventDispatcher.php';

class Swift_Transport_SendmailTransportTest
  extends Swift_Transport_AbstractSmtpEventSupportTest
{
  
  protected function _getTransport($buf, $dispatcher = null, $command = '/usr/sbin/sendmail -bs')
  {
    if (!$dispatcher)
    {
      $context = new Mockery();
      $dispatcher = $context->mock('Swift_Events_EventDispatcher');
    }
    $transport = new Swift_Transport_SendmailTransport($buf, $dispatcher);
    $transport->setCommand($command);
    return $transport;
  }
  
  protected function _getSendmail($buf, $dispatcher = null)
  {
    if (!$dispatcher)
    {
      $context = new Mockery();
      $dispatcher = $context->mock('Swift_Events_EventDispatcher');
    }
    $sendmail = new Swift_Transport_SendmailTransport($buf, $dispatcher);
    return $sendmail;
  }
  
  public function testCommandCanBeSetAndFetched()
  {
    $context = new Mockery();
    $buf = $this->_getBuffer($context);
    $sendmail = $this->_getSendmail($buf);
    
    $sendmail->setCommand('/usr/sbin/sendmail -bs');
    $this->assertEqual('/usr/sbin/sendmail -bs', $sendmail->getCommand());
    $sendmail->setCommand('/usr/sbin/sendmail -oi -t');
    $this->assertEqual('/usr/sbin/sendmail -oi -t', $sendmail->getCommand());
    
    $context->assertIsSatisfied();
  }
  
  public function testSendingMessageIn_t_ModeUsesSimplePipe()
  {
    $context = new Mockery();
    $buf = $this->_getBuffer($context);
    $sendmail = $this->_getSendmail($buf);
    $message = $context->mock('Swift_Mime_Message');
    
    $context->checking(Expectations::create()
      -> allowing($message)->getTo() -> returns(array('foo@bar'=>'Foobar', 'zip@button'=>'Zippy'))
      -> one($message)->toByteStream($buf)
      -> ignoring($message)
      -> one($buf)->initialize()
      -> one($buf)->terminate()
      -> one($buf)->setWriteTranslations(array("\r\n"=>"\n", "\n." => "\n.."))
      -> one($buf)->setWriteTranslations(array())
      -> ignoring($buf)
      );
    
    $sendmail->setCommand('/usr/sbin/sendmail -t');
    $this->assertEqual(2, $sendmail->send($message));
    
    $context->assertIsSatisfied();
  }
  
  public function testSendingIn_t_ModeWith_i_FlagDoesntEscapeDot()
  {
    $buf = $this->_getBuffer($this->_mockery());
    $sendmail = $this->_getSendmail($buf);
    $message = $this->_createMessage();
    
    $this->_mockery()->checking(Expectations::create()
      -> allowing($message)->getTo() -> returns(array('foo@bar'=>'Foobar', 'zip@button'=>'Zippy'))
      -> one($message)->toByteStream($buf)
      -> ignoring($message)
      -> one($buf)->initialize()
      -> one($buf)->terminate()
      -> one($buf)->setWriteTranslations(array("\r\n"=>"\n"))
      -> one($buf)->setWriteTranslations(array())
      -> ignoring($buf)
      );
    
    $sendmail->setCommand('/usr/sbin/sendmail -i -t');
    $this->assertEqual(2, $sendmail->send($message));
  }
  
  public function testSendingInTModeWith_oi_FlagDoesntEscapeDot()
  {
    $buf = $this->_getBuffer($this->_mockery());
    $sendmail = $this->_getSendmail($buf);
    $message = $this->_createMessage();
    
    $this->_mockery()->checking(Expectations::create()
      -> allowing($message)->getTo() -> returns(array('foo@bar'=>'Foobar', 'zip@button'=>'Zippy'))
      -> one($message)->toByteStream($buf)
      -> ignoring($message)
      -> one($buf)->initialize()
      -> one($buf)->terminate()
      -> one($buf)->setWriteTranslations(array("\r\n"=>"\n"))
      -> one($buf)->setWriteTranslations(array())
      -> ignoring($buf)
      );
    
    $sendmail->setCommand('/usr/sbin/sendmail -oi -t');
    $this->assertEqual(2, $sendmail->send($message));
  }
  
  public function testFluidInterface()
  {
    $context = new Mockery();
    $buf = $this->_getBuffer($context);
    $sendmail = $this->_getTransport($buf);
    
    $ref = $sendmail
      ->setCommand('/foo')
      ;
    $this->assertReference($ref, $sendmail);
    
    $context->assertIsSatisfied();
  }
  
  // -- Creation methods
  
  private function _createMessage()
  {
    return $this->_mockery()->mock('Swift_Mime_Message');
  }

}
