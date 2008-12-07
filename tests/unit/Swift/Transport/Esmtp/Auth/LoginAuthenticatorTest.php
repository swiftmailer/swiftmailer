<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/EsmtpBufferWrapper.php';
require_once 'Swift/Transport/Esmtp/Auth/LoginAuthenticator.php';
require_once 'Swift/Transport/TransportException.php';

class Swift_Transport_Esmtp_Auth_LoginAuthenticatorTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_buffer;
  
  public function setUp()
  {
    $this->_buffer = $this->_mock('Swift_Transport_EsmtpBufferWrapper');
  }
  
  public function testKeywordIsLogin()
  {
    $login = $this->_getAuthenticator();
    $this->assertEqual('LOGIN', $login->getAuthKeyword());
  }
  
  public function testSuccessfulAuthentication()
  {
    $login = $this->_getAuthenticator();
    $this->_checking(Expectations::create()
      -> one($this->_buffer)->executeCommand("AUTH LOGIN\r\n", array(334))
      -> one($this->_buffer)->executeCommand(base64_encode('jack') . "\r\n", array(334))
      -> one($this->_buffer)->executeCommand(base64_encode('pass') . "\r\n", array(235))
      );
    
    $this->assertTrue($login->authenticate($this->_buffer, 'jack', 'pass'),
      '%s: The buffer accepted all commands authentication should succeed'
      );
  }
  
  public function testAuthenticationFailureSendRsetAndReturnFalse()
  {
    $login = $this->_getAuthenticator();
    $this->_checking(Expectations::create()
      -> one($this->_buffer)->executeCommand("AUTH LOGIN\r\n", array(334))
      -> one($this->_buffer)->executeCommand(base64_encode('jack') . "\r\n", array(334))
      -> one($this->_buffer)->executeCommand(base64_encode('pass') . "\r\n", array(235))
        -> throws(new Swift_Transport_TransportException(""))
      -> one($this->_buffer)->executeCommand("RSET\r\n", array(250))
      );
    
    $this->assertFalse($login->authenticate($this->_buffer, 'jack', 'pass'),
      '%s: Authentication fails, so RSET should be sent'
      );
  }
  
  // -- Private helpers
  
  private function _getAuthenticator()
  {
    return new Swift_Transport_Esmtp_Auth_LoginAuthenticator();
  }
  
}
