<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/SmtpAgent.php';
require_once 'Swift/Transport/Esmtp/Auth/PlainAuthenticator.php';
require_once 'Swift/TransportException.php';

class Swift_Transport_Esmtp_Auth_PlainAuthenticatorTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_agent;
  
  public function setUp()
  {
    $this->_agent = $this->_mock('Swift_Transport_SmtpAgent');
  }
  
  public function testKeywordIsPlain()
  {
    /* -- RFC 4616, 1.
    The name associated with this mechanism is "PLAIN".
    */
    
    $login = $this->_getAuthenticator();
    $this->assertEqual('PLAIN', $login->getAuthKeyword());
  }
  
  public function testSuccessfulAuthentication()
  {
    /* -- RFC 4616, 2.
    The client presents the authorization identity (identity to act as),
    followed by a NUL (U+0000) character, followed by the authentication
    identity (identity whose password will be used), followed by a NUL
    (U+0000) character, followed by the clear-text password.
    */
    
    $plain = $this->_getAuthenticator();
    $this->_checking(Expectations::create()
      -> one($this->_agent)->executeCommand('AUTH PLAIN ' . base64_encode(
        'jack' . chr(0) . 'jack' . chr(0) . 'pass'
        ) . "\r\n", array(235))
      );
    
    $this->assertTrue($plain->authenticate($this->_agent, 'jack', 'pass'),
      '%s: The buffer accepted all commands authentication should succeed'
      );
  }
  
  public function testAuthenticationFailureSendRsetAndReturnFalse()
  {
    $plain = $this->_getAuthenticator();
    $this->_checking(Expectations::create()
      -> one($this->_agent)->executeCommand('AUTH PLAIN ' . base64_encode(
        'jack' . chr(0) . 'jack' . chr(0) . 'pass'
        ) . "\r\n", array(235)) -> throws(new Swift_TransportException(""))
      
      -> one($this->_agent)->executeCommand("RSET\r\n", array(250))
      );
    
    $this->assertFalse($plain->authenticate($this->_agent, 'jack', 'pass'),
      '%s: Authentication fails, so RSET should be sent'
      );
  }
  
  // -- Private helpers
  
  private function _getAuthenticator()
  {
    return new Swift_Transport_Esmtp_Auth_PlainAuthenticator();
  }
  
}
