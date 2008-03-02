<?php

require_once 'Swift/Transport/AbstractEsmtpTest.php';
require_once 'Swift/Transport/EsmtpTransport.php';
require_once 'Swift/Transport/EsmtpHandler.php';
require_once 'Swift/Transport/IoBuffer.php';
require_once 'Swift/Events/EventDispatcher.php';
require_once 'Swift/Events/EventObject.php';

Mock::generate('Swift_Transport_IoBuffer',
  'Swift_Transport_MockIoBuffer'
  );
Mock::generate('Swift_Events_EventDispatcher', 'Swift_Events_MockEventDispatcher');
Mock::generate('Swift_Events_EventObject', 'Swift_Events_MockEventObject');

class Swift_Transport_EsmtpTransportTest
  extends Swift_Transport_AbstractEsmtpTest
{
  
  private $_smtpBuf;
  private $_smtpTransport;
  
  public function setUp()
  {
    parent::setUp();
    $this->_smtpBuf = $this->getMockBuffer();
    $this->_dispatcher = new Swift_Events_MockEventDispatcher();
    $this->_smtpTransport = $this->getEsmtpTransport(
      $this->_smtpBuf, array(), $this->_dispatcher
      );
  }
  
  public function tearDown()
  {
    parent::tearDown();
    $this->_smtpTransport->stop();
  }
  
  public function getMockBuffer()
  {
    return new Swift_Transport_MockIoBuffer();
  }
  
  public function getEsmtpTransport($buf, $extensions, $dispatcher = null)
  {
    if (is_null($dispatcher))
    {
      $dispatcher = new Swift_Events_MockEventDispatcher();
    }
    return new Swift_Transport_EsmtpTransport($buf, $extensions, $dispatcher);
  }
  
  ///////////////////////////////////////////////////
  // THE FOLLOWING ADDS ESMTP SUPPORT FOR AUTH ETC //
  ///////////////////////////////////////////////////
  
  public function testExtensionHandlersAreSortedAsNeeded()
  {
    $ext1 = new Swift_Transport_MockEsmtpHandler();
    $ext1->setReturnValue('getHandledKeyword', 'AUTH');
    $ext1->setReturnValue('getPriorityOver', 0, array('STARTTLS'));
    
    $ext2 = new Swift_Transport_MockEsmtpHandler();
    $ext2->setReturnValue('getHandledKeyword', 'STARTTLS');
    $ext2->setReturnValue('getPriorityOver', -1, array('AUTH'));
    
    $this->_smtpTransport->setExtensionHandlers(array($ext1, $ext2));
    $this->assertEqual(array($ext2, $ext1), $this->_smtpTransport->getExtensionHandlers());
  }
  
  public function testHandlersAreNotifiedOfParams()
  {
    $ext1 = new Swift_Transport_MockEsmtpHandler();
    $ext1->setReturnValue('getHandledKeyword', 'AUTH');
    $ext1->expectOnce('setKeywordParams', array(array('PLAIN', 'LOGIN')));
    
    $ext2 = new Swift_Transport_MockEsmtpHandler();
    $ext2->setReturnValue('getHandledKeyword', 'SIZE');
    $ext2->expectOnce('setKeywordParams', array(array('123456')));
    
    $this->_smtpTransport->setExtensionHandlers(array($ext1, $ext2));
    
    $this->_smtpBuf->setReturnValue(
      'readLine', '220 server.com foo' . "\r\n", array(0)
      );
    $this->_smtpBuf->expectAt(
      0, 'write', array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_smtpBuf->setReturnValue(
      'write', 1, array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_smtpBuf->setReturnValueAt(1,
      'readLine', '250-ServerName.tld' . "\r\n", array(1)
      );
    $this->_smtpBuf->setReturnValueAt(2,
      'readLine', '250-AUTH PLAIN LOGIN' . "\r\n", array(1)
      );
    $this->_smtpBuf->setReturnValueAt(3,
      'readLine', '250 SIZE=123456' . "\r\n", array(1)
      );
    
    $this->_finishSmtpBuffer();
    
    $this->_smtpTransport->start();
  }
  
  public function testSupportedExtensionHandlersAreRunAfterEhlo()
  {
    $ext1 = new Swift_Transport_MockEsmtpHandler();
    $ext1->setReturnValue('getHandledKeyword', 'AUTH');
    $ext1->expectOnce('setKeywordParams', array(array('PLAIN', 'LOGIN')));
    $ext1->expectOnce('afterEhlo', array($this->_smtpTransport));
    
    $ext2 = new Swift_Transport_MockEsmtpHandler();
    $ext2->setReturnValue('getHandledKeyword', 'SIZE');
    $ext2->expectOnce('setKeywordParams', array(array('123456')));
    $ext2->expectOnce('afterEhlo', array($this->_smtpTransport));
    
    $ext3 = new Swift_Transport_MockEsmtpHandler();
    $ext3->setReturnValue('getHandledKeyword', 'STARTTLS');
    $ext3->expectNever('setKeywordParams');
    $ext3->expectNever('afterEhlo');
    
    $this->_smtpTransport->setExtensionHandlers(array($ext1, $ext2, $ext3));
    
    $this->_smtpBuf->setReturnValue(
      'readLine', '220 server.com foo' . "\r\n", array(0)
      );
    $this->_smtpBuf->expectAt(
      0, 'write', array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_smtpBuf->setReturnValue(
      'write', 1, array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_smtpBuf->setReturnValueAt(1,
      'readLine', '250-ServerName.tld' . "\r\n", array(1)
      );
    $this->_smtpBuf->setReturnValueAt(2,
      'readLine', '250-AUTH PLAIN LOGIN' . "\r\n", array(1)
      );
    $this->_smtpBuf->setReturnValueAt(3,
      'readLine', '250 SIZE=123456' . "\r\n", array(1)
      );
    
    $this->_finishSmtpBuffer();
    
    $this->_smtpTransport->start();
  }
  
  public function testExtensionsCanModifyMailFromParams()
  {
    $ext1 = new Swift_Transport_MockEsmtpHandler();
    $ext1->setReturnValue('getHandledKeyword', 'AUTH');
    $ext1->setReturnValue('getMailParams', array('FOO'));
    $ext1->setReturnValue('getPriorityOver', -1);
    
    $ext2 = new Swift_Transport_MockEsmtpHandler();
    $ext2->setReturnValue('getHandledKeyword', 'SIZE');
    $ext2->setReturnValue('getMailParams', array('ZIP'));
    $ext2->setReturnValue('getPriorityOver', 1);
    
    $ext3 = new Swift_Transport_MockEsmtpHandler();
    $ext3->setReturnValue('getHandledKeyword', 'STARTTLS');
    $ext3->expectNever('getMailParams');
    
    $this->_smtpTransport->setExtensionHandlers(array($ext1, $ext2, $ext3));
    
    $this->_smtpBuf->setReturnValue(
      'readLine', '220 server.com foo' . "\r\n", array(0)
      );
    $this->_smtpBuf->expectAt(
      0, 'write', array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_smtpBuf->setReturnValue(
      'write', 1, array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_smtpBuf->setReturnValueAt(1,
      'readLine', '250-ServerName.tld' . "\r\n", array(1)
      );
    $this->_smtpBuf->setReturnValueAt(2,
      'readLine', '250-AUTH PLAIN LOGIN' . "\r\n", array(1)
      );
    $this->_smtpBuf->setReturnValueAt(3,
      'readLine', '250 SIZE=123456' . "\r\n", array(1)
      );
    $this->_smtpBuf->expectAt(1, 'write', array("MAIL FROM: <me@domain> FOO ZIP\r\n"));
    $this->_smtpBuf->setReturnValue('write', 2, array("MAIL FROM: <me@domain> FOO ZIP\r\n"));
    $this->_smtpBuf->setReturnValue('readLine', "250 OK\r\n", array(2));
    
    $this->_smtpBuf->expectAt(2, 'write', array("RCPT TO: <foo@bar>\r\n"));
    $this->_smtpBuf->setReturnValue('write', 3, array("RCPT TO: <foo@bar>\r\n"));
    $this->_smtpBuf->setReturnValue('readLine', "250 OK\r\n", array(3));
    
    $this->_smtpBuf->expectMinimumCallCount('write', 3);
    
    $this->_finishSmtpBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('me@domain'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar'=>null));
    
    $this->_smtpTransport->start();
    $this->_smtpTransport->send($message);
  }
  
  public function testExtensionsCanModifyRcptParams()
  {
    $ext1 = new Swift_Transport_MockEsmtpHandler();
    $ext1->setReturnValue('getHandledKeyword', 'AUTH');
    $ext1->setReturnValue('getRcptParams', array('FOO'));
    $ext1->setReturnValue('getPriorityOver', -1);
    
    $ext2 = new Swift_Transport_MockEsmtpHandler();
    $ext2->setReturnValue('getHandledKeyword', 'SIZE');
    $ext2->setReturnValue('getRcptParams', array('ZIP'));
    $ext2->setReturnValue('getPriorityOver', 1);
    
    $ext3 = new Swift_Transport_MockEsmtpHandler();
    $ext3->setReturnValue('getHandledKeyword', 'STARTTLS');
    $ext3->expectNever('getRcptParams');
    
    $this->_smtpTransport->setExtensionHandlers(array($ext1, $ext2, $ext3));
    
    $this->_smtpBuf->setReturnValue(
      'readLine', '220 server.com foo' . "\r\n", array(0)
      );
    $this->_smtpBuf->expectAt(
      0, 'write', array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_smtpBuf->setReturnValue(
      'write', 1, array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_smtpBuf->setReturnValueAt(1,
      'readLine', '250-ServerName.tld' . "\r\n", array(1)
      );
    $this->_smtpBuf->setReturnValueAt(2,
      'readLine', '250-AUTH PLAIN LOGIN' . "\r\n", array(1)
      );
    $this->_smtpBuf->setReturnValueAt(3,
      'readLine', '250 SIZE=123456' . "\r\n", array(1)
      );
    $this->_smtpBuf->expectAt(1, 'write', array("MAIL FROM: <me@domain>\r\n"));
    $this->_smtpBuf->setReturnValue('write', 2, array("MAIL FROM: <me@domain>\r\n"));
    $this->_smtpBuf->setReturnValue('readLine', "250 OK\r\n", array(2));
    
    $this->_smtpBuf->expectAt(2, 'write', array("RCPT TO: <foo@bar> FOO ZIP\r\n"));
    $this->_smtpBuf->setReturnValue('write', 3, array("RCPT TO: <foo@bar> FOO ZIP\r\n"));
    $this->_smtpBuf->setReturnValue('readLine', "250 OK\r\n", array(3));
    
    $this->_smtpBuf->expectMinimumCallCount('write', 3);
    
    $this->_finishSmtpBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('me@domain'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar'=>null));
    
    $this->_smtpTransport->start();
    $this->_smtpTransport->send($message);
  }
  
  public function testExtensionsAreNotifiedOnCommand()
  {
    $ext1 = new Swift_Transport_MockEsmtpHandler();
    $ext1->setReturnValue('getHandledKeyword', 'AUTH');
    $ext1->setReturnValue('getPriorityOver', -1);
    $ext1->expectAt(0, 'onCommand', array($this->_smtpTransport, "FOO\r\n", array(250, 251)));
    $ext1->expectAtLeastOnce('onCommand');
    
    $ext2 = new Swift_Transport_MockEsmtpHandler();
    $ext2->setReturnValue('getHandledKeyword', 'SIZE');
    $ext2->setReturnValue('getPriorityOver', 1);
    $ext2->expectAt(0, 'onCommand', array($this->_smtpTransport, "FOO\r\n", array(250, 251)));
    $ext2->expectAtLeastOnce('onCommand');
    
    $ext3 = new Swift_Transport_MockEsmtpHandler();
    $ext3->setReturnValue('getHandledKeyword', 'STARTTLS');
    $ext3->expectNever('onCommand');
    
    $this->_smtpTransport->setExtensionHandlers(array($ext1, $ext2, $ext3));
    
    $this->_smtpBuf->setReturnValue(
      'readLine', '220 server.com foo' . "\r\n", array(0)
      );
    $this->_smtpBuf->expectAt(
      0, 'write', array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_smtpBuf->setReturnValue(
      'write', 1, array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_smtpBuf->setReturnValueAt(1,
      'readLine', '250-ServerName.tld' . "\r\n", array(1)
      );
    $this->_smtpBuf->setReturnValueAt(2,
      'readLine', '250-AUTH PLAIN LOGIN' . "\r\n", array(1)
      );
    $this->_smtpBuf->setReturnValueAt(3,
      'readLine', '250 SIZE=123456' . "\r\n", array(1)
      );
    $this->_smtpBuf->setReturnValue('write', 2, array("FOO\r\n"));
    $this->_smtpBuf->setReturnValue('readLine', "251 Cool\r\n", array(2));
    
    $this->_finishSmtpBuffer();
    
    $this->_smtpTransport->start();
    
    $this->_smtpTransport->executeCommand("FOO\r\n", array(250, 251));
  }
  
  public function testChainOfCommandAlgorithmWhenNotifyingExtensions()
  {
    $e = new Swift_Transport_CommandSentException("250 OK\r\n");
    $ext1 = new Swift_Transport_MockEsmtpHandler();
    $ext1->setReturnValue('getHandledKeyword', 'AUTH');
    $ext1->setReturnValue('getPriorityOver', -1);
    $ext1->expectAt(0, 'onCommand', array($this->_smtpTransport, "FOO\r\n", array(250, 251)));
    $ext1->throwOn('onCommand', $e);
    $ext1->expectAtLeastOnce('onCommand');
    
    $ext2 = new Swift_Transport_MockEsmtpHandler();
    $ext2->setReturnValue('getHandledKeyword', 'SIZE');
    $ext2->setReturnValue('getPriorityOver', 1);
    $ext2->expectNever('onCommand');
    
    $ext3 = new Swift_Transport_MockEsmtpHandler();
    $ext3->setReturnValue('getHandledKeyword', 'STARTTLS');
    $ext3->expectNever('onCommand');
    
    $this->_smtpTransport->setExtensionHandlers(array($ext1, $ext2, $ext3));
    
    $this->_smtpBuf->setReturnValue(
      'readLine', '220 server.com foo' . "\r\n", array(0)
      );
    $this->_smtpBuf->expectAt(
      0, 'write', array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_smtpBuf->setReturnValue(
      'write', 1, array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_smtpBuf->setReturnValueAt(1,
      'readLine', '250-ServerName.tld' . "\r\n", array(1)
      );
    $this->_smtpBuf->setReturnValueAt(2,
      'readLine', '250-AUTH PLAIN LOGIN' . "\r\n", array(1)
      );
    $this->_smtpBuf->setReturnValueAt(3,
      'readLine', '250 SIZE=123456' . "\r\n", array(1)
      );
    
    $this->_finishSmtpBuffer();
    
    $this->_smtpTransport->start();
    
    $this->_smtpTransport->executeCommand("FOO\r\n", array(250, 251));
  }
  
  public function testExtensionsCanExposeMixinMethods()
  {
    $ext1 = new Swift_Transport_MockEsmtpHandlerMixin();
    $ext1->setReturnValue('getHandledKeyword', 'AUTH');
    $ext1->setReturnValue('getPriorityOver', 0, array('STARTTLS'));
    $ext1->setReturnValue('exposeMixinMethods', array('setUsername', 'setPassword'));
    $ext1->expectOnce('setUsername', array('mick'));
    $ext1->expectOnce('setPassword', array('pass'));
    
    $ext2 = new Swift_Transport_MockEsmtpHandler();
    $ext2->setReturnValue('getHandledKeyword', 'STARTTLS');
    $ext2->setReturnValue('getPriorityOver', -1, array('AUTH'));
    
    $this->_smtpTransport->setExtensionHandlers(array($ext1, $ext2));
    
    $this->_smtpTransport->setUsername('mick');
    $this->_smtpTransport->setPassword('pass');
  }
  
  public function testMixinMethodsBeginningWithSetAndNullReturnAreFluid()
  {
    $ext1 = new Swift_Transport_MockEsmtpHandlerMixin();
    $ext1->setReturnValue('getHandledKeyword', 'AUTH');
    $ext1->setReturnValue('getPriorityOver', 0, array('STARTTLS'));
    $ext1->setReturnValue('exposeMixinMethods', array('setUsername', 'setPassword'));
    $ext1->expectOnce('setUsername', array('mick'));
    $ext1->setReturnValue('setUsername', null);
    $ext1->expectOnce('setPassword', array('pass'));
    $ext1->setReturnValue('setPassword', null);
    
    $ext2 = new Swift_Transport_MockEsmtpHandler();
    $ext2->setReturnValue('getHandledKeyword', 'STARTTLS');
    $ext2->setReturnValue('getPriorityOver', -1, array('AUTH'));
    
    $this->_smtpTransport->setExtensionHandlers(array($ext1, $ext2));
    
    $this->assertReference($this->_smtpTransport, $this->_smtpTransport->setUsername('mick'));
    $this->assertReference($this->_smtpTransport, $this->_smtpTransport->setPassword('pass'));
  }
  
  public function testMixinSetterWhichReturnValuesAreNotFluid()
  {
    $ext1 = new Swift_Transport_MockEsmtpHandlerMixin();
    $ext1->setReturnValue('getHandledKeyword', 'AUTH');
    $ext1->setReturnValue('getPriorityOver', 0, array('STARTTLS'));
    $ext1->setReturnValue('exposeMixinMethods', array('setUsername', 'setPassword'));
    $ext1->expectOnce('setUsername', array('mick'));
    $ext1->setReturnValue('setUsername', 'x');
    $ext1->expectOnce('setPassword', array('pass'));
    $ext1->setReturnValue('setPassword', 'y');
    
    $ext2 = new Swift_Transport_MockEsmtpHandler();
    $ext2->setReturnValue('getHandledKeyword', 'STARTTLS');
    $ext2->setReturnValue('getPriorityOver', -1, array('AUTH'));
    
    $this->_smtpTransport->setExtensionHandlers(array($ext1, $ext2));
    
    $this->assertEqual('x', $this->_smtpTransport->setUsername('mick'));
    $this->assertEqual('y', $this->_smtpTransport->setPassword('pass'));
  }
  
  //////////////////////////////////////////////////
  /// THE FOLLOWING TEST EVENT HANDLING FEATURES ///
  //////////////////////////////////////////////////
  
  public function testSendingDispatchesBeforeSendEvent()
  {
    $evt = new Swift_Events_MockEventObject();
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('chris@swiftmailer.org'=>null));
    $message->setReturnValue('getTo', array('mark@swiftmailer.org'=>'Mark'));
    $this->_dispatcher->setReturnValue(
      'createEvent', $evt, array('send', $this->_smtpTransport, '*')
      );
    $this->_dispatcher->expectAt(0, 'dispatchEvent', array($evt, 'beforeSendPerformed'));
    $this->_dispatcher->expectMinimumCallCount('dispatchEvent', 1);
    
    $this->_finishSmtpBuffer();
    
    $this->_smtpTransport->start();
    
    $this->_smtpTransport->send($message);
  }
  
  public function testSendingDispatchesSendEvent()
  {
    $evt = new Swift_Events_MockEventObject();
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('chris@swiftmailer.org'=>null));
    $message->setReturnValue('getTo', array('mark@swiftmailer.org'=>'Mark'));
    $this->_dispatcher->setReturnValue(
      'createEvent', $evt, array('send', $this->_smtpTransport, '*')
      );
    $this->_dispatcher->expectAt(0, 'dispatchEvent', array($evt, 'beforeSendPerformed'));
    $this->_dispatcher->expectAt(1, 'dispatchEvent', array($evt, 'sendPerformed'));
    $this->_dispatcher->expectMinimumCallCount('dispatchEvent', 2);
    
    $this->_finishSmtpBuffer();
    
    $this->_smtpTransport->start();
    
    $this->_smtpTransport->send($message);
  }
  
  public function testCancellingEventBubbleBeforeSendStopsEvent()
  {
    $evt = new Swift_Events_MockEventObject();
    $evt->setReturnValue('bubbleCancelled', true);
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('chris@swiftmailer.org'=>null));
    $message->setReturnValue('getTo', array('mark@swiftmailer.org'=>'Mark'));
    $this->_dispatcher->setReturnValue(
      'createEvent', $evt, array('send', $this->_smtpTransport, '*')
      );
    $this->_dispatcher->expectOnce('dispatchEvent', array($evt, 'beforeSendPerformed'));
    
    $this->_finishSmtpBuffer();
    
    $this->_smtpTransport->start();
    
    $this->assertEqual(0, $this->_smtpTransport->send($message));
  }
  
  public function testStartingTransportDispatchesTransportChangeEvent()
  {
    $evt = new Swift_Events_MockEventObject();
    
    $this->_dispatcher->setReturnValue(
      'createEvent', $evt, array('transportchange', $this->_smtpTransport)
      );
    $this->_dispatcher->expectAt(0, 'dispatchEvent', array($evt, 'transportStarted'));
    $this->_dispatcher->expectMinimumCallCount('dispatchEvent', 1);
    
    $this->_finishSmtpBuffer();
    
    $this->_smtpTransport->start();
  }
  
  public function testStoppingTransportDispatchesTransportChangeEvent()
  {
    $evt = new Swift_Events_MockEventObject();
    
    $this->_dispatcher->setReturnValue(
      'createEvent', $evt, array('transportchange', $this->_smtpTransport)
      );
    $this->_dispatcher->expectAt(0, 'dispatchEvent', array($evt, 'transportStarted'));
    $this->_dispatcher->expectAt(1, 'dispatchEvent', array($evt, 'transportStopped'));
    $this->_dispatcher->expectMinimumCallCount('dispatchEvent', 2);
    
    $this->_finishSmtpBuffer();
    
    $this->_smtpTransport->start();
    $this->_smtpTransport->stop();
  }
  
  public function testResponseEventsAreGenerated()
  {
    $evt = new Swift_Events_MockEventObject();
    
    $this->_dispatcher->setReturnValue(
      'createEvent', $evt, array('response', $this->_smtpTransport, '*')
      );
    $this->_dispatcher->expectAtLeastOnce('dispatchEvent', array($evt, 'responseReceived'));
    
    $this->_finishSmtpBuffer();
    
    $this->_smtpTransport->start();
  }
  
  public function testCommandEventsAreGenerated()
  {
    $evt = new Swift_Events_MockEventObject();
    
    $this->_dispatcher->setReturnValue(
      'createEvent', $evt, array('command', $this->_smtpTransport, '*')
      );
    $this->_dispatcher->expectAtLeastOnce('dispatchEvent', array($evt, 'commandSent'));
    
    $this->_finishSmtpBuffer();
    
    $this->_smtpTransport->start();
  }
  
  public function testExceptionsCauseExceptionEvents()
  {
    $evt = new Swift_Events_MockEventObject();
    
    $this->_dispatcher->setReturnValue(
      'createEvent', $evt, array('exception', $this->_smtpTransport, '*')
      );
    $this->_dispatcher->expectAtLeastOnce('dispatchEvent', array($evt, 'exceptionThrown'));
    
    $this->_smtpBuf->setReturnValue(
      'readLine', '503 I am sleepy, go away!' . "\r\n", array(0)
      );
    
    $this->_finishSmtpBuffer();
    
    try
    {
      $this->_smtpTransport->start();
      $this->fail('TransportException should be thrown on invalid response');
    }
    catch (Swift_Transport_TransportException $e)
    {
      $this->pass();
    }
  }
  
  public function testExceptionsBubblesCanBeCancelled()
  {
    $evt = new Swift_Events_MockEventObject();
    $evt->setReturnValue('bubbleCancelled', true);
    
    $this->_dispatcher->setReturnValue(
      'createEvent', $evt, array('exception', $this->_smtpTransport, '*')
      );
    $this->_dispatcher->expectAtLeastOnce('dispatchEvent', array($evt, 'exceptionThrown'));
    
    $this->_smtpBuf->setReturnValue(
      'readLine', '503 I am sleepy, go away!' . "\r\n", array(0)
      );
    
    $this->_finishSmtpBuffer();
    
    $this->_smtpTransport->start();
  }
  
  // -- Private helpers
  
  /**
   * Fill in any gaps ;)
   */
  private function _finishSmtpBuffer()
  {
    $this->_smtpBuf->setReturnValue(
      'readLine', '220 server.com foo' . "\r\n", array(0)
      );
    $this->_smtpBuf->setReturnValue(
      'write', $x = uniqid(), array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_smtpBuf->setReturnValue(
      'readLine', '250 ServerName' . "\r\n", array($x)
      );
    $this->_smtpBuf->setReturnValue(
      'write', $x = uniqid(), array(new PatternExpectation('~^MAIL FROM: <.*?>\r\n$~D'))
      );
    $this->_smtpBuf->setReturnValue(
      'readLine', '250 OK' . "\r\n", array($x)
      );
    $this->_smtpBuf->setReturnValue(
      'write', $x = uniqid(), array(new PatternExpectation('~^RCPT TO: <.*?>\r\n$~D'))
      );
    $this->_smtpBuf->setReturnValue(
      'readLine', "250 OK\r\n", array($x)
      );
    $this->_smtpBuf->setReturnValue('write', $x = uniqid(), array("DATA\r\n"));
    $this->_smtpBuf->setReturnValue('readLine', "354 Go ahead\r\n", array($x));
    $this->_smtpBuf->setReturnValue('write', $x = uniqid(), array("\r\n.\r\n"));
    $this->_smtpBuf->setReturnValue('readLine', "250 OK\r\n", array($x));
    $this->_smtpBuf->setReturnValue('readLine', false); //default return
  }
  
}
