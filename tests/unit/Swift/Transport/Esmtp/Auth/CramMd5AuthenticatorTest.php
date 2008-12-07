<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/EsmtpBufferWrapper.php';
require_once 'Swift/Transport/Esmtp/Auth/CramMd5Authenticator.php';
require_once 'Swift/Transport/TransportException.php';

class Swift_Transport_Esmtp_Auth_CramMd5AuthenticatorTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_buffer;
  
  public function setUp()
  {
    $this->_buffer = $this->_mock('Swift_Transport_EsmtpBufferWrapper');
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
      -> one($this->_buffer)->executeCommand("AUTH CRAM-MD5\r\n", array(334))
        -> returns('334 ' . base64_encode('<foo@bar>') . "\r\n")
      // The use of any() is controversial, but here to avoid crazy test logic
      -> one($this->_buffer)->executeCommand(any(), array(235))
      );
    
    $this->assertTrue($cram->authenticate($this->_buffer, 'jack', 'pass'),
      '%s: The buffer accepted all commands authentication should succeed'
      );
  }
  
  public function testAuthenticationFailureSendRsetAndReturnFalse()
  {
    $cram = $this->_getAuthenticator();
    $this->_checking(Expectations::create()
      -> one($this->_buffer)->executeCommand("AUTH CRAM-MD5\r\n", array(334))
        -> returns('334 ' . base64_encode('<foo@bar>') . "\r\n")
      // The use of any() is controversial, but here to avoid crazy test logic
      -> one($this->_buffer)->executeCommand(any(), array(235))
       -> throws(new Swift_Transport_TransportException(""))
      
      -> one($this->_buffer)->executeCommand("RSET\r\n", array(250))
      );
    
    $this->assertFalse($cram->authenticate($this->_buffer, 'jack', 'pass'),
      '%s: Authentication fails, so RSET should be sent'
      );
  }
  
  // -- Private helpers
  
  private function _getAuthenticator()
  {
    return new Swift_Transport_Esmtp_Auth_CramMd5Authenticator();
  }
  
}
