<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/EsmtpBufferWrapper.php';
require_once 'Swift/Transport/Esmtp/Auth/LoginAuthenticator.php';
require_once 'Swift/Transport/TransportException.php';

Mock::generate('Swift_Transport_EsmtpBufferWrapper',
  'Swift_Transport_MockEsmtpBufferWrapper'
  );

class Swift_Transport_Esmtp_Auth_LoginAuthenticatorTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_buffer;
  
  public function setUp()
  {
    $this->_buffer = new Swift_Transport_MockEsmtpBufferWrapper();
  }
  
  public function testKeywordIsLogin()
  {
    $login = $this->_getAuthenticator();
    $this->assertEqual('LOGIN', $login->getAuthKeyword());
  }
  
  public function testSuccessfulAuthentication()
  {
    $login = $this->_getAuthenticator();
    $this->_buffer->expectAt(0,
      'executeCommand', array("AUTH LOGIN\r\n", array(334))
      );
    $this->_buffer->expectAt(1,
      'executeCommand', array(base64_encode('jack') . "\r\n", array(334))
      );
    $this->_buffer->expectAt(2,
      'executeCommand', array(base64_encode('pass') . "\r\n", array(235))
      );
    $this->_buffer->expectMinimumCallCount('executeCommand', 3);
    
    $this->assertTrue($login->authenticate($this->_buffer, 'jack', 'pass'),
      '%s: The buffer accepted all commands authentication should succeed'
      );
  }
  
  public function testAuthenticationFailureSendRsetAndReturnFalse()
  {
    $e = new Swift_Transport_TransportException("");
    
    $login = $this->_getAuthenticator();
    $this->_buffer->expectAt(0,
      'executeCommand', array("AUTH LOGIN\r\n", array(334))
      );
    $this->_buffer->expectAt(1,
      'executeCommand', array(base64_encode('jack') . "\r\n", array(334))
      );
    $this->_buffer->expectAt(2,
      'executeCommand', array(base64_encode('pass') . "\r\n", array(235))
      );
    $this->_buffer->throwOn(
      'executeCommand', $e, array(base64_encode('pass') . "\r\n", array(235))
      );
    $this->_buffer->expectAt(3,
      'executeCommand', array("RSET\r\n", array(250))
      );
    $this->_buffer->expectMinimumCallCount('executeCommand', 4);
    
    $this->assertFalse($login->authenticate($this->_buffer, 'jack', 'pass'),
      '%s: The buffer accepted all commands authentication should succeed'
      );
  }
  
  // -- Private helpers
  
  private function _getAuthenticator()
  {
    return new Swift_Transport_Esmtp_Auth_LoginAuthenticator();
  }
  
}
