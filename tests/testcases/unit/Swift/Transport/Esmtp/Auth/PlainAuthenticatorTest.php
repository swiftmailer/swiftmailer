<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/EsmtpBufferWrapper.php';
require_once 'Swift/Transport/Esmtp/Auth/PlainAuthenticator.php';
require_once 'Swift/Transport/TransportException.php';

Mock::generate('Swift_Transport_EsmtpBufferWrapper',
  'Swift_Transport_MockEsmtpBufferWrapper'
  );

class Swift_Transport_Esmtp_Auth_PlainAuthenticatorTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_buffer;
  
  public function setUp()
  {
    $this->_buffer = new Swift_Transport_MockEsmtpBufferWrapper();
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
    $this->_buffer->expectOnce(
      'executeCommand', array("AUTH PLAIN " . base64_encode(
        'jack' . chr(0) . 'jack' . chr(0) . 'pass'
        ) . "\r\n", array(235))
      );
    
    $this->assertTrue($plain->authenticate($this->_buffer, 'jack', 'pass'),
      '%s: The buffer accepted all commands authentication should succeed'
      );
  }
  
  public function testAuthenticationFailureSendRsetAndReturnFalse()
  {
    $e = new Swift_Transport_TransportException("");
    
    $login = $this->_getAuthenticator();
    $this->_buffer->expectAt(0,
      'executeCommand', array("AUTH PLAIN " . base64_encode(
        'jack' . chr(0) . 'jack' . chr(0) . 'pass'
        ) . "\r\n", array(235))
      );
    $this->_buffer->throwOn(
      'executeCommand', $e, array("AUTH PLAIN " . base64_encode(
        'jack' . chr(0) . 'jack' . chr(0) . 'pass'
        ) . "\r\n", array(235))
      );
    $this->_buffer->expectAt(1,
      'executeCommand', array("RSET\r\n", array(250))
      );
    $this->_buffer->expectMinimumCallCount('executeCommand', 2);
    
    $this->assertFalse($login->authenticate($this->_buffer, 'jack', 'pass'),
      '%s: The buffer accepted all commands authentication should succeed'
      );
  }
  
  // -- Private helpers
  
  private function _getAuthenticator()
  {
    return new Swift_Transport_Esmtp_Auth_PlainAuthenticator();
  }
  
}
