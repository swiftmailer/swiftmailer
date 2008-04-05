<?php

require_once 'Swift/Transport/EsmtpTransportTest.php';
require_once 'Swift/Transport/EsmtpTransport.php';
require_once 'Swift/Transport/EsmtpHandler.php';
require_once 'Swift/Events/EventDispatcher.php';
require_once 'Swift/Events/EventObject.php';

class Swift_Transport_EsmtpTransport_EventSupportTest
  extends Swift_Transport_EsmtpTransportTest
{
  
  protected function _getTransport($buf, $dispatcher = null)
  {
    if (!$dispatcher)
    {
      $context = new Mockery();
      $dispatcher = $context->mock('Swift_Events_EventDispatcher');
    }
    return new Swift_Transport_EsmtpTransport($buf, array(), $dispatcher);
  }
  
  public function testSendingDispatchesBeforeSendEvent()
  {
    $context = new Mockery();
    $buf = $this->_getBuffer($context);
    $dispatcher = $context->mock('Swift_Events_EventDispatcher');
    $evt = $context->mock('Swift_Events_EventObject');
    $smtp = $this->_getTransport($buf, $dispatcher);
    $message = $context->mock('Swift_Mime_Message');
    $context->checking(Expectations::create()
      -> allowing($message)->getFrom() -> returns(array('chris@swiftmailer.org'=>null))
      -> allowing($message)->getTo() -> returns(array('mark@swiftmailer.org'=>'Mark'))
      -> ignoring($message)
      -> allowing($dispatcher)->createEvent('send', $smtp, optional()) -> returns($evt)
      -> one($dispatcher)->dispatchEvent($evt, 'beforeSendPerformed')
      -> ignoring($dispatcher)
      -> ignoring($evt)
      );
    $this->_finishBuffer($context, $buf);
    $smtp->start();
    $this->assertEqual(1, $smtp->send($message));
    $context->assertIsSatisfied();
  }
  
  public function testSendingDispatchesSendEvent()
  {
    $context = new Mockery();
    $buf = $this->_getBuffer($context);
    $dispatcher = $context->mock('Swift_Events_EventDispatcher');
    $evt = $context->mock('Swift_Events_EventObject');
    $smtp = $this->_getTransport($buf, $dispatcher);
    $message = $context->mock('Swift_Mime_Message');
    $context->checking(Expectations::create()
      -> allowing($message)->getFrom() -> returns(array('chris@swiftmailer.org'=>null))
      -> allowing($message)->getTo() -> returns(array('mark@swiftmailer.org'=>'Mark'))
      -> ignoring($message)
      -> allowing($dispatcher)->createEvent('send', $smtp, optional()) -> returns($evt)
      -> one($dispatcher)->dispatchEvent($evt, 'sendPerformed')
      -> ignoring($dispatcher)
      -> ignoring($evt)
      );
    $this->_finishBuffer($context, $buf);
    $smtp->start();
    $this->assertEqual(1, $smtp->send($message));
    $context->assertIsSatisfied();
  }
  
  public function testSendEventCapturesFailures()
  {
    $context = new Mockery();
    $buf = $this->_getBuffer($context);
    $dispatcher = $context->mock('Swift_Events_EventDispatcher');
    $evt = $context->mock('Swift_Events_SendEvent');
    $smtp = $this->_getTransport($buf, $dispatcher);
    $message = $context->mock('Swift_Mime_Message');
    $context->checking(Expectations::create()
      -> allowing($message)->getFrom() -> returns(array('chris@swiftmailer.org'=>null))
      -> allowing($message)->getTo() -> returns(array('mark@swiftmailer.org'=>'Mark'))
      -> ignoring($message)
      -> one($buf)->write("MAIL FROM: <chris@swiftmailer.org>\r\n") -> returns(1)
      -> one($buf)->readLine(1) -> returns("250 OK\r\n")
      -> one($buf)->write("RCPT TO: <mark@swiftmailer.org>\r\n") -> returns(2)
      -> one($buf)->readLine(2) -> returns("500 Not now\r\n")
      -> allowing($dispatcher)->createEvent('send', $smtp, optional()) -> returns($evt)
      -> one($dispatcher)->dispatchEvent($evt, 'sendPerformed') -> calls(create_function('$inv',
        '$args =& $inv->getArguments(); SimpleTest::getContext()->getTest()->assertEqual(
          array("mark@swiftmailer.org"), $args[0]->failedRecipients
          );'
        ))
      -> ignoring($dispatcher)
      -> ignoring($evt)
      );
    $this->_finishBuffer($context, $buf);
    $smtp->start();
    $this->assertEqual(0, $smtp->send($message));
    $context->assertIsSatisfied();
  }
  
  public function testCancellingEventBubbleBeforeSendStopsEvent()
  {
    $context = new Mockery();
    $buf = $this->_getBuffer($context);
    $dispatcher = $context->mock('Swift_Events_EventDispatcher');
    $evt = $context->mock('Swift_Events_EventObject');
    $smtp = $this->_getTransport($buf, $dispatcher);
    $message = $context->mock('Swift_Mime_Message');
    $context->checking(Expectations::create()
      -> allowing($message)->getFrom() -> returns(array('chris@swiftmailer.org'=>null))
      -> allowing($message)->getTo() -> returns(array('mark@swiftmailer.org'=>'Mark'))
      -> ignoring($message)
      -> allowing($dispatcher)->createEvent('send', $smtp, optional()) -> returns($evt)
      -> one($dispatcher)->dispatchEvent($evt, 'beforeSendPerformed')
      -> ignoring($dispatcher)
      -> one($evt)->bubbleCancelled() -> returns(true)
      -> ignoring($evt)
      );
    $this->_finishBuffer($context, $buf);
    $smtp->start();
    $this->assertEqual(0, $smtp->send($message));
    $context->assertIsSatisfied();
  }
  
  public function testStartingTransportDispatchesTransportChangeEvent()
  {
    $context = new Mockery();
    $buf = $this->_getBuffer($context);
    $dispatcher = $context->mock('Swift_Events_EventDispatcher');
    $evt = $context->mock('Swift_Events_EventObject');
    $smtp = $this->_getTransport($buf, $dispatcher);
    $context->checking(Expectations::create()
      -> allowing($dispatcher)->createEvent('transportchange', $smtp, optional()) -> returns($evt)
      -> one($dispatcher)->dispatchEvent($evt, 'transportStarted')
      -> ignoring($dispatcher)
      -> ignoring($evt)
      );
    $this->_finishBuffer($context, $buf);
    $smtp->start();
    $context->assertIsSatisfied();
  }
  
  public function testStoppingTransportDispatchesTransportChangeEvent()
  {
    $context = new Mockery();
    $buf = $this->_getBuffer($context);
    $dispatcher = $context->mock('Swift_Events_EventDispatcher');
    $evt = $context->mock('Swift_Events_EventObject');
    $smtp = $this->_getTransport($buf, $dispatcher);
    $context->checking(Expectations::create()
      -> allowing($dispatcher)->createEvent('transportchange', $smtp, optional()) -> returns($evt)
      -> one($dispatcher)->dispatchEvent($evt, 'transportStopped')
      -> ignoring($dispatcher)
      -> ignoring($evt)
      );
    $this->_finishBuffer($context, $buf);
    $smtp->start();
    $smtp->stop();
    $context->assertIsSatisfied();
  }
  
  public function testResponseEventsAreGenerated()
  {
    $context = new Mockery();
    $buf = $this->_getBuffer($context);
    $dispatcher = $context->mock('Swift_Events_EventDispatcher');
    $evt = $context->mock('Swift_Events_EventObject');
    $smtp = $this->_getTransport($buf, $dispatcher);
    $context->checking(Expectations::create()
      -> allowing($dispatcher)->createEvent('response', $smtp, optional()) -> returns($evt)
      -> one($dispatcher)->dispatchEvent($evt, 'responseReceived')
      -> ignoring($dispatcher)
      -> ignoring($evt)
      );
    $this->_finishBuffer($context, $buf);
    $smtp->start();
    $context->assertIsSatisfied();
  }
  
  public function XtestCommandEventsAreGenerated()
  {
    $context = new Mockery();
    $buf = $this->_getBuffer($context);
    $dispatcher = $context->mock('Swift_Events_EventDispatcher');
    $evt = $context->mock('Swift_Events_EventObject');
    $smtp = $this->_getTransport($buf, $dispatcher);
    $context->checking(Expectations::create()
      -> allowing($dispatcher)->createEvent('command', $smtp, optional()) -> returns($evt)
      -> one($dispatcher)->dispatchEvent($evt, 'commandSent')
      -> ignoring($dispatcher)
      -> ignoring($evt)
      );
    $this->_finishBuffer($context, $buf);
    $smtp->start();
    $context->assertIsSatisfied();
  }
  
  public function testExceptionsCauseExceptionEvents()
  {
    $context = new Mockery();
    $buf = $this->_getBuffer($context);
    $dispatcher = $context->mock('Swift_Events_EventDispatcher');
    $evt = $context->mock('Swift_Events_EventObject');
    $smtp = $this->_getTransport($buf, $dispatcher);
    $context->checking(Expectations::create()
      -> atLeast(1)->of($buf)->readLine(any()) -> returns("503 I'm sleepy, go away!\r\n")
      -> allowing($dispatcher)->createEvent('exception', $smtp, optional()) -> returns($evt)
      -> one($dispatcher)->dispatchEvent($evt, 'exceptionThrown')
      -> ignoring($dispatcher)
      -> ignoring($evt)
      );
    $this->_finishBuffer($context, $buf);
    try
    {
      $smtp->start();
      $this->fail('TransportException should be thrown on invalid response');
    }
    catch (Swift_Transport_TransportException $e)
    {
    }
    $context->assertIsSatisfied();
  }
  
  public function testExceptionBubblesCanBeCancelled()
  {
    $context = new Mockery();
    $buf = $this->_getBuffer($context);
    $dispatcher = $context->mock('Swift_Events_EventDispatcher');
    $evt = $context->mock('Swift_Events_EventObject');
    $smtp = $this->_getTransport($buf, $dispatcher);
    $context->checking(Expectations::create()
      -> atLeast(1)->of($buf)->readLine(any()) -> returns("503 I'm sleepy, go away!\r\n")
      -> allowing($dispatcher)->createEvent('exception', $smtp, optional()) -> returns($evt)
      -> one($dispatcher)->dispatchEvent($evt, 'exceptionThrown')
      -> ignoring($dispatcher)
      -> allowing($evt)->bubbleCancelled() -> returns(true)
      -> ignoring($evt)
      );
    $this->_finishBuffer($context, $buf);
    $smtp->start();
    $context->assertIsSatisfied();
  }
  
}
