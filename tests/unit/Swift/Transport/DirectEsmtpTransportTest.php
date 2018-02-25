<?php

class Swift_Transport_DirectEsmtpTransportTest extends \SwiftMailerTestCase
{
    protected function getTransport($dispatcher = null, Swift_Transport_EsmtpTransport $smtp = null)
    {
        $dispatcher = $dispatcher ?? $this->createEventDispatcher();
        $smtp = $smtp ?? $this->createEsmtpTransport();

        return $this->getMockery(
                'Swift_Transport_DirectEsmtpTransport',
                [$dispatcher, $smtp, new Swift_AddressEncoder_IdnAddressEncoder()]
            )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    protected function createEventDispatcher($stub = true)
    {
        return $this->getMockery('Swift_Events_EventDispatcher')->shouldIgnoreMissing();
    }

    protected function createEsmtpTransport()
    {
        return $this->getMockery('Swift_Transport_EsmtpTransport')->shouldIgnoreMissing();
    }

    protected function createMessage()
    {
        return $this->getMockery('Swift_Mime_SimpleMessage')->shouldIgnoreMissing();
    }

    public function testRecipients()
    {
        $message = $this->createMessage();
        $message->shouldReceive('getReversePath')
            ->zeroOrMoreTimes()
            ->andReturn('me@domain');
        $message->shouldReceive('getTo')
            ->zeroOrMoreTimes()
            ->andReturn(['alice@example.com' => 'Alice']);
        $message->shouldReceive('getCc')
            ->zeroOrMoreTimes()
            ->andReturn(['bob@example.com' => 'Bob']);
        $message->shouldReceive('getBcc')
            ->zeroOrMoreTimes()
            ->andReturn(['oscar@example.org' => 'Oscar']);

        $direct = $this->getTransport();
        $direct->shouldReceive('groupRecipientsByMxHosts')
           ->once()
           ->with(
                ['alice@example.com' => 'Alice', 'bob@example.com' => 'Bob'],
                ['oscar@example.org' => 'Oscar']
            );

        $direct->send($message);
    }

    public function testSendingToMultipleDomains()
    {
        $message = $this->createMessage();
        $message->shouldReceive('getReversePath')
            ->zeroOrMoreTimes()
            ->andReturn('me@domain');
        $message->shouldReceive('getTo')
            ->zeroOrMoreTimes()
            ->andReturn(['alice@example.com' => 'Alice', 'oscar@example.org' => 'Oscar']);
        $message->shouldReceive('getBcc')
            ->zeroOrMoreTimes()
            ->andReturn(['bob@example.com' => 'Bob']);

        $dispatcher = $this->createEventDispatcher(false);
        $smtp = $this->createEsmtpTransport();
        $direct = $this->getTransport($dispatcher, $smtp);

        $evt = $this->getMockery('Swift_Events_SendEvent')->shouldIgnoreMissing();
        $dispatcher->shouldReceive('createSendEvent')
                   ->once()
                   ->andReturn($evt);
        $dispatcher->shouldReceive('dispatchEvent')
                   ->once()
                   ->with($evt, 'beforeSendPerformed');
        $evt->shouldReceive('bubbleCancelled')
            ->once()
            ->andReturn(false);

        $direct->shouldReceive('groupRecipientsByMxHosts')
           ->once()
           ->with(
                ['alice@example.com' => 'Alice', 'oscar@example.org' => 'Oscar'],
                ['bob@example.com' => 'Bob']
            )
           ->andReturn([
                'example.com' => [
                    'tos' => ['alice@example.com'],
                    'bcc' => ['bob@example.com'],
                    'hosts' => ['smtp.example.com']
                ],
                'example.org' => [
                    'tos' => ['oscar@example.org'],
                    'bcc' => [],
                    'hosts' => ['smtp.example.org']
                ],
            ]);

        $smtp->shouldReceive('setHost')
            ->with('smtp.example.com')
            ->once();
        $smtp->shouldReceive('setHost')
            ->with('smtp.example.org')
            ->once();

        $smtp->shouldReceive('isStarted')
            ->twice()
            ->andReturn(false);
        $smtp->shouldReceive('start')
            ->twice();

        $smtp->shouldReceive('sendCopy')
            ->with($message, 'me@domain', ['alice@example.com'], ['bob@example.com'], [])
            ->once()
            ->andReturn(2);
        $smtp->shouldReceive('sendCopy')
            ->with($message, 'me@domain', ['oscar@example.org'], [], [])
            ->once()
            ->andReturn(1);

        $smtp->shouldReceive('stop')
            ->twice();

        $evt->shouldReceive('setResult')
            ->with(Swift_Events_SendEvent::RESULT_SUCCESS)
            ->once();
        $dispatcher->shouldReceive('dispatchEvent')
                   ->once()
                   ->with($evt, 'sendPerformed');

        $sent = $direct->send($message);

        $this->assertSame(3, $sent);
    }

    public function testFailoverToSecondoryHostsForDomain()
    {
        $message = $this->createMessage();
        $message->shouldReceive('getReversePath')
            ->zeroOrMoreTimes()
            ->andReturn('me@domain');
        $message->shouldReceive('getTo')
            ->zeroOrMoreTimes()
            ->andReturn(['alice@example.com' => 'Alice']);

        $dispatcher = $this->createEventDispatcher(false);
        $smtp = $this->createEsmtpTransport();
        $direct = $this->getTransport($dispatcher, $smtp);

        $evt = $this->getMockery('Swift_Events_SendEvent')->shouldIgnoreMissing();
        $dispatcher->shouldReceive('createSendEvent')
                   ->once()
                   ->andReturn($evt);
        $dispatcher->shouldReceive('dispatchEvent')
                   ->once()
                   ->with($evt, 'beforeSendPerformed');
        $evt->shouldReceive('bubbleCancelled')
            ->once()
            ->andReturn(false);

        $direct->shouldReceive('groupRecipientsByMxHosts')
           ->once()
           ->andReturn([
                'example.com' => [
                    'tos' => ['alice@example.com'],
                    'bcc' => [],
                    'hosts' => ['smtp1.example.com', 'smtp2.example.com', 'smtp3.example.com']
                ],
            ]);

        $smtp->shouldReceive('setHost')
            ->with('smtp1.example.com')
            ->once();
        $smtp->shouldReceive('setHost')
            ->with('smtp2.example.com')
            ->once();
        $smtp->shouldReceive('setHost')
            ->with('smtp3.example.com')
            ->never();

        $smtp->shouldReceive('isStarted')
            ->twice()
            ->andReturn(false);
        $smtp->shouldReceive('start')
            ->twice();

        $i = 0;
        $smtp->shouldReceive('sendCopy')
            ->with($message, 'me@domain', ['alice@example.com'], [], \Mockery::any())
            ->twice()
            ->andReturnUsing(function () use (&$i) {
                if ($i++ == 0) {
                    throw new Swift_TransportException('smtp1 is down');
                } else {
                    return 1;
                }
            });

        $smtp->shouldReceive('stop')
            ->twice();

        $evt->shouldReceive('setResult')
            ->with(Swift_Events_SendEvent::RESULT_SUCCESS)
            ->once();
        $dispatcher->shouldReceive('dispatchEvent')
                   ->once()
                   ->with($evt, 'sendPerformed');

        $sent = $direct->send($message, $failedRecipients);

        $this->assertSame(1, $sent);
        $this->assertEmpty($failedRecipients);
    }

    public function testAllHostsForDomainFail()
    {
        $message = $this->createMessage();
        $message->shouldReceive('getReversePath')
            ->zeroOrMoreTimes()
            ->andReturn('me@domain');
        $message->shouldReceive('getTo')
            ->zeroOrMoreTimes()
            ->andReturn(['alice@example.com' => 'Alice']);

        $dispatcher = $this->createEventDispatcher(false);
        $smtp = $this->createEsmtpTransport();
        $direct = $this->getTransport($dispatcher, $smtp);

        $evt = $this->getMockery('Swift_Events_SendEvent')->shouldIgnoreMissing();
        $dispatcher->shouldReceive('createSendEvent')
                   ->once()
                   ->andReturn($evt);
        $dispatcher->shouldReceive('dispatchEvent')
                   ->once()
                   ->with($evt, 'beforeSendPerformed');
        $evt->shouldReceive('bubbleCancelled')
            ->once()
            ->andReturn(false);

        $direct->shouldReceive('groupRecipientsByMxHosts')
           ->once()
           ->andReturn([
                'example.com' => [
                    'tos' => ['alice@example.com'],
                    'bcc' => [],
                    'hosts' => ['smtp1.example.com', 'smtp2.example.com']
                ],
            ]);

        $smtp->shouldReceive('setHost')
            ->with('smtp1.example.com')
            ->once();
        $smtp->shouldReceive('setHost')
            ->with('smtp2.example.com')
            ->once();

        $smtp->shouldReceive('isStarted')
            ->twice()
            ->andReturn(false);
        $smtp->shouldReceive('start')
            ->twice();

        $smtp->shouldReceive('sendCopy')
            ->with($message, 'me@domain', ['alice@example.com'], [], \Mockery::any())
            ->twice()
            ->andThrow(new Swift_TransportException('smtp is down'));

        $smtp->shouldReceive('stop')
            ->twice();

        $evt->shouldReceive('setResult')
            ->with(Swift_Events_SendEvent::RESULT_FAILED)
            ->once();
        $dispatcher->shouldReceive('dispatchEvent')
                   ->once()
                   ->with($evt, 'sendPerformed');

        $sent = $direct->send($message, $failedRecipients);

        $this->assertSame(0, $sent);
        $this->assertSame(['alice@example.com'], $failedRecipients);
    }

    public function testSomeDomainsFail()
    {
        $message = $this->createMessage();
        $message->shouldReceive('getReversePath')
            ->zeroOrMoreTimes()
            ->andReturn('me@domain');

        $dispatcher = $this->createEventDispatcher(false);
        $smtp = $this->createEsmtpTransport();
        $direct = $this->getTransport($dispatcher, $smtp);

        $evt = $this->getMockery('Swift_Events_SendEvent')->shouldIgnoreMissing();
        $dispatcher->shouldReceive('createSendEvent')
                   ->once()
                   ->andReturn($evt);
        $dispatcher->shouldReceive('dispatchEvent')
                   ->once()
                   ->with($evt, 'beforeSendPerformed');
        $evt->shouldReceive('bubbleCancelled')
            ->once()
            ->andReturn(false);

        $direct->shouldReceive('groupRecipientsByMxHosts')
           ->once()
           ->andReturn([
                'example.com' => [
                    'tos' => ['alice@example.com'],
                    'bcc' => [],
                    'hosts' => ['smtp.example.com']
                ],
                'example.org' => [
                    'tos' => ['bob@example.org'],
                    'bcc' => [],
                    'hosts' => ['smtp.example.org']
                ],
            ]);

        $smtp->shouldReceive('setHost')
            ->with('smtp.example.com')
            ->once();
        $smtp->shouldReceive('setHost')
            ->with('smtp.example.org')
            ->once();

        $smtp->shouldReceive('isStarted')
            ->twice()
            ->andReturn(false);
        $smtp->shouldReceive('start')
            ->twice();

        $smtp->shouldReceive('sendCopy')
            ->with($message, 'me@domain', ['alice@example.com'], [], \Mockery::any())
            ->once()
            ->andThrow(new Swift_TransportException('smtp is down'));
        $smtp->shouldReceive('sendCopy')
            ->with($message, 'me@domain', ['bob@example.org'], [], \Mockery::any())
            ->once()
            ->andReturn(1);

        $smtp->shouldReceive('stop')
            ->twice();

        $evt->shouldReceive('setResult')
            ->with(Swift_Events_SendEvent::RESULT_TENTATIVE)
            ->once();
        $dispatcher->shouldReceive('dispatchEvent')
                   ->once()
                   ->with($evt, 'sendPerformed');

        $sent = $direct->send($message, $failedRecipients);

        $this->assertSame(1, $sent);
        $this->assertSame(['alice@example.com'], $failedRecipients);
    }

    public function testGetMxHostsInvokedWithDomain()
    {
        $message = $this->createMessage();
        $message->shouldReceive('getReversePath')
            ->zeroOrMoreTimes()
            ->andReturn('me@domain');
        $message->shouldReceive('getTo')
            ->zeroOrMoreTimes()
            ->andReturn(['alice@example.com' => 'Alice']);

        $smtp = $this->createEsmtpTransport();
        $direct = $this->getTransport(null, $smtp);

        $direct->shouldReceive('getmxrr')
            ->with('example.com', \Mockery::any(), \Mockery::any())
            ->once()
            ->andReturnUsing(function ($domain, &$hosts, &$weights) {
                $hosts = ['smtp2.example.com', 'smtp1.example.com'];
                $weights = [20, 10];
                return true;
            });

        $smtp->shouldReceive('setHost')
            ->with('smtp1.example.com')
            ->once();
        $smtp->shouldReceive('setHost')
            ->with('smtp2.example.com')
            ->never();

        $sent = $direct->send($message);
    }

    public function testGetMxHostsForDomainWithoutMxHosts()
    {
        $message = $this->createMessage();
        $message->shouldReceive('getReversePath')
            ->zeroOrMoreTimes()
            ->andReturn('me@domain');
        $message->shouldReceive('getTo')
            ->zeroOrMoreTimes()
            ->andReturn(['alice@example.com' => 'Alice']);

        $smtp = $this->createEsmtpTransport();
        $direct = $this->getTransport(null, $smtp);

        $direct->shouldReceive('getmxrr')
            ->with('example.com', \Mockery::any(), \Mockery::any())
            ->once()
            ->andReturn(false);

        $smtp->shouldReceive('setHost')
            ->with('example.com')
            ->once();

        $sent = $direct->send($message);
    }

    public function testGetMxHostsForUtf8Domain()
    {
        $message = $this->createMessage();
        $message->shouldReceive('getReversePath')
            ->zeroOrMoreTimes()
            ->andReturn('me@domain');
        $message->shouldReceive('getTo')
            ->zeroOrMoreTimes()
            ->andReturn(['alice@exämple.com' => 'Alice']);

        $smtp = $this->createEsmtpTransport();
        $direct = $this->getTransport(null, $smtp);

        $direct->shouldReceive('getmxrr')
            ->with('xn--exmple-cua.com', \Mockery::any(), \Mockery::any())
            ->once()
             ->andReturnUsing(function ($domain, &$hosts, &$weights) {
                $hosts = ['smtp.example.com'];
                $weights = [10];
                return true;
            });

        $smtp->shouldReceive('setHost')
            ->with('smtp.example.com')
            ->once();

        $sent = $direct->send($message);
    }

    public function testRegisterPluginLoadsPluginInEventDispatcher()
    {
        $dispatcher = $this->createEventDispatcher(false);
        $listener = $this->getMockery('Swift_Events_EventListener');
        $direct = $this->getTransport($dispatcher);
        $dispatcher->shouldReceive('bindEventListener')
                   ->once()
                   ->with($listener);

        $direct->registerPlugin($listener);
    }

    public function testCancellingEventBubbleBeforeSendStopsEvent()
    {
        $dispatcher = $this->createEventDispatcher(false);
        $evt = $this->getMockery('Swift_Events_SendEvent')->shouldIgnoreMissing();
        $diredt = $this->getTransport($dispatcher);
        $message = $this->createMessage();

        $message->shouldReceive('getReversePath')
                ->zeroOrMoreTimes()
                ->andReturn('chris@swiftmailer.org');
        $message->shouldReceive('getTo')
                ->zeroOrMoreTimes()
                ->andReturn(['mark@swiftmailer.org' => 'Mark']);
        $dispatcher->shouldReceive('createSendEvent')
                   ->zeroOrMoreTimes()
                   ->with($diredt, \Mockery::any())
                   ->andReturn($evt);
        $dispatcher->shouldReceive('dispatchEvent')
                   ->once()
                   ->with($evt, 'beforeSendPerformed');
        $dispatcher->shouldReceive('dispatchEvent')
                   ->zeroOrMoreTimes();
        $evt->shouldReceive('bubbleCancelled')
            ->atLeast()->once()
            ->andReturn(true);

        $diredt->start();
        $this->assertEquals(0, $diredt->send($message));
    }

    public function testExceptionsCauseExceptionEvents()
    {
        $message = $this->createMessage();
        $message->shouldReceive('getReversePath')
            ->zeroOrMoreTimes()
            ->andReturn(false);

        $dispatcher = $this->createEventDispatcher(false);
        $evt = $this->getMockery('Swift_Events_TransportExceptionEvent');
        $diredt = $this->getTransport($dispatcher);

        $dispatcher->shouldReceive('createTransportExceptionEvent')
                   ->zeroOrMoreTimes()
                   ->with($diredt, \Mockery::any())
                   ->andReturn($evt);
        $dispatcher->shouldReceive('dispatchEvent')
                   ->once()
                   ->with($evt, 'exceptionThrown');
        $evt->shouldReceive('bubbleCancelled')
            ->atLeast()->once()
            ->andReturn(false);

        try {
            $diredt->send($message);
            $this->fail('TransportException should be thrown on invalid response');
        } catch (Swift_TransportException $e) {
        }
    }

    public function testExceptionBubblesCanBeCancelled()
    {
        $message = $this->createMessage();
        $message->shouldReceive('getReversePath')
            ->zeroOrMoreTimes()
            ->andReturn(false);

        $dispatcher = $this->createEventDispatcher(false);
        $evt = $this->getMockery('Swift_Events_TransportExceptionEvent');
        $diredt = $this->getTransport($dispatcher);

        $dispatcher->shouldReceive('createTransportExceptionEvent')
                   ->once()
                   ->with($diredt, \Mockery::any())
                   ->andReturn($evt);
        $dispatcher->shouldReceive('dispatchEvent')
                   ->once()
                   ->with($evt, 'exceptionThrown');
        $evt->shouldReceive('bubbleCancelled')
            ->atLeast()->once()
            ->andReturn(true);

        $diredt->send($message);
    }
}
