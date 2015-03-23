<?php

class Swift_Events_TransportExceptionEventTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionCanBeFetchViaGetter()
    {
        $ex = $this->createException();
        $transport = $this->createTransport();
        $evt = $this->createEvent($transport, $ex);
        $ref = $evt->getException();
        $this->assertEquals($ex, $ref,
            '%s: Exception should be available via getException()'
            );
    }

    public function testSourceIsTransport()
    {
        $ex = $this->createException();
        $transport = $this->createTransport();
        $evt = $this->createEvent($transport, $ex);
        $ref = $evt->getSource();
        $this->assertEquals($transport, $ref,
            '%s: Transport should be available via getSource()'
            );
    }

    // -- Creation Methods

    private function createEvent(Swift_Transport $transport, Swift_TransportException $ex)
    {
        return new Swift_Events_TransportExceptionEvent($transport, $ex);
    }

    private function createTransport()
    {
        return $this->getMock('Swift_Transport');
    }

    private function createException()
    {
        return new Swift_TransportException('');
    }
}
