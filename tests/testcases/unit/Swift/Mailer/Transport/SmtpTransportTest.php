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
  
  public function testStartAccepts220ServiceGreeting()
  {
    /* -- RFC 2821, 4.2.
     
     Greeting = "220 " Domain [ SP text ] CRLF
     
     -- RFC 2822, 4.3.2.
     
     CONNECTION ESTABLISHMENT
         S: 220
         E: 554
    */
    
    $this->_buffer->setReturnValue(
      'readLine', "220 some.server.tld bleh\r\n", array(0)
      );
    try
    {
      $this->_smtp->start();
      $this->pass();
    }
    catch (Exception $e)
    {
      $this->fail('220 is a valid SMTP greeting and should be accepted');
    }
  }
  
  public function testBadGreetingCausesException()
  {
    $this->_buffer->setReturnValue(
      'readLine', "554 some.server.tld unknown error\r\n", array(0)
      );
    try
    {
      $this->_smtp->start();
      $this->fail('554 greeting indicates an error and should cause an exception');
    }
    catch (Exception $e)
    {
      $this->pass();
    }
  }
  
  public function testStartSendsEhlo()
  {
    /* -- RFC 2821, 3.2.
    
      3.2 Client Initiation

         Once the server has sent the welcoming message and the client has
         received it, the client normally sends the EHLO command to the
         server, indicating the client's identity.  In addition to opening the
         session, use of EHLO indicates that the client is able to process
         service extensions and requests that the server provide a list of the
         extensions it supports.  Older SMTP systems which are unable to
         support service extensions and contemporary clients which do not
         require service extensions in the mail session being initiated, MAY
         use HELO instead of EHLO.  Servers MUST NOT return the extended
         EHLO-style response to a HELO command.  For a particular connection
         attempt, if the server returns a "command not recognized" response to
         EHLO, the client SHOULD be able to fall back and send HELO.

         In the EHLO command the host sending the command identifies itself;
         the command may be interpreted as saying "Hello, I am <domain>" (and,
         in the case of EHLO, "and I support service extension requests").
     */
    
    //$this->assertFalse(true, 'Not implemented');
  }
  
}
