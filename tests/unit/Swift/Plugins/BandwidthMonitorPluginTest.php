<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Plugins/BandwidthMonitorPlugin.php';
require_once 'Swift/Events/SendEvent.php';
require_once 'Swift/Events/CommandEvent.php';
require_once 'Swift/Events/ResponseEvent.php';
require_once 'Swift/Mime/Message.php';

class Swift_Plugins_BandwidthMonitorPluginTest
    extends Swift_Tests_SwiftUnitTestCase
{
    public function setUp()
    {
        $this->_monitor = new Swift_Plugins_BandwidthMonitorPlugin();
    }

    public function testBytesOutIncreasesAccordingToMessageLength()
    {
        $message = $this->_createMessageWithByteCount(6);
        $evt = $this->_createSendEvent($message);

        $this->assertEqual(0, $this->_monitor->getBytesOut());
        $this->_monitor->sendPerformed($evt);
        $this->assertEqual(6, $this->_monitor->getBytesOut());
        $this->_monitor->sendPerformed($evt);
        $this->assertEqual(12, $this->_monitor->getBytesOut());
    }

    public function testBytesOutIncreasesWhenCommandsSent()
    {
        $evt = $this->_createCommandEvent("RCPT TO: <foo@bar.com>\r\n");

        $this->assertEqual(0, $this->_monitor->getBytesOut());
        $this->_monitor->commandSent($evt);
        $this->assertEqual(24, $this->_monitor->getBytesOut());
        $this->_monitor->commandSent($evt);
        $this->assertEqual(48, $this->_monitor->getBytesOut());
    }

    public function testBytesInIncreasesWhenResponsesReceived()
    {
        $evt = $this->_createResponseEvent("250 Ok\r\n");

        $this->assertEqual(0, $this->_monitor->getBytesIn());
        $this->_monitor->responseReceived($evt);
        $this->assertEqual(8, $this->_monitor->getBytesIn());
        $this->_monitor->responseReceived($evt);
        $this->assertEqual(16, $this->_monitor->getBytesIn());
    }

    public function testCountersCanBeReset()
    {
        $evt = $this->_createResponseEvent("250 Ok\r\n");

        $this->assertEqual(0, $this->_monitor->getBytesIn());
        $this->_monitor->responseReceived($evt);
        $this->assertEqual(8, $this->_monitor->getBytesIn());
        $this->_monitor->responseReceived($evt);
        $this->assertEqual(16, $this->_monitor->getBytesIn());

        $evt = $this->_createCommandEvent("RCPT TO: <foo@bar.com>\r\n");

        $this->assertEqual(0, $this->_monitor->getBytesOut());
        $this->_monitor->commandSent($evt);
        $this->assertEqual(24, $this->_monitor->getBytesOut());
        $this->_monitor->commandSent($evt);
        $this->assertEqual(48, $this->_monitor->getBytesOut());

        $this->_monitor->reset();

        $this->assertEqual(0, $this->_monitor->getBytesOut());
        $this->assertEqual(0, $this->_monitor->getBytesIn());
    }

    // -- Creation Methods

    private function _createSendEvent($message)
    {
        $evt = $this->_mock('Swift_Events_SendEvent');
        $this->_checking(Expectations::create()
            -> ignoring($evt)->getMessage() -> returns($message)
            );
        return $evt;
    }

    private function _createCommandEvent($command)
    {
        $evt = $this->_mock('Swift_Events_CommandEvent');
        $this->_checking(Expectations::create()
            -> ignoring($evt)->getCommand() -> returns($command)
            );
        return $evt;
    }

    private function _createResponseEvent($response)
    {
        $evt = $this->_mock('Swift_Events_ResponseEvent');
        $this->_checking(Expectations::create()
            -> ignoring($evt)->getResponse() -> returns($response)
            );
        return $evt;
    }

    private function _createMessageWithByteCount($bytes)
    {
        $this->_bytes = $bytes;
        $msg = $this->_mock('Swift_Mime_Message');
        $this->_checking(Expectations::create()
            -> ignoring($msg)->toByteStream(any()) -> calls(array($this, '_write'))
        );
        return $msg;
    }

    private $_bytes = 0;
    public function _write($invocation)
    {
        $args = $invocation->getArguments();
        $is = $args[0];
        for ($i = 0; $i < $this->_bytes; ++$i) {
            $is->write('x');
        }
    }

}