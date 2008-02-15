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
    $this->_finishBuffer();
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
    $this->_finishBuffer();
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
  
  public function testStartSendsEhloToInitiate()
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
         
       -- RFC 2281, 4.1.1.1.
       
       ehlo            = "EHLO" SP Domain CRLF
       helo            = "HELO" SP Domain CRLF
       
       -- RFC 2821, 4.3.2.
       
       EHLO or HELO
           S: 250
           E: 504, 550
       
     */
    
    $this->_buffer->setReturnValue(
      'readLine', '220 server.com foo' . "\r\n", array(0)
      );
    $this->_buffer->expectAt(
      0, 'write', array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValue(
      'write', 1, array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValue(
      'readLine', '250 STARTTLS' . "\r\n", array(1)
      );
    $this->_buffer->expectMinimumCallCount('write', 1);
    $this->_finishBuffer();
    
    try
    {
      $this->_smtp->start();
      $this->pass();
    }
    catch (Exception $e)
    {
      $this->fail('Starting Smtp should send EHLO and accept 250 response');
    }
  }
  
  public function testHeloIsUsedAsFallback()
  {
    /* -- RFC 2821, 4.1.4.
    
       If the EHLO command is not acceptable to the SMTP server, 501, 500,
       or 502 failure replies MUST be returned as appropriate.  The SMTP
       server MUST stay in the same state after transmitting these replies
       that it was in before the EHLO was received.
    */
    
    $this->_buffer->setReturnValue(
      'readLine', '220 server.com foo' . "\r\n", array(0)
      );
    $this->_buffer->expectAt(
      0, 'write', array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValue(
      'write', 1, array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValue(
      'readLine', '501 WTF' . "\r\n", array(1)
      );
    $this->_buffer->expectAt(
      1, 'write', array(new PatternExpectation('~^HELO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValue(
      'write', 2, array(new PatternExpectation('~^HELO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValue(
      'readLine', '250 HELO' . "\r\n", array(2)
      );
    $this->_buffer->expectMinimumCallCount('write', 2);
    $this->_finishBuffer();
    
    try
    {
      $this->_smtp->start();
      $this->pass();
    }
    catch (Exception $e)
    {
      $this->fail(
        'Starting Smtp should fallback to HELO if needed and accept 250 response'
        );
    }
  }
  
  public function testInvalidHeloResponseCausesException()
  {
    $this->_buffer->setReturnValue(
      'readLine', '220 server.com foo' . "\r\n", array(0)
      );
    $this->_buffer->expectAt(
      0, 'write', array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValue(
      'write', 1, array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValue(
      'readLine', '501 WTF' . "\r\n", array(1)
      );
    $this->_buffer->expectAt(
      1, 'write', array(new PatternExpectation('~^HELO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValue(
      'write', 2, array(new PatternExpectation('~^HELO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValue(
      'readLine', '504 WTF' . "\r\n", array(2)
      );
    $this->_buffer->expectMinimumCallCount('write', 2);
    $this->_finishBuffer();
    
    try
    {
      $this->_smtp->start();
      $this->fail('Non 250 HELO response should raise Exception');
    }
    catch (Exception $e)
    {
      $this->pass();
    }
  }
  
  public function testDomainNameIsPlaceInEhlo()
  {
    /* -- RFC 2821, 4.1.4.
    
       The SMTP client MUST, if possible, ensure that the domain parameter
       to the EHLO command is a valid principal host name (not a CNAME or MX
       name) for its host.  If this is not possible (e.g., when the client's
       address is dynamically assigned and the client does not have an
       obvious name), an address literal SHOULD be substituted for the
       domain name and supplemental information provided that will assist in
       identifying the client.
    */
    
  }
  
  // -- Private helpers
  
  private function _finishBuffer()
  {
    $this->_buffer->setReturnValue(
      'readLine', '220 server.com foo' . "\r\n", array(0)
      );
    $this->_buffer->setReturnValue(
      'write', 1, array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValue(
      'readLine', '250 STARTTLS' . "\r\n", array(1)
      );
    $this->_buffer->setReturnValue('readLine', false); //default return
  }
  
}
