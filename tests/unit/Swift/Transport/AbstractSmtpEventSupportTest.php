<?php

require_once 'Swift/Transport/AbstractSmtpTest.php';
require_once 'Swift/Events/EventDispatcher.php';
require_once 'Swift/Events/EventListener.php';
require_once 'Swift/Events/EventObject.php';
require_once 'Swift/Events/EventListener.php';

abstract class Swift_Transport_AbstractSmtpEventSupportTest
    extends Swift_Transport_AbstractSmtpTest
{
    public function testRegisterPluginLoadsPluginInEventDispatcher()
    {
        $buf = $this->_getBuffer();
        $dispatcher = $this->_createEventDispatcher(false);
        $listener = $this->_mock('Swift_Events_EventListener');
        $smtp = $this->_getTransport($buf, $dispatcher);
        $this->_checking(Expectations::create()
            -> one($dispatcher)->bindEventListener($listener)
            );
        $smtp->registerPlugin($listener);
    }

    public function testSendingDispatchesBeforeSendEvent()
    {
        $buf = $this->_getBuffer();
        $dispatcher = $this->_createEventDispatcher(false);
        $message = $this->_createMessage();
        $smtp = $this->_getTransport($buf, $dispatcher);
        $evt = $this->_mock('Swift_Events_SendEvent');
        $this->_checking(Expectations::create()
            -> allowing($message)->getFrom() -> returns(array('chris@swiftmailer.org'=>null))
            -> allowing($message)->getTo() -> returns(array('mark@swiftmailer.org'=>'Mark'))
            -> ignoring($message)
            -> one($dispatcher)->createSendEvent(optional()) -> returns($evt)
            -> one($dispatcher)->dispatchEvent($evt, 'beforeSendPerformed')
            -> ignoring($dispatcher)
            -> ignoring($evt)
            );
        $this->_finishBuffer($buf);
        $smtp->start();
        $this->assertEqual(1, $smtp->send($message));
    }

    public function testSendingDispatchesSendEvent()
    {
        $buf = $this->_getBuffer();
        $dispatcher = $this->_createEventDispatcher(false);
        $message = $this->_createMessage();
        $smtp = $this->_getTransport($buf, $dispatcher);
        $evt = $this->_mock('Swift_Events_SendEvent');
        $this->_checking(Expectations::create()
            -> allowing($message)->getFrom() -> returns(array('chris@swiftmailer.org'=>null))
            -> allowing($message)->getTo() -> returns(array('mark@swiftmailer.org'=>'Mark'))
            -> ignoring($message)
            -> one($dispatcher)->createSendEvent(optional()) -> returns($evt)
            -> one($dispatcher)->dispatchEvent($evt, 'sendPerformed')
            -> ignoring($dispatcher)
            -> ignoring($evt)
            );
        $this->_finishBuffer($buf);
        $smtp->start();
        $this->assertEqual(1, $smtp->send($message));
    }

    public function testSendEventCapturesFailures()
    {
        $buf = $this->_getBuffer();
        $dispatcher = $this->_createEventDispatcher(false);
        $evt = $this->_mock('Swift_Events_SendEvent');
        $smtp = $this->_getTransport($buf, $dispatcher);
        $message = $this->_createMessage();
        $this->_checking(Expectations::create()
            -> allowing($message)->getFrom() -> returns(array('chris@swiftmailer.org'=>null))
            -> allowing($message)->getTo() -> returns(array('mark@swiftmailer.org'=>'Mark'))
            -> ignoring($message)
            -> one($buf)->write("MAIL FROM: <chris@swiftmailer.org>\r\n") -> returns(1)
            -> one($buf)->readLine(1) -> returns("250 OK\r\n")
            -> one($buf)->write("RCPT TO: <mark@swiftmailer.org>\r\n") -> returns(2)
            -> one($buf)->readLine(2) -> returns("500 Not now\r\n")
            -> allowing($dispatcher)->createSendEvent($smtp, optional()) -> returns($evt)
            -> one($evt)->setFailedRecipients(array('mark@swiftmailer.org'))
            -> ignoring($dispatcher)
            -> ignoring($evt)
            );
        $this->_finishBuffer($buf);
        $smtp->start();
        $this->assertEqual(0, $smtp->send($message));
    }

    public function testSendEventHasResultFailedIfAllFailures()
    {
        $buf = $this->_getBuffer();
        $dispatcher = $this->_createEventDispatcher(false);
        $evt = $this->_mock('Swift_Events_SendEvent');
        $smtp = $this->_getTransport($buf, $dispatcher);
        $message = $this->_createMessage();
        $this->_checking(Expectations::create()
            -> allowing($message)->getFrom() -> returns(array('chris@swiftmailer.org'=>null))
            -> allowing($message)->getTo() -> returns(array('mark@swiftmailer.org'=>'Mark'))
            -> ignoring($message)
            -> one($buf)->write("MAIL FROM: <chris@swiftmailer.org>\r\n") -> returns(1)
            -> one($buf)->readLine(1) -> returns("250 OK\r\n")
            -> one($buf)->write("RCPT TO: <mark@swiftmailer.org>\r\n") -> returns(2)
            -> one($buf)->readLine(2) -> returns("500 Not now\r\n")
            -> allowing($dispatcher)->createSendEvent($smtp, optional()) -> returns($evt)
            -> one($evt)->setResult(Swift_Events_SendEvent::RESULT_FAILED)
            -> ignoring($dispatcher)
            -> ignoring($evt)
            );
        $this->_finishBuffer($buf);
        $smtp->start();
        $this->assertEqual(0, $smtp->send($message));
    }

    public function testSendEventHasResultTentativeIfSomeFailures()
    {
        $buf = $this->_getBuffer();
        $dispatcher = $this->_createEventDispatcher(false);
        $evt = $this->_mock('Swift_Events_SendEvent');
        $smtp = $this->_getTransport($buf, $dispatcher);
        $message = $this->_createMessage();
        $this->_checking(Expectations::create()
            -> allowing($message)->getFrom() -> returns(array('chris@swiftmailer.org'=>null))
            -> allowing($message)->getTo() -> returns(array(
                'mark@swiftmailer.org'=>'Mark', 'chris@site.tld'=>'Chris'
                ))
            -> ignoring($message)
            -> one($buf)->write("MAIL FROM: <chris@swiftmailer.org>\r\n") -> returns(1)
            -> one($buf)->readLine(1) -> returns("250 OK\r\n")
            -> one($buf)->write("RCPT TO: <mark@swiftmailer.org>\r\n") -> returns(2)
            -> one($buf)->readLine(2) -> returns("500 Not now\r\n")
            -> allowing($dispatcher)->createSendEvent($smtp, optional()) -> returns($evt)
            -> one($evt)->setResult(Swift_Events_SendEvent::RESULT_TENTATIVE)
            -> ignoring($dispatcher)
            -> ignoring($evt)
            );
        $this->_finishBuffer($buf);
        $smtp->start();
        $this->assertEqual(1, $smtp->send($message));
    }

    public function testSendEventHasResultSuccessIfNoFailures()
    {
        $buf = $this->_getBuffer();
        $dispatcher = $this->_createEventDispatcher(false);
        $evt = $this->_mock('Swift_Events_SendEvent');
        $smtp = $this->_getTransport($buf, $dispatcher);
        $message = $this->_createMessage();
        $this->_checking(Expectations::create()
            -> allowing($message)->getFrom() -> returns(array('chris@swiftmailer.org'=>null))
            -> allowing($message)->getTo() -> returns(array(
                'mark@swiftmailer.org'=>'Mark', 'chris@site.tld'=>'Chris'
                ))
            -> ignoring($message)
            -> allowing($dispatcher)->createSendEvent($smtp, optional()) -> returns($evt)
            -> one($evt)->setResult(Swift_Events_SendEvent::RESULT_SUCCESS)
            -> ignoring($dispatcher)
            -> ignoring($evt)
            );
        $this->_finishBuffer($buf);
        $smtp->start();
        $this->assertEqual(2, $smtp->send($message));
    }

    public function testCancellingEventBubbleBeforeSendStopsEvent()
    {
        $buf = $this->_getBuffer();
        $dispatcher = $this->_createEventDispatcher(false);
        $evt = $this->_mock('Swift_Events_SendEvent');
        $smtp = $this->_getTransport($buf, $dispatcher);
        $message = $this->_createMessage();
        $this->_checking(Expectations::create()
            -> allowing($message)->getFrom() -> returns(array('chris@swiftmailer.org'=>null))
            -> allowing($message)->getTo() -> returns(array('mark@swiftmailer.org'=>'Mark'))
            -> ignoring($message)
            -> allowing($dispatcher)->createSendEvent($smtp, optional()) -> returns($evt)
            -> one($dispatcher)->dispatchEvent($evt, 'beforeSendPerformed')
            -> ignoring($dispatcher)
            -> atLeast(1)->of($evt)->bubbleCancelled() -> returns(true)
            -> ignoring($evt)
            );
        $this->_finishBuffer($buf);
        $smtp->start();
        $this->assertEqual(0, $smtp->send($message));
    }

    public function testStartingTransportDispatchesTransportChangeEvent()
    {
        $buf = $this->_getBuffer();
        $dispatcher = $this->_createEventDispatcher(false);
        $evt = $this->_mock('Swift_Events_TransportChangeEvent');
        $smtp = $this->_getTransport($buf, $dispatcher);
        $this->_checking(Expectations::create()
            -> allowing($dispatcher)->createTransportChangeEvent($smtp, optional()) -> returns($evt)
            -> one($dispatcher)->dispatchEvent($evt, 'transportStarted')
            -> ignoring($dispatcher)
            -> ignoring($evt)
            );
        $this->_finishBuffer($buf);
        $smtp->start();
    }

    public function testStartingTransportDispatchesBeforeTransportChangeEvent()
    {
        $buf = $this->_getBuffer();
        $dispatcher = $this->_createEventDispatcher(false);
        $evt = $this->_mock('Swift_Events_TransportChangeEvent');
        $smtp = $this->_getTransport($buf, $dispatcher);
        $this->_checking(Expectations::create()
            -> allowing($dispatcher)->createTransportChangeEvent($smtp, optional()) -> returns($evt)
            -> one($dispatcher)->dispatchEvent($evt, 'beforeTransportStarted')
            -> ignoring($dispatcher)
            -> ignoring($evt)
            );
        $this->_finishBuffer($buf);
        $smtp->start();
    }

    public function testCancellingBubbleBeforeTransportStartStopsEvent()
    {
        $buf = $this->_getBuffer();
        $dispatcher = $this->_createEventDispatcher(false);
        $evt = $this->_mock('Swift_Events_TransportChangeEvent');
        $smtp = $this->_getTransport($buf, $dispatcher);
        $this->_checking(Expectations::create()
            -> allowing($dispatcher)->createTransportChangeEvent($smtp, optional()) -> returns($evt)
            -> one($dispatcher)->dispatchEvent($evt, 'beforeTransportStarted')
            -> allowing($evt)->bubbleCancelled() -> returns(true)
            -> ignoring($dispatcher)
            -> ignoring($evt)
            );
        $this->_finishBuffer($buf);
        $smtp->start();

        $this->assertFalse($smtp->isStarted(),
            '%s: Transport should not be started since event bubble was cancelled'
        );
    }

    public function testStoppingTransportDispatchesTransportChangeEvent()
    {
        $buf = $this->_getBuffer();
        $dispatcher = $this->_createEventDispatcher(false);
        $evt = $this->_mock('Swift_Events_TransportChangeEvent');
        $smtp = $this->_getTransport($buf, $dispatcher);
        $this->_checking(Expectations::create()
            -> allowing($dispatcher)->createTransportChangeEvent($smtp, optional()) -> returns($evt)
            -> one($dispatcher)->dispatchEvent($evt, 'transportStopped')
            -> ignoring($dispatcher)
            -> ignoring($evt)
            );
        $this->_finishBuffer($buf);
        $smtp->start();
        $smtp->stop();
    }

    public function testStoppingTransportDispatchesBeforeTransportChangeEvent()
    {
        $buf = $this->_getBuffer();
        $dispatcher = $this->_createEventDispatcher(false);
        $evt = $this->_mock('Swift_Events_TransportChangeEvent');
        $smtp = $this->_getTransport($buf, $dispatcher);
        $this->_checking(Expectations::create()
            -> allowing($dispatcher)->createTransportChangeEvent($smtp, optional()) -> returns($evt)
            -> one($dispatcher)->dispatchEvent($evt, 'beforeTransportStopped')
            -> ignoring($dispatcher)
            -> ignoring($evt)
            );
        $this->_finishBuffer($buf);
        $smtp->start();
        $smtp->stop();
    }

    public function testCancellingBubbleBeforeTransportStoppedStopsEvent()
    {
        $buf = $this->_getBuffer();
        $dispatcher = $this->_createEventDispatcher(false);
        $evt = $this->_mock('Swift_Events_TransportChangeEvent');
        $smtp = $this->_getTransport($buf, $dispatcher);
        $seq = $this->_sequence('stopping transport');
        $this->_checking(Expectations::create()
            -> allowing($dispatcher)->createTransportChangeEvent($smtp, optional()) -> returns($evt)
            -> one($dispatcher)->dispatchEvent($evt, 'beforeTransportStopped') -> inSequence($seq)
            -> allowing($evt)->bubbleCancelled() -> inSequence($seq) -> returns(true)
            -> ignoring($dispatcher)
            -> ignoring($evt)
            );
        $this->_finishBuffer($buf);
        $smtp->start();
        $smtp->stop();

        $this->assertTrue($smtp->isStarted(),
            '%s: Transport should not be stopped since event bubble was cancelled'
        );
    }

    public function testResponseEventsAreGenerated()
    {
        $buf = $this->_getBuffer();
        $dispatcher = $this->_createEventDispatcher(false);
        $evt = $this->_mock('Swift_Events_ResponseEvent');
        $smtp = $this->_getTransport($buf, $dispatcher);
        $this->_checking(Expectations::create()
            -> allowing($dispatcher)->createResponseEvent($smtp, optional()) -> returns($evt)
            -> one($dispatcher)->dispatchEvent($evt, 'responseReceived')
            -> ignoring($dispatcher)
            -> ignoring($evt)
            );
        $this->_finishBuffer($buf);
        $smtp->start();
    }

    public function testCommandEventsAreGenerated()
    {
        $buf = $this->_getBuffer();
        $dispatcher = $this->_createEventDispatcher(false);
        $evt = $this->_mock('Swift_Events_CommandEvent');
        $smtp = $this->_getTransport($buf, $dispatcher);
        $this->_checking(Expectations::create()
            -> allowing($dispatcher)->createCommandEvent($smtp, optional()) -> returns($evt)
            -> one($dispatcher)->dispatchEvent($evt, 'commandSent')
            -> ignoring($dispatcher)
            -> ignoring($evt)
            );
        $this->_finishBuffer($buf);
        $smtp->start();
    }

    public function testExceptionsCauseExceptionEvents()
    {
        $buf = $this->_getBuffer();
        $dispatcher = $this->_createEventDispatcher(false);
        $evt = $this->_mock('Swift_Events_TransportExceptionEvent');
        $smtp = $this->_getTransport($buf, $dispatcher);
        $this->_checking(Expectations::create()
            -> atLeast(1)->of($buf)->readLine(any()) -> returns("503 I'm sleepy, go away!\r\n")
            -> allowing($dispatcher)->createTransportExceptionEvent($smtp, optional()) -> returns($evt)
            -> one($dispatcher)->dispatchEvent($evt, 'exceptionThrown')
            -> ignoring($dispatcher)
            -> ignoring($evt)
            );
        $this->_finishBuffer($buf);
        try {
            $smtp->start();
            $this->fail('TransportException should be thrown on invalid response');
        } catch (Swift_TransportException $e) {
        }
    }

    public function testExceptionBubblesCanBeCancelled()
    {
        $buf = $this->_getBuffer();
        $dispatcher = $this->_createEventDispatcher(false);
        $evt = $this->_mock('Swift_Events_TransportExceptionEvent');
        $smtp = $this->_getTransport($buf, $dispatcher);
        $this->_checking(Expectations::create()
            -> atLeast(1)->of($buf)->readLine(any()) -> returns("503 I'm sleepy, go away!\r\n")
            -> allowing($dispatcher)->createTransportExceptionEvent($smtp, optional()) -> returns($evt)
            -> one($dispatcher)->dispatchEvent($evt, 'exceptionThrown')
            -> atLeast(1)->of($evt)->bubbleCancelled() -> returns(true)
            -> ignoring($dispatcher)
            -> ignoring($evt)
            );
        $this->_finishBuffer($buf);
        $smtp->start();
    }

    // -- Creation Methods

    protected function _createEventDispatcher($stub = true)
    {
        return $stub
            ? $this->_stub('Swift_Events_EventDispatcher')
            : $this->_mock('Swift_Events_EventDispatcher')
            ;
    }
}
