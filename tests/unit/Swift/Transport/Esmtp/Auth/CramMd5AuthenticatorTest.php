<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/EsmtpBufferWrapper.php';
require_once 'Swift/Transport/Esmtp/Auth/CramMd5Authenticator.php';
require_once 'Swift/Transport/TransportException.php';

Mock::generate('Swift_Transport_EsmtpBufferWrapper',
  'Swift_Transport_MockEsmtpBufferWrapper'
  );

class Swift_Transport_Esmtp_Auth_CramMd5AuthenticatorTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_buffer;
  
  public function setUp()
  {
    $this->_buffer = new Swift_Transport_MockEsmtpBufferWrapper();
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
    $this->_buffer->expectAt(0,
      'executeCommand', array("AUTH CRAM-MD5\r\n", array(334))
      );
    $this->_buffer->setReturnValue('executeCommand',
      '334 ' . base64_encode('<foo@bar>') . "\r\n", array("AUTH CRAM-MD5\r\n", array(334))
      );
    $this->_buffer->expectAt(1,
      'executeCommand', array('*', array(235))
      );
    $this->_buffer->expectMinimumCallCount('executeCommand', 2);
    
    $this->assertTrue($cram->authenticate($this->_buffer, 'jack', 'pass'),
      '%s: The buffer accepted all commands authentication should succeed'
      );
  }
  
  public function testAuthenticationFailureSendRsetAndReturnFalse()
  {
    $e = new Swift_Transport_TransportException("");
    
    $cram = $this->_getAuthenticator();
    $this->_buffer->expectAt(0,
      'executeCommand', array("AUTH CRAM-MD5\r\n", array(334))
      );
    $this->_buffer->setReturnValue('executeCommand',
      '334 ' . base64_encode('<foo@bar>') . "\r\n", array("AUTH CRAM-MD5\r\n", array(334))
      );
    $this->_buffer->expectAt(1,
      'executeCommand', array('*', array(235))
      );
    $this->_buffer->throwOn(
      'executeCommand', $e, array('*', array(235))
      );
    $this->_buffer->expectAt(2,
      'executeCommand', array("RSET\r\n", array(250))
      );
    $this->_buffer->expectMinimumCallCount('executeCommand', 3);
    
    $this->assertFalse($cram->authenticate($this->_buffer, 'jack', 'pass'),
      '%s: The buffer accepted all commands authentication should succeed'
      );
  }
  
  // -- Private helpers
  
  private function _getAuthenticator()
  {
    return new Swift_Transport_Esmtp_Auth_CramMd5Authenticator();
  }
  
}
