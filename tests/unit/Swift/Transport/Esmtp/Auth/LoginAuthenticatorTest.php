<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/SmtpAgent.php';
require_once 'Swift/Transport/Esmtp/Auth/LoginAuthenticator.php';
require_once 'Swift/TransportException.php';

class Swift_Transport_Esmtp_Auth_LoginAuthenticatorTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_agent;
  
  public function setUp()
  {
    $this->_agent = $this->_mock('Swift_Transport_SmtpAgent');
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
      -> one($this->_agent)->executeCommand("AUTH LOGIN\r\n", array(334))
      -> one($this->_agent)->executeCommand(base64_encode('jack') . "\r\n", array(334))
      -> one($this->_agent)->executeCommand(base64_encode('pass') . "\r\n", array(235))
      );
    
    $this->assertTrue($login->authenticate($this->_agent, 'jack', 'pass'),
      '%s: The buffer accepted all commands authentication should succeed'
      );
  }
  
  public function testAuthenticationFailureSendRsetAndReturnFalse()
  {
    $login = $this->_getAuthenticator();
    $this->_checking(Expectations::create()
      -> one($this->_agent)->executeCommand("AUTH LOGIN\r\n", array(334))
      -> one($this->_agent)->executeCommand(base64_encode('jack') . "\r\n", array(334))
      -> one($this->_agent)->executeCommand(base64_encode('pass') . "\r\n", array(235))
        -> throws(new Swift_TransportException(""))
      -> one($this->_agent)->executeCommand("RSET\r\n", array(250))
      );
    
    $this->assertFalse($login->authenticate($this->_agent, 'jack', 'pass'),
      '%s: Authentication fails, so RSET should be sent'
      );
  }
  
  // -- Private helpers
  
  private function _getAuthenticator()
  {
    return new Swift_Transport_Esmtp_Auth_LoginAuthenticator();
  }
  
}
