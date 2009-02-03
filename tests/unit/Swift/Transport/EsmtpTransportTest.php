<?php

require_once 'Swift/Transport/AbstractSmtpEventSupportTest.php';
require_once 'Swift/Transport/EsmtpTransport.php';
require_once 'Swift/Events/EventDispatcher.php';

class Swift_Transport_EsmtpTransportTest
  extends Swift_Transport_AbstractSmtpEventSupportTest
{
  
  protected function _getTransport($buf, $dispatcher = null)
  {
    if (!$dispatcher)
    {
      $dispatcher = $this->_createEventDispatcher();
    }
    return new Swift_Transport_EsmtpTransport($buf, array(), $dispatcher);
  }
  
  public function testHostCanBeSetAndFetched()
  {
    $buf = $this->_getBuffer();
    $smtp = $this->_getTransport($buf);
    $smtp->setHost('foo');
    $this->assertEqual('foo', $smtp->getHost(), '%s: Host should be returned');
  }
  
  public function testPortCanBeSetAndFetched()
  {
    $buf = $this->_getBuffer();
    $smtp = $this->_getTransport($buf);
    $smtp->setPort(25);
    $this->assertEqual(25, $smtp->getPort(), '%s: Port should be returned');
  }
  
  public function testTimeoutCanBeSetAndFetched()
  {
    $buf = $this->_getBuffer();
    $smtp = $this->_getTransport($buf);
    $smtp->setTimeout(10);
    $this->assertEqual(10, $smtp->getTimeout(), '%s: Timeout should be returned');
  }
  
  public function testEncryptionCanBeSetAndFetched()
  {
    $buf = $this->_getBuffer();
    $smtp = $this->_getTransport($buf);
    $smtp->setEncryption('tls');
    $this->assertEqual('tls', $smtp->getEncryption(), '%s: Crypto should be returned');
  }
  
  public function testStartSendsHeloToInitiate()
  {//Overridden for EHLO instead
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
    
    $buf = $this->_getBuffer();
    $smtp = $this->_getTransport($buf);
    $s = $this->_sequence('SMTP-convo');
    $this->_checking(Expectations::create()
      -> one($buf)->initialize() -> inSequence($s)
      -> one($buf)->readLine(0) -> inSequence($s) -> returns("220 some.server.tld bleh\r\n")
      -> one($buf)->write(pattern('~^EHLO .+?\r\n$~D')) -> inSequence($s) -> returns(1)
      -> one($buf)->readLine(1) -> inSequence($s) -> returns('250 ServerName' . "\r\n")
      );
    $this->_finishBuffer($buf);
    try
    {
      $smtp->start();
    }
    catch (Exception $e)
    {
      $this->fail('Starting Esmtp should send EHLO and accept 250 response');
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
    
    $buf = $this->_getBuffer();
    $smtp = $this->_getTransport($buf);
    $s = $this->_sequence('SMTP-convo');
    $this->_checking(Expectations::create()
      -> one($buf)->initialize() -> inSequence($s)
      -> one($buf)->readLine(0) -> inSequence($s) -> returns("220 some.server.tld bleh\r\n")
      -> one($buf)->write(pattern('~^EHLO .+?\r\n$~D')) -> inSequence($s) -> returns(1)
      -> one($buf)->readLine(1) -> inSequence($s) -> returns('501 WTF' . "\r\n")
      -> one($buf)->write(pattern('~^HELO .+?\r\n$~D')) -> inSequence($s) -> returns(2)
      -> one($buf)->readLine(2) -> inSequence($s) -> returns('250 HELO' . "\r\n")
      );
    $this->_finishBuffer($buf);
    try
    {
      $smtp->start();
    }
    catch (Exception $e)
    {
      $this->fail(
        'Starting Esmtp should fallback to HELO if needed and accept 250 response'
        );
    }
  }
  
  public function testInvalidHeloResponseCausesException()
  {//Overridden to first try EHLO
    $buf = $this->_getBuffer();
    $smtp = $this->_getTransport($buf);
    $s = $this->_sequence('SMTP-convo');
    $this->_checking(Expectations::create()
      -> one($buf)->initialize() -> inSequence($s)
      -> one($buf)->readLine(0) -> inSequence($s) -> returns("220 some.server.tld bleh\r\n")
      -> one($buf)->write(pattern('~^EHLO .*?\r\n$~D')) -> inSequence($s) -> returns(1)
      -> one($buf)->readLine(1) -> inSequence($s) -> returns('501 WTF' . "\r\n")
      -> one($buf)->write(pattern('~^HELO .*?\r\n$~D')) -> inSequence($s) -> returns(2)
      -> one($buf)->readLine(2) -> inSequence($s) -> returns('504 WTF' . "\r\n")
      );
    $this->_finishBuffer($buf);
    try
    {
      $this->assertFalse($smtp->isStarted(), '%s: SMTP should begin non-started');
      $smtp->start();
      $this->fail('Non 250 HELO response should raise Exception');
    }
    catch (Exception $e)
    {
      $this->assertFalse($smtp->isStarted(), '%s: SMTP start() should have failed');
    }
  }
  
  public function testDomainNameIsPlacedInEhlo()
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
    
    $buf = $this->_getBuffer();
    $smtp = $this->_getTransport($buf);
    $s = $this->_sequence('SMTP-convo');
    $this->_checking(Expectations::create()
      -> one($buf)->initialize() -> inSequence($s)
      -> one($buf)->readLine(0) -> inSequence($s) -> returns("220 some.server.tld bleh\r\n")
      -> one($buf)->write("EHLO mydomain.com\r\n") -> inSequence($s) -> returns(1)
      -> one($buf)->readLine(1) -> inSequence($s) -> returns('250 ServerName' . "\r\n")
      );
    $this->_finishBuffer($buf);
    $smtp->setLocalDomain('mydomain.com');
    $smtp->start();
  }
  
  public function testDomainNameIsPlacedInHelo()
  { //Overridden to include ESMTP
    /* -- RFC 2821, 4.1.4.
    
       The SMTP client MUST, if possible, ensure that the domain parameter
       to the EHLO command is a valid principal host name (not a CNAME or MX
       name) for its host.  If this is not possible (e.g., when the client's
       address is dynamically assigned and the client does not have an
       obvious name), an address literal SHOULD be substituted for the
       domain name and supplemental information provided that will assist in
       identifying the client.
    */
    
    $buf = $this->_getBuffer();
    $smtp = $this->_getTransport($buf);
    $s = $this->_sequence('SMTP-convo');
    $this->_checking(Expectations::create()
      -> one($buf)->initialize() -> inSequence($s)
      -> one($buf)->readLine(0) -> inSequence($s) -> returns("220 some.server.tld bleh\r\n")
      -> one($buf)->write(pattern('~^EHLO .+?\r\n$~D')) -> inSequence($s) -> returns(1)
      -> one($buf)->readLine(1) -> inSequence($s) -> returns('501 WTF' . "\r\n")
      -> one($buf)->write("HELO mydomain.com\r\n") -> inSequence($s) -> returns(2)
      -> one($buf)->readLine(2) -> inSequence($s) -> returns('250 ServerName' . "\r\n")
      );
    $this->_finishBuffer($buf);
    $smtp->setLocalDomain('mydomain.com');
    $smtp->start();
  }
  
  public function testFluidInterface()
  {
    $buf = $this->_getBuffer();
    $smtp = $this->_getTransport($buf);
    
    $ref = $smtp
      ->setHost('foo')
      ->setPort(25)
      ->setEncryption('tls')
      ->setTimeout(30)
      ;
    $this->assertReference($ref, $smtp);
  }
  
}
