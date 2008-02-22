<?php

require_once 'Swift/Transport/AbstractEsmtpTest.php';
require_once 'Swift/Transport/SendmailTransport.php';
require_once 'Swift/Transport/CommandSentException.php';
require_once 'Swift/Transport/IoBuffer.php';
require_once 'Swift/Mime/Message.php';

Mock::generate('Swift_Transport_IoBuffer',
  'Swift_Transport_MockIoBuffer'
  );
Mock::generate('Swift_Mime_Message', 'Swift_Mime_MockMessage');

class Swift_Transport_SendmailTransportTest
  extends Swift_Transport_AbstractEsmtpTest
{
  
  private $_sendmailBuf;
  private $_sendmail;
  
  public function setUp()
  {
    parent::setUp();
    $this->_sendmailBuf = $this->getMockBuffer();
    $this->_sendmail = new Swift_Transport_SendmailTransport($this->_sendmailBuf);
  }
  
  public function tearDown()
  {
    parent::tearDown();
    $this->_sendmail->stop();
  }
  
  public function getMockBuffer()
  {
    return new Swift_Transport_MockIoBuffer();
  }
  
  public function getEsmtpTransport($buf, $extensions)
  {//All tests should be run for ESMTP functionality on -bs mode.
    $smtp = new Swift_Transport_SendmailTransport($buf);
    $smtp->setCommand('/usr/sbin/sendmail -bs');
    return $smtp;
  }
  
  public function testCommandCanBeSetAndFetched()
  {
    $this->_sendmail->setCommand('/usr/sbin/sendmail -bs');
    $this->assertEqual('/usr/sbin/sendmail -bs', $this->_sendmail->getCommand());
    $this->_sendmail->setCommand('/usr/sbin/sendmail -oi -t');
    $this->assertEqual('/usr/sbin/sendmail -oi -t', $this->_sendmail->getCommand());
  }
  
  public function testSendingMessageInTModeUsesSimplePipe()
  {
    $this->_sendmail->setCommand('/usr/sbin/sendmail -t');
    $this->_sendmailBuf->expectOnce('initialize');
    $this->_sendmailBuf->expectOnce('terminate');
    $this->_sendmailBuf->expectAt(0,
      'setWriteTranslations', array(array("\r\n"=>"\n"))
      );
    $this->_sendmailBuf->expectAt(1,
      'setWriteTranslations', array(array())
      );
    $this->_sendmailBuf->expectCallCount('setWriteTranslations', 2);
    $this->_sendmailBuf->expectNever('readLine');
    $this->_sendmailBuf->expectNever('write'); //mocks don't actually perform the write
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getTo', array('foo@bar'=>'Foobar', 'zip@button'=>'Zippy'));
    $message->expectOnce('toByteStream', array($this->_sendmailBuf));
    
    $this->assertEqual(2, $this->_sendmail->send($message));
  }

}
