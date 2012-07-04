<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Events/CommandEvent.php';
require_once 'Swift/Transport.php';

class Swift_Events_CommandEventTest extends Swift_Tests_SwiftUnitTestCase
{
    public function testCommandCanBeFetchedByGetter()
    {
        $evt = $this->_createEvent($this->_createTransport(), "FOO\r\n");
        $this->assertEqual("FOO\r\n", $evt->getCommand());
    }

    public function testSuccessCodesCanBeFetchedViaGetter()
    {
        $evt = $this->_createEvent($this->_createTransport(), "FOO\r\n", array(250));
        $this->assertEqual(array(250), $evt->getSuccessCodes());
    }

    public function testSourceIsBuffer()
    {
        $transport = $this->_createTransport();
        $evt = $this->_createEvent($transport, "FOO\r\n");
        $ref = $evt->getSource();
        $this->assertReference($transport, $ref);
    }

    // -- Creation Methods

    private function _createEvent(Swift_Transport $source, $command,
        $successCodes = array())
    {
        return new Swift_Events_CommandEvent($source, $command, $successCodes);
    }

    private function _createTransport()
    {
        return $this->_stub('Swift_Transport');
    }
}
