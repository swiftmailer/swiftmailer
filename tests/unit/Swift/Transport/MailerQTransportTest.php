<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';

class Swift_Transport_MailerQTransportTest extends Swift_Tests_SwiftUnitTestCase
{
    public function testThisMethod()
    {
        $dispatcher = $this->_createEventDispatcher();
        $transport = $this->_createTransport($dispatcher, 'localhost', 'outbox', 'guest', 'guest', '/');

        $to = $this->_createHeader();
        $headers = $this->_createHeaders(array(
            'To' => $to
        ));
        
        $message = $this->_createMessage($headers);
    }

    // -- Creation Methods

    private function _createTransport($dispatcher, $hostname, $exchange, $login, $password, $vhost)
    {
        return new Swift_Transport_MailerQTransport($dispatcher, $hostname, $exchange, $login, $password, $vhost);
    }

    private function _createEventDispatcher()
    {
        return $this->_mock('Swift_Events_EventDispatcher');
    }

    private function _createMessage($headers)
    {
        $message = $this->_mock('Swift_Mime_Message');

        $this->_checking(Expectations::create()
            -> allowing($message)->getHeaders() -> returns($headers)
        );
        
        return $message;
    }

    private function _createHeaders($headers = array())
    {
        $set = $this->_mock('Swift_Mime_HeaderSet');

        if (count($headers) > 0) {
            foreach ($headers as $name => $header) {
                $this->_checking(Expectations::create()
                    -> allowing($set)->get($name) -> returns($header)
                    -> allowing($set)->has($name) -> returns(true)
                );
            }
        }

        $header = $this->_createHeader();
        $this->_checking(Expectations::create()
            -> allowing($set)->get(any()) -> returns($header)
            -> allowing($set)->has(any()) -> returns(true)
            -> ignoring($header)
        );

        return $set;
    }

    private function _createHeader()
    {
        return $this->_mock('Swift_Mime_Header');
    }
}
