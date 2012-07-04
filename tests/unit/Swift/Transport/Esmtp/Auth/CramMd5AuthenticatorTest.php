<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/SmtpAgent.php';
require_once 'Swift/Transport/Esmtp/Auth/CramMd5Authenticator.php';
require_once 'Swift/TransportException.php';

class Swift_Transport_Esmtp_Auth_CramMd5AuthenticatorTest
    extends Swift_Tests_SwiftUnitTestCase
{
    private $_agent;

    public function setUp()
    {
        $this->_agent = $this->_mock('Swift_Transport_SmtpAgent');
    }

    public function testKeywordIsCramMd5()
    {
        /* -- RFC 2195, 2.
        The authentication type associated with CRAM is "CRAM-MD5".
        */

        $cram = $this->_getAuthenticator();
        $this->assertEqual('CRAM-MD5', $cram->getAuthKeyword());
    }

    public function testSuccessfulAuthentication()
    {
        $cram = $this->_getAuthenticator();
        $this->_checking(Expectations::create()
            -> one($this->_agent)->executeCommand("AUTH CRAM-MD5\r\n", array(334))
                -> returns('334 ' . base64_encode('<foo@bar>') . "\r\n")
            // The use of any() is controversial, but here to avoid crazy test logic
            -> one($this->_agent)->executeCommand(any(), array(235))
            );

        $this->assertTrue($cram->authenticate($this->_agent, 'jack', 'pass'),
            '%s: The buffer accepted all commands authentication should succeed'
            );
    }

    public function testAuthenticationFailureSendRsetAndReturnFalse()
    {
        $cram = $this->_getAuthenticator();
        $this->_checking(Expectations::create()
            -> one($this->_agent)->executeCommand("AUTH CRAM-MD5\r\n", array(334))
                -> returns('334 ' . base64_encode('<foo@bar>') . "\r\n")
            // The use of any() is controversial, but here to avoid crazy test logic
            -> one($this->_agent)->executeCommand(any(), array(235))
       -> throws(new Swift_TransportException(""))

            -> one($this->_agent)->executeCommand("RSET\r\n", array(250))
            );

        $this->assertFalse($cram->authenticate($this->_agent, 'jack', 'pass'),
            '%s: Authentication fails, so RSET should be sent'
            );
    }

    // -- Private helpers

    private function _getAuthenticator()
    {
        return new Swift_Transport_Esmtp_Auth_CramMd5Authenticator();
    }
}
