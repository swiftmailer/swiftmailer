<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Mailer/Transport/SmtpTransport.php';
require_once 'Swift/Mailer/Transport/IoBuffer.php';

Mock::generate('Swift_Mailer_Transport_IoBuffer',
  'Swift_Mailer_Transport_MockIoBuffer'
  );

class Swift_Mailer_Transport_SmtpTransportTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_buffer;
  private $_smtp;
  
  public function setUp()
  {
    $this->_buffer = new Swift_Mailer_Transport_MockIoBuffer();
    $this->_smtp = new Swift_Mailer_Transport_SmtpTransport($this->_buffer);
  }
  
  public function testStartSendsEhlo()
  {
    /* -- RFC 2821, 3.
     */
    
    $this->assertFalse(true, 'Not implemented');
  }
  
}
