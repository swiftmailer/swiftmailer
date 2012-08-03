<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/FailoverTransport.php';
require_once 'Swift/TransportException.php';
require_once 'Swift/Transport.php';
require_once 'Swift/Events/EventListener.php';

class Swift_Transport_FailoverTransportTest
    extends Swift_Tests_SwiftUnitTestCase
{
    public function testFirstTransportIsUsed()
    {
        $context = new Mockery();
        $message1 = $context->mock('Swift_Mime_Message');
        $message2 = $context->mock('Swift_Mime_Message');
        $t1 = $context->mock('Swift_Transport');
        $t2 = $context->mock('Swift_Transport');
        $con = $context->states('Connection')->startsAs('off');
        $context->checking(Expectations::create()
            -> ignoring($message1)
            -> ignoring($message2)
            -> allowing($t1)->isStarted() -> returns(false) -> when($con->is('off'))
            -> allowing($t1)->isStarted() -> returns(true) -> when($con->is('on'))
            -> one($t1)->start() -> when($con->isNot('on')) -> then($con->is('on'))
            -> one($t1)->send($message1, optional()) -> returns(1) -> when($con->is('on'))
            -> one($t1)->send($message2, optional()) -> returns(1) -> when($con->is('on'))
            -> ignoring($t1)
            -> never($t2)->start()
            -> never($t2)->send(any(), optional())
            -> ignoring($t2)
            );

        $transport = $this->_getTransport(array($t1, $t2));
        $transport->start();
        $this->assertEqual(1, $transport->send($message1));
        $this->assertEqual(1, $transport->send($message2));
        $context->assertIsSatisfied();
    }

    public function testMessageCanBeTriedOnNextTransportIfExceptionThrown()
    {
        $e = new Swift_TransportException('b0rken');

        $context = new Mockery();
        $message = $context->mock('Swift_Mime_Message');
        $t1 = $context->mock('Swift_Transport');
        $t2 = $context->mock('Swift_Transport');
        $con1 = $context->states('Connection')->startsAs('off');
        $con2 = $context->states('Connection')->startsAs('off');
        $context->checking(Expectations::create()
            -> ignoring($message)
            -> allowing($t1)->isStarted() -> returns(false) -> when($con1->is('off'))
            -> allowing($t1)->isStarted() -> returns(true) -> when($con1->is('on'))
            -> one($t1)->start() -> when($con1->isNot('on')) -> then($con1->is('on'))
            -> one($t1)->send($message, optional()) -> throws($e) -> when($con1->is('on'))
            -> ignoring($t1)
            -> allowing($t2)->isStarted() -> returns(false) -> when($con2->is('off'))
            -> allowing($t2)->isStarted() -> returns(true) -> when($con2->is('on'))
            -> one($t2)->start() -> when($con2->isNot('on')) -> then($con2->is('on'))
            -> one($t2)->send($message, optional()) -> returns(1) -> when($con2->is('on'))
            -> ignoring($t2)
            );

        $transport = $this->_getTransport(array($t1, $t2));
        $transport->start();
        $this->assertEqual(1, $transport->send($message));
        $context->assertIsSatisfied();
    }

    public function testZeroIsReturnedIfTransportReturnsZero()
    {
        $context = new Mockery();
        $message = $context->mock('Swift_Mime_Message');
        $t1 = $context->mock('Swift_Transport');
        $con = $context->states('Connection')->startsAs('off');
        $context->checking(Expectations::create()
            -> ignoring($message)
            -> allowing($t1)->isStarted() -> returns(false) -> when($con->is('off'))
            -> allowing($t1)->isStarted() -> returns(true) -> when($con->is('on'))
            -> one($t1)->start() -> when($con->isNot('on')) -> then($con->is('on'))
            -> one($t1)->send($message, optional()) -> returns(0) -> when($con->is('on'))
            -> ignoring($t1)
            );

        $transport = $this->_getTransport(array($t1));
        $transport->start();
        $this->assertEqual(0, $transport->send($message));
        $context->assertIsSatisfied();
    }

    public function testTransportsWhichThrowExceptionsAreNotRetried()
    {
        $e = new Swift_TransportException('maur b0rken');

        $context = new Mockery();
        $message1 = $context->mock('Swift_Mime_Message');
        $message2 = $context->mock('Swift_Mime_Message');
        $message3 = $context->mock('Swift_Mime_Message');
        $message4 = $context->mock('Swift_Mime_Message');
        $t1 = $context->mock('Swift_Transport');
        $t2 = $context->mock('Swift_Transport');
        $con1 = $context->states('Connection')->startsAs('off');
        $con2 = $context->states('Connection')->startsAs('off');
        $context->checking(Expectations::create()
            -> ignoring($message1)
            -> ignoring($message2)
            -> ignoring($message3)
            -> ignoring($message4)
            -> allowing($t1)->isStarted() -> returns(false) -> when($con1->is('off'))
            -> allowing($t1)->isStarted() -> returns(true) -> when($con1->is('on'))
            -> one($t1)->start() -> when($con1->isNot('on')) -> then($con1->is('on'))
            -> one($t1)->send($message1, optional()) -> throws($e) -> when($con1->is('on'))
            -> never($t1)->send($message2, optional())
            -> never($t1)->send($message3, optional())
            -> never($t1)->send($message4, optional())
            -> ignoring($t1)
            -> allowing($t2)->isStarted() -> returns(false) -> when($con2->is('off'))
            -> allowing($t2)->isStarted() -> returns(true) -> when($con2->is('on'))
            -> one($t2)->start() -> when($con2->isNot('on')) -> then($con2->is('on'))
            -> one($t2)->send($message1, optional()) -> returns(1) -> when($con2->is('on'))
            -> one($t2)->send($message2, optional()) -> returns(1) -> when($con2->is('on'))
            -> one($t2)->send($message3, optional()) -> returns(1) -> when($con2->is('on'))
            -> one($t2)->send($message4, optional()) -> returns(1) -> when($con2->is('on'))
            -> ignoring($t2)
            );

        $transport = $this->_getTransport(array($t1, $t2));
        $transport->start();
        $this->assertEqual(1, $transport->send($message1));
        $this->assertEqual(1, $transport->send($message2));
        $this->assertEqual(1, $transport->send($message3));
        $this->assertEqual(1, $transport->send($message4));
    }

    public function testExceptionIsThrownIfAllTransportsDie()
    {
        $e = new Swift_TransportException('b0rken');

        $context = new Mockery();
        $message = $context->mock('Swift_Mime_Message');
        $t1 = $context->mock('Swift_Transport');
        $t2 = $context->mock('Swift_Transport');
        $con1 = $context->states('Connection')->startsAs('off');
        $con2 = $context->states('Connection')->startsAs('off');
        $context->checking(Expectations::create()
            -> ignoring($message)
            -> allowing($t1)->isStarted() -> returns(false) -> when($con1->is('off'))
            -> allowing($t1)->isStarted() -> returns(true) -> when($con1->is('on'))
            -> one($t1)->start() -> when($con1->isNot('on')) -> then($con1->is('on'))
            -> one($t1)->send($message, optional()) -> throws($e) -> when($con1->is('on'))
            -> ignoring($t1)
            -> allowing($t2)->isStarted() -> returns(false) -> when($con2->is('off'))
            -> allowing($t2)->isStarted() -> returns(true) -> when($con2->is('on'))
            -> one($t2)->start() -> when($con2->isNot('on')) -> then($con2->is('on'))
            -> one($t2)->send($message, optional()) -> throws($e) -> when($con2->is('on'))
            -> ignoring($t2)
            );

        $transport = $this->_getTransport(array($t1, $t2));
        $transport->start();
        try {
            $transport->send($message);
            $this->fail('All transports failed so Exception should be thrown');
        } catch (Exception $e) {
        }
        $context->assertIsSatisfied();
    }

    public function testStoppingTransportStopsAllDelegates()
    {
        $context = new Mockery();
        $t1 = $context->mock('Swift_Transport');
        $t2 = $context->mock('Swift_Transport');
        $con1 = $context->states('Connection')->startsAs('on');
        $con2 = $context->states('Connection')->startsAs('on');
        $context->checking(Expectations::create()
            -> allowing($t1)->isStarted() -> returns(true) -> when($con1->is('on'))
            -> one($t1)->stop() -> when($con1->is('on')) -> then($con1->is('off'))
            -> ignoring($t1)
            -> allowing($t2)->isStarted() -> returns(true) -> when($con2->is('on'))
            -> one($t2)->stop() -> when($con2->is('on')) -> then($con2->is('off'))
            -> ignoring($t2)
            );

        $transport = $this->_getTransport(array($t1, $t2));
        $transport->start();
        $transport->stop();
        $context->assertIsSatisfied();
    }

    public function testTransportShowsAsNotStartedIfAllDelegatesDead()
    {
        $e = new Swift_TransportException('b0rken');

        $context = new Mockery();
        $message = $context->mock('Swift_Mime_Message');
        $t1 = $context->mock('Swift_Transport');
        $t2 = $context->mock('Swift_Transport');
        $con1 = $context->states('Connection')->startsAs('off');
        $con2 = $context->states('Connection')->startsAs('off');
        $context->checking(Expectations::create()
            -> ignoring($message)
            -> allowing($t1)->isStarted() -> returns(false) -> when($con1->is('off'))
            -> allowing($t1)->isStarted() -> returns(true) -> when($con1->is('on'))
            -> one($t1)->start() -> when($con1->isNot('on')) -> then($con1->is('on'))
            -> one($t1)->send($message, optional()) -> throws($e) -> when($con1->is('on'))
            -> ignoring($t1)
            -> allowing($t2)->isStarted() -> returns(false) -> when($con2->is('off'))
            -> allowing($t2)->isStarted() -> returns(true) -> when($con2->is('on'))
            -> one($t2)->start() -> when($con2->isNot('on')) -> then($con2->is('on'))
            -> one($t2)->send($message, optional()) -> throws($e) -> when($con2->is('on'))
            -> ignoring($t2)
            );

        $transport = $this->_getTransport(array($t1, $t2));
        $transport->start();
        $this->assertTrue($transport->isStarted());
        try {
            $transport->send($message);
            $this->fail('All transports failed so Exception should be thrown');
        } catch (Exception $e) {
            $this->assertFalse($transport->isStarted());
        }
        $context->assertIsSatisfied();
    }

    public function testRestartingTransportRestartsDeadDelegates()
    {
        $e = new Swift_TransportException('b0rken');

        $context = new Mockery();
        $message1 = $context->mock('Swift_Mime_Message');
        $message2 = $context->mock('Swift_Mime_Message');
        $t1 = $context->mock('Swift_Transport');
        $t2 = $context->mock('Swift_Transport');
        $con1 = $context->states('Connection')->startsAs('off');
        $con2 = $context->states('Connection')->startsAs('off');
        $context->checking(Expectations::create()
            -> ignoring($message1)
            -> ignoring($message2)
            -> allowing($t1)->isStarted() -> returns(false) -> when($con1->is('off'))
            -> allowing($t1)->isStarted() -> returns(true) -> when($con1->is('on'))
            -> exactly(2)->of($t1)->start() -> when($con1->isNot('on')) -> then($con1->is('on'))
            -> one($t1)->send($message1, optional()) -> throws($e) -> when($con1->is('on')) -> then($con1->is('off'))
            -> one($t1)->send($message2, optional()) -> returns(10) -> when($con1->is('on'))
            -> ignoring($t1)
            -> allowing($t2)->isStarted() -> returns(false) -> when($con2->is('off'))
            -> allowing($t2)->isStarted() -> returns(true) -> when($con2->is('on'))
            -> one($t2)->start() -> when($con2->isNot('on')) -> then($con2->is('on'))
            -> one($t2)->send($message1, optional()) -> throws($e) -> when($con2->is('on'))
            -> never($t2)->send($message2, optional())
            -> ignoring($t2)
            );

        $transport = $this->_getTransport(array($t1, $t2));
        $transport->start();
        $this->assertTrue($transport->isStarted());
        try {
            $transport->send($message1);
            $this->fail('All transports failed so Exception should be thrown');
        } catch (Exception $e) {
            $this->assertFalse($transport->isStarted());
        }
        //Restart and re-try
        $transport->start();
        $this->assertTrue($transport->isStarted());
        $this->assertEqual(10, $transport->send($message2));
        $context->assertIsSatisfied();
    }

    public function testFailureReferenceIsPassedToDelegates()
    {
        $failures = array();

        $context = new Mockery();
        $message = $context->mock('Swift_Mime_Message');
        $t1 = $context->mock('Swift_Transport');
        $con = $context->states('Connection')->startsAs('off');
        $context->checking(Expectations::create()
            -> ignoring($message)
            -> allowing($t1)->isStarted() -> returns(false) -> when($con->is('off'))
            -> allowing($t1)->isStarted() -> returns(true) -> when($con->is('on'))
            -> one($t1)->start() -> when($con->isNot('on')) -> then($con->is('on'))
            -> one($t1)->send($message, reference($failures)) -> returns(1) -> when($con->is('on'))
            -> ignoring($t1)
            );

        $transport = $this->_getTransport(array($t1));
        $transport->start();
        $transport->send($message, $failures);
        $context->assertIsSatisfied();
    }

    public function testRegisterPluginDelegatesToLoadedTransports()
    {
        $context = new Mockery();

        $plugin = $this->_createPlugin($context);

        $t1 = $context->mock('Swift_Transport');
        $t2 = $context->mock('Swift_Transport');
        $context->checking(Expectations::create()
            -> one($t1)->registerPlugin($plugin)
            -> one($t2)->registerPlugin($plugin)
            -> ignoring($t1)
            -> ignoring($t2)
            );

        $transport = $this->_getTransport(array($t1, $t2));
        $transport->registerPlugin($plugin);

        $context->assertIsSatisfied();
    }

    // -- Private helpers

    private function _getTransport(array $transports)
    {
        $transport = new Swift_Transport_FailoverTransport();
        $transport->setTransports($transports);

        return $transport;
    }

    private function _createPlugin($context)
    {
        return $context->mock('Swift_Events_EventListener');
    }

    private function _createInnerTransport()
    {
        return $this->_mockery()->mock('Swift_Transport');
    }
}
