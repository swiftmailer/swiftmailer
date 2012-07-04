<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Events/TransportExceptionEvent.php';
require_once 'Swift/Transport.php';
require_once 'Swift/TransportException.php';

class Swift_Events_TransportExceptionEventTest extends Swift_Tests_SwiftUnitTestCase
{
    public function testExceptionCanBeFetchViaGetter()
    {
        $ex = $this->_createException();
        $transport = $this->_createTransport();
        $evt = $this->_createEvent($transport, $ex);
        $ref = $evt->getException();
        $this->assertReference($ex, $ref,
            '%s: Exception should be available via getException()'
            );
    }

    public function testSourceIsTransport()
    {
        $ex = $this->_createException();
        $transport = $this->_createTransport();
        $evt = $this->_createEvent($transport, $ex);
        $ref = $evt->getSource();
        $this->assertReference($transport, $ref,
            '%s: Transport should be available via getSource()'
            );
    }

    // -- Creation Methods

    private function _createEvent(Swift_Transport $transport,
        Swift_TransportException $ex)
    {
        return new Swift_Events_TransportExceptionEvent($transport, $ex);
    }

    private function _createTransport()
    {
        return $this->_stub('Swift_Transport');
    }

    private function _createException()
    {
        return new Swift_TransportException('');
    }
}
