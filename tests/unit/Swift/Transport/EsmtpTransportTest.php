<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/EsmtpTransport.php';
require_once 'Swift/Transport/EsmtpHandler.php';
require_once 'Swift/Transport/CommandSentException.php';
require_once 'Swift/Transport/IoBuffer.php';
require_once 'Swift/Mime/Message.php';

Mock::generate('Swift_Transport_IoBuffer',
  'Swift_Transport_MockIoBuffer'
  );
Mock::generate('Swift_Transport_EsmtpHandler',
  'Swift_Transport_MockEsmtpHandler'
  );
Mock::generate('Swift_Transport_EsmtpHandler',
  'Swift_Transport_MockEsmtpHandlerMixin',
  array('setUsername', 'setPassword')
  );
Mock::generate('Swift_Mime_Message', 'Swift_Mime_MockMessage');

class Swift_Transport_EsmtpTransportTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_buffer;
  private $_smtp;
  
  public function setUp()
  {
    $this->_buffer = new Swift_Transport_MockIoBuffer();
    $this->_smtp = new Swift_Transport_EsmtpTransport($this->_buffer, array());
  }
  
  public function tearDown()
  {
    $this->_smtp->stop();
  }
  
  public function testHostCanBeSetAndFetched()
  {
    $this->_smtp->setHost('foo');
    $this->assertEqual('foo', $this->_smtp->getHost());
  }
  
  public function testPortCanBeSetAndFetched()
  {
    $this->_smtp->setPort(25);
    $this->assertEqual(25, $this->_smtp->getPort());
  }
  
  public function testTimeoutCanBeSetAndFetched()
  {
    $this->_smtp->setTimeout(10);
    $this->assertEqual(10, $this->_smtp->getTimeout());
  }
  
  public function testEncryptionCanBeSetAndFetched()
  {
    $this->_smtp->setEncryption('tls');
    $this->assertEqual('tls', $this->_smtp->getEncryption());
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
    
    $this->_buffer->expectOnce('initialize');
    $this->_buffer->setReturnValue(
      'readLine', "220 some.server.tld bleh\r\n", array(0)
      );
    $this->_finishBuffer();
    try
    {
      $this->assertFalse($this->_smtp->isStarted());
      $this->_smtp->start();
      $this->assertTrue($this->_smtp->isStarted());
      $this->pass();
    }
    catch (Exception $e)
    {
      $this->fail('220 is a valid SMTP greeting and should be accepted');
    }
  }
  
  public function testBadGreetingCausesException()
  {
    $this->_buffer->expectOnce('initialize');
    $this->_buffer->setReturnValue(
      'readLine', "554 some.server.tld unknown error\r\n", array(0)
      );
    $this->_finishBuffer();
    try
    {
      $this->assertFalse($this->_smtp->isStarted());
      $this->_smtp->start();
      $this->fail('554 greeting indicates an error and should cause an exception');
    }
    catch (Exception $e)
    {
      $this->assertFalse($this->_smtp->isStarted());
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
      'readLine', '250 ServerName.tld' . "\r\n", array(1)
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
        'Starting Esmtp should fallback to HELO if needed and accept 250 response'
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
    
    $this->_buffer->setReturnValue(
      'readLine', '220 server.com foo' . "\r\n", array(0)
      );
    $this->_buffer->expectAt(
      0, 'write', array("EHLO mydomain.com\r\n")
      );
    $this->_buffer->setReturnValue(
      'write', 1, array("EHLO mydomain.com\r\n")
      );
    $this->_buffer->setReturnValue(
      'readLine', '250 ServerName.tld' . "\r\n", array(1)
      );
    $this->_buffer->expectMinimumCallCount('write', 1);
    $this->_finishBuffer();
    
    $this->_smtp->setLocalDomain('mydomain.com');
    
    $this->_smtp->start();
  }
  
  public function testSuccessfulMailCommand()
  {
    /* -- RFC 2821, 3.3.
    
    There are three steps to SMTP mail transactions.  The transaction
    starts with a MAIL command which gives the sender identification.
    
    .....
    
    The first step in the procedure is the MAIL command.

      MAIL FROM:<reverse-path> [SP <mail-parameters> ] <CRLF>
      
    -- RFC 2821, 4.1.1.2.
    
    Syntax:

      "MAIL FROM:" ("<>" / Reverse-Path)
                       [SP Mail-parameters] CRLF
    -- RFC 2821, 4.1.2.
    
    Reverse-path = Path
      Forward-path = Path
      Path = "<" [ A-d-l ":" ] Mailbox ">"
      A-d-l = At-domain *( "," A-d-l )
            ; Note that this form, the so-called "source route",
            ; MUST BE accepted, SHOULD NOT be generated, and SHOULD be
            ; ignored.
      At-domain = "@" domain
      
    -- RFC 2821, 4.3.2.
    
    MAIL
      S: 250
      E: 552, 451, 452, 550, 553, 503
    */
    
    $this->_buffer->expectAt(1, 'write', array("MAIL FROM: <me@domain.com>\r\n"));
    $this->_buffer->setReturnValue('write', 2, array("MAIL FROM: <me@domain.com>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(2));
    $this->_buffer->expectMinimumCallCount('write', 2);
    
    $this->_finishBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('me@domain.com'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar'=>null));
    
    try
    {
      $this->_smtp->start();
      $this->_smtp->send($message);
      $this->pass();
    }
    catch (Exception $e)
    {
      $this->fail('MAIL FROM should accept a 250 response');
    }
  }
  
  public function testInvalidResponseCodeFromMailCausesException()
  {
    $this->_buffer->expectAt(1, 'write', array("MAIL FROM: <me@domain.com>\r\n"));
    $this->_buffer->setReturnValue('write', 2, array("MAIL FROM: <me@domain.com>\r\n"));
    $this->_buffer->setReturnValue('readLine', "553 Go away\r\n", array(2));
    $this->_buffer->expectMinimumCallCount('write', 2);
    
    $this->_finishBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('me@domain.com'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar'=>null));
    
    try
    {
      $this->_smtp->start();
      $this->_smtp->send($message);
      $this->fail('MAIL FROM should accept a 250 response');
    }
    catch (Exception $e)
    {
      $this->pass();
    }
  }
  
  public function testSenderIsPreferredOverFrom()
  {
    $this->_buffer->expectAt(1, 'write', array("MAIL FROM: <another@domain.com>\r\n"));
    $this->_buffer->setReturnValue('write', 2, array("MAIL FROM: <another@domain.com>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(2));
    $this->_buffer->expectMinimumCallCount('write', 2);
    
    $this->_finishBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getSender', array('another@domain.com'=>'Someone'));
    $message->setReturnValue('getFrom', array('me@domain.com'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar'=>null));
    
    $this->_smtp->start();
    $this->_smtp->send($message);
  }
  
  public function testReturnPathIsPreferredOverSender()
  {
    $this->_buffer->expectAt(1, 'write', array("MAIL FROM: <more@domain.com>\r\n"));
    $this->_buffer->setReturnValue('write', 2, array("MAIL FROM: <more@domain.com>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(2));
    $this->_buffer->expectMinimumCallCount('write', 2);
    
    $this->_finishBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getReturnPath', 'more@domain.com');
    $message->setReturnValue('getSender', array('another@domain.com'=>'Someone'));
    $message->setReturnValue('getFrom', array('me@domain.com'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar'=>null));
    
    $this->_smtp->start();
    $this->_smtp->send($message);
  }
  
  public function testSuccessfulRcptCommandWith250Response()
  {
    /* -- RFC 2821, 3.3.
    
     The second step in the procedure is the RCPT command.

      RCPT TO:<forward-path> [ SP <rcpt-parameters> ] <CRLF>

     The first or only argument to this command includes a forward-path
     (normally a mailbox and domain, always surrounded by "<" and ">"
     brackets) identifying one recipient.  If accepted, the SMTP server
     returns a 250 OK reply and stores the forward-path.  If the recipient
     is known not to be a deliverable address, the SMTP server returns a
     550 reply, typically with a string such as "no such user - " and the
     mailbox name (other circumstances and reply codes are possible).
     This step of the procedure can be repeated any number of times.
    
    -- RFC 2821, 4.1.1.3.
    
    This command is used to identify an individual recipient of the mail
    data; multiple recipients are specified by multiple use of this
    command.  The argument field contains a forward-path and may contain
    optional parameters.

    The forward-path normally consists of the required destination
    mailbox.  Sending systems SHOULD not generate the optional list of
    hosts known as a source route.
    
    .......
    
    "RCPT TO:" ("<Postmaster@" domain ">" / "<Postmaster>" / Forward-Path)
                    [SP Rcpt-parameters] CRLF
                    
    -- RFC 2821, 4.2.2.
    
      250 Requested mail action okay, completed
      251 User not local; will forward to <forward-path>
         (See section 3.4)
      252 Cannot VRFY user, but will accept message and attempt
          delivery
    
    -- RFC 2821, 4.3.2.
    
    RCPT
      S: 250, 251 (but see section 3.4 for discussion of 251 and 551)
      E: 550, 551, 552, 553, 450, 451, 452, 503, 550
    */
    
    //We'll treat 252 as accepted since it isn't really a failure
    
    $this->_buffer->expectAt(2, 'write', array("RCPT TO: <foo@bar>\r\n"));
    $this->_buffer->setReturnValue('write', 3, array("RCPT TO: <foo@bar>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(3));
    $this->_buffer->expectMinimumCallCount('write', 3);
    
    $this->_finishBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('me@domain.com'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar'=>null));
    
    try
    {
      $this->_smtp->start();
      $this->_smtp->send($message);
      $this->pass();
    }
    catch (Exception $e)
    {
      $this->fail('RCPT TO should accept a 250 response');
    }
  }
  
  public function testMultipleRecipientsSendsMultipleRcpt()
  {
    $this->_buffer->expectAt(2, 'write', array("RCPT TO: <foo@bar>\r\n"));
    $this->_buffer->setReturnValue('write', 3, array("RCPT TO: <foo@bar>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(3));
    
    $this->_buffer->expectAt(3, 'write', array("RCPT TO: <zip@button>\r\n"));
    $this->_buffer->setReturnValue('write', 4, array("RCPT TO: <zip@button>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(4));
    
    $this->_buffer->expectAt(4, 'write', array("RCPT TO: <test@domain>\r\n"));
    $this->_buffer->setReturnValue('write', 5, array("RCPT TO: <test@domain>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(5));
    
    $this->_buffer->expectMinimumCallCount('write', 5);
    
    $this->_finishBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('me@domain.com'=>'Me'));
    $message->setReturnValue('getTo', array(
      'foo@bar' => null,
      'zip@button' => 'Zip Button',
      'test@domain' => 'Test user'
      ));
    
    $this->_smtp->start();
    $this->_smtp->send($message);
  }
  
  public function testCcRecipientsSendsMultipleRcpt()
  {
    $this->_buffer->expectAt(2, 'write', array("RCPT TO: <foo@bar>\r\n"));
    $this->_buffer->setReturnValue('write', 3, array("RCPT TO: <foo@bar>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(3));
    
    $this->_buffer->expectAt(3, 'write', array("RCPT TO: <zip@button>\r\n"));
    $this->_buffer->setReturnValue('write', 4, array("RCPT TO: <zip@button>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(4));
    
    $this->_buffer->expectAt(4, 'write', array("RCPT TO: <test@domain>\r\n"));
    $this->_buffer->setReturnValue('write', 5, array("RCPT TO: <test@domain>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(5));
    
    $this->_buffer->expectMinimumCallCount('write', 5);
    
    $this->_finishBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('me@domain.com'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar' => null));
    $message->setReturnValue('getCc', array(
      'zip@button' => 'Zip Button',
      'test@domain' => 'Test user'
      ));
    
    $this->_smtp->start();
    $this->_smtp->send($message);
  }
  
  public function testSendReturnsNumberOfSuccessfulRecipients()
  {
    $this->_buffer->expectAt(2, 'write', array("RCPT TO: <foo@bar>\r\n"));
    $this->_buffer->setReturnValue('write', 3, array("RCPT TO: <foo@bar>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(3));
    
    $this->_buffer->expectAt(3, 'write', array("RCPT TO: <zip@button>\r\n"));
    $this->_buffer->setReturnValue('write', 4, array("RCPT TO: <zip@button>\r\n"));
    $this->_buffer->setReturnValue('readLine', "501 no such user\r\n", array(4));
    
    $this->_buffer->expectAt(4, 'write', array("RCPT TO: <test@domain>\r\n"));
    $this->_buffer->setReturnValue('write', 5, array("RCPT TO: <test@domain>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(5));
    
    $this->_buffer->expectMinimumCallCount('write', 5);
    
    $this->_finishBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('me@domain.com'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar' => null));
    $message->setReturnValue('getCc', array(
      'zip@button' => 'Zip Button',
      'test@domain' => 'Test user'
      ));
    
    $this->_smtp->start();
    $this->assertEqual(2, $this->_smtp->send($message));
  }
  
  public function testRsetIsSentIfNoSuccessfulRecipients()
  {
    /* --RFC 2821, 4.1.1.5.
    
    This command specifies that the current mail transaction will be
    aborted.  Any stored sender, recipients, and mail data MUST be
    discarded, and all buffers and state tables cleared.  The receiver
    MUST send a "250 OK" reply to a RSET command with no arguments.  A
    reset command may be issued by the client at any time.
   
    -- RFC 2821, 4.3.2.
    
    RSET
      S: 250
    */
    
    $this->_buffer->expectAt(2, 'write', array("RCPT TO: <foo@bar>\r\n"));
    $this->_buffer->setReturnValue('write', 3, array("RCPT TO: <foo@bar>\r\n"));
    $this->_buffer->setReturnValue('readLine', "503 error\r\n", array(3));
    
    $this->_buffer->expectAt(3, 'write', array("RSET\r\n"));
    $this->_buffer->setReturnValue('write', 4, array("RSET\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(4));
    
    $this->_buffer->expectMinimumCallCount('write', 4);
    
    $this->_finishBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('me@domain.com'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar' => null));
    
    $this->_smtp->start();
    $this->assertEqual(0, $this->_smtp->send($message));
  }
  
  public function testSuccessfulDataCommand()
  {
    /* -- RFC 2821, 3.3.
    
    The third step in the procedure is the DATA command (or some
    alternative specified in a service extension).
    
          DATA <CRLF>

    If accepted, the SMTP server returns a 354 Intermediate reply and
    considers all succeeding lines up to but not including the end of
    mail data indicator to be the message text.
    
    -- RFC 2821, 4.1.1.4.
    
    The receiver normally sends a 354 response to DATA, and then treats
    the lines (strings ending in <CRLF> sequences, as described in
    section 2.3.7) following the command as mail data from the sender.
    This command causes the mail data to be appended to the mail data
    buffer.  The mail data may contain any of the 128 ASCII character
    codes, although experience has indicated that use of control
    characters other than SP, HT, CR, and LF may cause problems and
    SHOULD be avoided when possible.
   
    -- RFC 2821, 4.3.2.
    
    DATA
      I: 354 -> data -> S: 250
                        E: 552, 554, 451, 452
      E: 451, 554, 503
    */
    
    $this->_buffer->expectAt(3, 'write', array("DATA\r\n"));
    $this->_buffer->setReturnValue('write', 4, array("DATA\r\n"));
    $this->_buffer->setReturnValue('readLine', "354 Go ahead\r\n", array(4));
    
    $this->_buffer->expectMinimumCallCount('write', 4);
    
    $this->_finishBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('me@domain.com'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar' => null));
    
    try
    {
      $this->_smtp->start();
      $this->assertEqual(1, $this->_smtp->send($message));
      $this->pass();
    }
    catch (Exception $e)
    {
      $this->fail('354 is the expected response to DATA');
    }
  }
  
  public function testBadDataResponseCausesException()
  {
    $this->_buffer->expectAt(3, 'write', array("DATA\r\n"));
    $this->_buffer->setReturnValue('write', 4, array("DATA\r\n"));
    $this->_buffer->setReturnValue('readLine', "451 Not now\r\n", array(4));
    
    $this->_buffer->expectMinimumCallCount('write', 4);
    
    $this->_finishBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('me@domain.com'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar' => null));
    
    try
    {
      $this->_smtp->start();
      $this->assertEqual(0, $this->_smtp->send($message));
      $this->fail('354 is the expected response to DATA');
    }
    catch (Exception $e)
    {
      $this->pass();
    }
  }
  
  public function testMessageIsStreamedToBufferForData()
  {
    $this->_buffer->expectAt(3, 'write', array("DATA\r\n"));
    $this->_buffer->setReturnValue('write', 4, array("DATA\r\n"));
    $this->_buffer->setReturnValue('readLine', "354 Go ahead\r\n", array(4));
    
    $this->_buffer->expectMinimumCallCount('write', 4);
    
    $this->_finishBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('me@domain.com'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar' => null));
    $message->expectOnce('toByteStream', array($this->_buffer));
    
    $this->_smtp->start();
    $this->assertEqual(1, $this->_smtp->send($message));
  }
  
  public function testSuccessfulResponseAfterDataTransmission()
  {
    $this->_buffer->expectAt(3, 'write', array("DATA\r\n"));
    $this->_buffer->setReturnValue('write', 4, array("DATA\r\n"));
    $this->_buffer->setReturnValue('readLine', "354 Go ahead\r\n", array(4));
    
    $this->_buffer->expectAt(4, 'write', array("\r\n.\r\n"));
    $this->_buffer->setReturnValue('write', 5, array("\r\n.\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(5));
    
    $this->_buffer->expectMinimumCallCount('write', 5);
    
    $this->_finishBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('me@domain.com'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar' => null));
    $message->expectOnce('toByteStream', array($this->_buffer));
    
    try
    {
      $this->_smtp->start();
      $this->assertEqual(1, $this->_smtp->send($message));
      $this->pass();
    }
    catch (Exception $e)
    {
      $this->fail('250 is the expected response after a DATA transmission');
    }
  }
  
  public function testBadResponseAfterDataTransmissionCausesException()
  {
    $this->_buffer->expectAt(3, 'write', array("DATA\r\n"));
    $this->_buffer->setReturnValue('write', 4, array("DATA\r\n"));
    $this->_buffer->setReturnValue('readLine', "354 Go ahead\r\n", array(4));
    
    $this->_buffer->expectAt(4, 'write', array("\r\n.\r\n"));
    $this->_buffer->setReturnValue('write', 5, array("\r\n.\r\n"));
    $this->_buffer->setReturnValue('readLine', "554 error\r\n", array(5));
    
    $this->_buffer->expectMinimumCallCount('write', 5);
    
    $this->_finishBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('me@domain.com'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar' => null));
    $message->expectOnce('toByteStream', array($this->_buffer));
    
    try
    {
      $this->_smtp->start();
      $this->assertEqual(0, $this->_smtp->send($message));
      $this->fail('250 is the expected response after a DATA transmission');
    }
    catch (Exception $e)
    {
      $this->pass();
    }
  }
  
  public function testBccRecipientsAreRemovedFromHeaders()
  {
    /* -- RFC 2821, 7.2.
    
     Addresses that do not appear in the message headers may appear in the
     RCPT commands to an SMTP server for a number of reasons.  The two
     most common involve the use of a mailing address as a "list exploder"
     (a single address that resolves into multiple addresses) and the
     appearance of "blind copies".  Especially when more than one RCPT
     command is present, and in order to avoid defeating some of the
     purpose of these mechanisms, SMTP clients and servers SHOULD NOT copy
     the full set of RCPT command arguments into the headers, either as
     part of trace headers or as informational or private-extension
     headers.  Since this rule is often violated in practice, and cannot
     be enforced, sending SMTP systems that are aware of "bcc" use MAY
     find it helpful to send each blind copy as a separate message
     transaction containing only a single RCPT command.
     */
    
    $this->_buffer->expectAt(2, 'write', array("RCPT TO: <foo@bar>\r\n"));
    $this->_buffer->setReturnValue('write', 3, array("RCPT TO: <foo@bar>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(3));
    
    $this->_buffer->expectMinimumCallCount('write', 3);
    
    $this->_finishBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('me@domain.com'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar' => null));
    $message->setReturnValue('getBcc', array(
      'zip@button' => 'Zip Button',
      'test@domain' => 'Test user'
      ));
    $message->expectAt(0, 'setBcc', array(array()));
    $message->expectAtLeastOnce('setBcc');
    
    $this->_smtp->start();
    $this->assertEqual(3, $this->_smtp->send($message));
  }
  
  public function testEachBccRecipientIsSentASeparateMessage()
  {
    //To: recipient
    $this->_buffer->expectAt(2, 'write', array("RCPT TO: <foo@bar>\r\n"));
    $this->_buffer->setReturnValue('write', 3, array("RCPT TO: <foo@bar>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(3));
    
    $this->_buffer->expectAt(3, 'write', array("DATA\r\n"));
    $this->_buffer->setReturnValue('write', 4, array("DATA\r\n"));
    $this->_buffer->setReturnValue('readLine', "354 Go ahead\r\n", array(4));
    
    $this->_buffer->expectAt(4, 'write', array("\r\n.\r\n"));
    $this->_buffer->setReturnValue('write', 5, array("\r\n.\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(5));
    
    //Bcc's
    $this->_buffer->expectAt(5, 'write', array("MAIL FROM: <me@domain.com>\r\n"));
    $this->_buffer->setReturnValue('write', 6, array("MAIL FROM: <me@domain.com>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(6));
    
    $this->_buffer->expectAt(6, 'write', array("RCPT TO: <zip@button>\r\n"));
    $this->_buffer->setReturnValue('write', 7, array("RCPT TO: <zip@button>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(7));
    
    $this->_buffer->expectAt(7, 'write', array("DATA\r\n"));
    $this->_buffer->setReturnValue('write', 8, array("DATA\r\n"));
    $this->_buffer->setReturnValue('readLine', "354 Go ahead\r\n", array(8));
    
    $this->_buffer->expectAt(8, 'write', array("\r\n.\r\n"));
    $this->_buffer->setReturnValue('write', 9, array("\r\n.\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(9));
    
    $this->_buffer->expectAt(9, 'write', array("MAIL FROM: <me@domain.com>\r\n"));
    $this->_buffer->setReturnValue('write', 10, array("MAIL FROM: <me@domain.com>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(10));
    
    $this->_buffer->expectAt(10, 'write', array("RCPT TO: <test@domain>\r\n"));
    $this->_buffer->setReturnValue('write', 11, array("RCPT TO: <test@domain>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(11));
    
    $this->_buffer->expectAt(11, 'write', array("DATA\r\n"));
    $this->_buffer->setReturnValue('write', 12, array("DATA\r\n"));
    $this->_buffer->setReturnValue('readLine', "354 Go ahead\r\n", array(12));
    
    $this->_buffer->expectAt(12, 'write', array("\r\n.\r\n"));
    $this->_buffer->setReturnValue('write', 13, array("\r\n.\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(13));
    
    $this->_buffer->expectMinimumCallCount('write', 13);
    
    $this->_finishBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('me@domain.com'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar' => null));
    $message->setReturnValue('getBcc', array(
      'zip@button' => 'Zip Button',
      'test@domain' => 'Test user'
      ));
    $message->expectAt(0, 'setBcc', array(array()));
    $message->expectAt(1, 'setBcc', array(array('zip@button' => 'Zip Button')));
    $message->expectAt(2, 'setBcc', array(array('test@domain' => 'Test user')));
    $message->expectMinimumCallCount('setBcc', 3);
    
    $this->_smtp->start();
    $this->assertEqual(3, $this->_smtp->send($message));
  }
  
  public function testBccRecipientsAreRestoredAfterSending()
  {
    //To: recipient
    $this->_buffer->expectAt(2, 'write', array("RCPT TO: <foo@bar>\r\n"));
    $this->_buffer->setReturnValue('write', 3, array("RCPT TO: <foo@bar>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(3));
    
    $this->_buffer->expectAt(3, 'write', array("DATA\r\n"));
    $this->_buffer->setReturnValue('write', 4, array("DATA\r\n"));
    $this->_buffer->setReturnValue('readLine', "354 Go ahead\r\n", array(4));
    
    $this->_buffer->expectAt(4, 'write', array("\r\n.\r\n"));
    $this->_buffer->setReturnValue('write', 5, array("\r\n.\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(5));
    
    //Bcc's
    $this->_buffer->expectAt(5, 'write', array("MAIL FROM: <me@domain.com>\r\n"));
    $this->_buffer->setReturnValue('write', 6, array("MAIL FROM: <me@domain.com>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(6));
    
    $this->_buffer->expectAt(6, 'write', array("RCPT TO: <zip@button>\r\n"));
    $this->_buffer->setReturnValue('write', 7, array("RCPT TO: <zip@button>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(7));
    
    $this->_buffer->expectAt(7, 'write', array("DATA\r\n"));
    $this->_buffer->setReturnValue('write', 8, array("DATA\r\n"));
    $this->_buffer->setReturnValue('readLine', "354 Go ahead\r\n", array(8));
    
    $this->_buffer->expectAt(8, 'write', array("\r\n.\r\n"));
    $this->_buffer->setReturnValue('write', 9, array("\r\n.\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(9));
    
    $this->_buffer->expectAt(9, 'write', array("MAIL FROM: <me@domain.com>\r\n"));
    $this->_buffer->setReturnValue('write', 10, array("MAIL FROM: <me@domain.com>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(10));
    
    $this->_buffer->expectAt(10, 'write', array("RCPT TO: <test@domain>\r\n"));
    $this->_buffer->setReturnValue('write', 11, array("RCPT TO: <test@domain>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(11));
    
    $this->_buffer->expectAt(11, 'write', array("DATA\r\n"));
    $this->_buffer->setReturnValue('write', 12, array("DATA\r\n"));
    $this->_buffer->setReturnValue('readLine', "354 Go ahead\r\n", array(12));
    
    $this->_buffer->expectAt(12, 'write', array("\r\n.\r\n"));
    $this->_buffer->setReturnValue('write', 13, array("\r\n.\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(13));
    
    $this->_buffer->expectMinimumCallCount('write', 13);
    
    $this->_finishBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('me@domain.com'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar' => null));
    $message->setReturnValue('getBcc', array(
      'zip@button' => 'Zip Button',
      'test@domain' => 'Test user'
      ));
    $message->expectAt(0, 'setBcc', array(array()));
    $message->expectAt(1, 'setBcc', array(array('zip@button' => 'Zip Button')));
    $message->expectAt(2, 'setBcc', array(array('test@domain' => 'Test user')));
    $message->expectAt(3, 'setBcc', array(array(
      'zip@button' => 'Zip Button',
      'test@domain' => 'Test user'
      )));
    $message->expectCallCount('setBcc', 4);
    
    $this->_smtp->start();
    $this->assertEqual(3, $this->_smtp->send($message));
  }
  
  public function testMessageStateIsRestoredOnFailure()
  {
    $this->_buffer->expectAt(2, 'write', array("RCPT TO: <foo@bar>\r\n"));
    $this->_buffer->setReturnValue('write', 3, array("RCPT TO: <foo@bar>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(3));
    
    $this->_buffer->expectAt(3, 'write', array("DATA\r\n"));
    $this->_buffer->setReturnValue('write', 4, array("DATA\r\n"));
    $this->_buffer->setReturnValue('readLine', "451 No\r\n", array(4));
    
    $this->_buffer->expectMinimumCallCount('write', 4);
    
    $this->_finishBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('me@domain.com'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar' => null));
    $message->setReturnValue('getBcc', array(
      'zip@button' => 'Zip Button',
      'test@domain' => 'Test user'
      ));
    $message->expectAt(0, 'setBcc', array(array()));
    $message->expectAt(1, 'setBcc', array(array(
      'zip@button' => 'Zip Button',
      'test@domain' => 'Test user'
      ))); //restoration
    $message->expectCallCount('setBcc', 2);
    
    $this->_smtp->start();
    try
    {
      $this->_smtp->send($message);
      $this->fail('A bad response was given so exception is expected');
    }
    catch (Exception $e)
    {
      $this->pass();
    }
  }
  
  public function testStopSendsQuitCommand()
  {
    /* -- RFC 2821, 4.1.1.10.
    
    This command specifies that the receiver MUST send an OK reply, and
    then close the transmission channel.

    The receiver MUST NOT intentionally close the transmission channel
    until it receives and replies to a QUIT command (even if there was an
    error).  The sender MUST NOT intentionally close the transmission
    channel until it sends a QUIT command and SHOULD wait until it
    receives the reply (even if there was an error response to a previous
    command).  If the connection is closed prematurely due to violations
    of the above or system or network failure, the server MUST cancel any
    pending transaction, but not undo any previously completed
    transaction, and generally MUST act as if the command or transaction
    in progress had received a temporary error (i.e., a 4yz response).

    The QUIT command may be issued at any time.

    Syntax:
      "QUIT" CRLF
    */
    
    $this->_buffer->expectOnce('initialize');
    $this->_buffer->expectOnce('terminate');
    $this->_buffer->expectAt(1, 'write', array("QUIT\r\n"));
    $this->_buffer->setReturnValue('write', 2, array("QUIT\r\n"));
    $this->_buffer->setReturnValue('readLine', "221 Bye\r\n", array(2));
    $this->_buffer->expectMinimumCallCount('write', 2);
    
    $this->_finishBuffer();
    
    $this->assertFalse($this->_smtp->isStarted());
    $this->_smtp->start();
    $this->assertTrue($this->_smtp->isStarted());
    $this->_smtp->stop();
    $this->assertFalse($this->_smtp->isStarted());
  }
  
  public function testBufferCanBeFetched()
  {
    $this->assertReference($this->_buffer, $this->_smtp->getBuffer());
  }
  
  public function testBufferCanBeWrittenToUsingExecuteCommand()
  {
    $this->_buffer->expectOnce('write', array("FOO\r\n"));
    $this->_buffer->setReturnValue('write', 1, array("FOO\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(1));
    
    $res = $this->_smtp->executeCommand("FOO\r\n");
    $this->assertEqual("250 OK\r\n", $res);
  }
  
  public function testResponseCodesAreValidated()
  {
    $this->_buffer->expectOnce('write', array("FOO\r\n"));
    $this->_buffer->setReturnValue('write', 1, array("FOO\r\n"));
    $this->_buffer->setReturnValue('readLine', "551 No\r\n", array(1));
    
    try
    {
      $this->_smtp->executeCommand("FOO\r\n", array(250, 251));
      $this->fail('A 250 or 251 response was needed but 551 was returned.');
    }
    catch (Exception $e)
    {
      $this->pass();
    }
  }
  
  ///////////////////////////////////////////////////
  // THE FOLLOWING ADDS ESMTP SUPPORT FOR AUTH ETC //
  ///////////////////////////////////////////////////
  
  public function testExtensionHandlersAreSortedAsNeeded()
  {
    $ext1 = new Swift_Transport_MockEsmtpHandler();
    $ext1->setReturnValue('getHandledKeyword', 'AUTH');
    $ext1->setReturnValue('getPriorityOver', 0, array('STARTTLS'));
    
    $ext2 = new Swift_Transport_MockEsmtpHandler();
    $ext2->setReturnValue('getHandledKeyword', 'STARTTLS');
    $ext2->setReturnValue('getPriorityOver', -1, array('AUTH'));
    
    $this->_smtp->setExtensionHandlers(array($ext1, $ext2));
    $this->assertEqual(array($ext2, $ext1), $this->_smtp->getExtensionHandlers());
  }
  
  public function testHandlersAreNotifiedOfParams()
  {
    $ext1 = new Swift_Transport_MockEsmtpHandler();
    $ext1->setReturnValue('getHandledKeyword', 'AUTH');
    $ext1->expectOnce('setKeywordParams', array(array('PLAIN', 'LOGIN')));
    
    $ext2 = new Swift_Transport_MockEsmtpHandler();
    $ext2->setReturnValue('getHandledKeyword', 'SIZE');
    $ext2->expectOnce('setKeywordParams', array(array('123456')));
    
    $this->_smtp->setExtensionHandlers(array($ext1, $ext2));
    
    $this->_buffer->setReturnValue(
      'readLine', '220 server.com foo' . "\r\n", array(0)
      );
    $this->_buffer->expectAt(
      0, 'write', array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValue(
      'write', 1, array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValueAt(1,
      'readLine', '250-ServerName.tld' . "\r\n", array(1)
      );
    $this->_buffer->setReturnValueAt(2,
      'readLine', '250-AUTH PLAIN LOGIN' . "\r\n", array(1)
      );
    $this->_buffer->setReturnValueAt(3,
      'readLine', '250 SIZE=123456' . "\r\n", array(1)
      );
    
    $this->_finishBuffer();
    
    $this->_smtp->start();
  }
  
  public function testSupportedExtensionHandlersAreRunAfterEhlo()
  {
    $ext1 = new Swift_Transport_MockEsmtpHandler();
    $ext1->setReturnValue('getHandledKeyword', 'AUTH');
    $ext1->expectOnce('setKeywordParams', array(array('PLAIN', 'LOGIN')));
    $ext1->expectOnce('afterEhlo', array($this->_smtp));
    
    $ext2 = new Swift_Transport_MockEsmtpHandler();
    $ext2->setReturnValue('getHandledKeyword', 'SIZE');
    $ext2->expectOnce('setKeywordParams', array(array('123456')));
    $ext2->expectOnce('afterEhlo', array($this->_smtp));
    
    $ext3 = new Swift_Transport_MockEsmtpHandler();
    $ext3->setReturnValue('getHandledKeyword', 'STARTTLS');
    $ext3->expectNever('setKeywordParams');
    $ext3->expectNever('afterEhlo');
    
    $this->_smtp->setExtensionHandlers(array($ext1, $ext2, $ext3));
    
    $this->_buffer->setReturnValue(
      'readLine', '220 server.com foo' . "\r\n", array(0)
      );
    $this->_buffer->expectAt(
      0, 'write', array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValue(
      'write', 1, array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValueAt(1,
      'readLine', '250-ServerName.tld' . "\r\n", array(1)
      );
    $this->_buffer->setReturnValueAt(2,
      'readLine', '250-AUTH PLAIN LOGIN' . "\r\n", array(1)
      );
    $this->_buffer->setReturnValueAt(3,
      'readLine', '250 SIZE=123456' . "\r\n", array(1)
      );
    
    $this->_finishBuffer();
    
    $this->_smtp->start();
  }
  
  public function testExtensionsCanModifyMailFromParams()
  {
    $ext1 = new Swift_Transport_MockEsmtpHandler();
    $ext1->setReturnValue('getHandledKeyword', 'AUTH');
    $ext1->setReturnValue('getMailParams', array('FOO'));
    $ext1->setReturnValue('getPriorityOver', -1);
    
    $ext2 = new Swift_Transport_MockEsmtpHandler();
    $ext2->setReturnValue('getHandledKeyword', 'SIZE');
    $ext2->setReturnValue('getMailParams', array('ZIP'));
    $ext2->setReturnValue('getPriorityOver', 1);
    
    $ext3 = new Swift_Transport_MockEsmtpHandler();
    $ext3->setReturnValue('getHandledKeyword', 'STARTTLS');
    $ext3->expectNever('getMailParams');
    
    $this->_smtp->setExtensionHandlers(array($ext1, $ext2, $ext3));
    
    $this->_buffer->setReturnValue(
      'readLine', '220 server.com foo' . "\r\n", array(0)
      );
    $this->_buffer->expectAt(
      0, 'write', array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValue(
      'write', 1, array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValueAt(1,
      'readLine', '250-ServerName.tld' . "\r\n", array(1)
      );
    $this->_buffer->setReturnValueAt(2,
      'readLine', '250-AUTH PLAIN LOGIN' . "\r\n", array(1)
      );
    $this->_buffer->setReturnValueAt(3,
      'readLine', '250 SIZE=123456' . "\r\n", array(1)
      );
    $this->_buffer->expectAt(1, 'write', array("MAIL FROM: <me@domain> FOO ZIP\r\n"));
    $this->_buffer->setReturnValue('write', 2, array("MAIL FROM: <me@domain> FOO ZIP\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(2));
    
    $this->_buffer->expectAt(2, 'write', array("RCPT TO: <foo@bar>\r\n"));
    $this->_buffer->setReturnValue('write', 3, array("RCPT TO: <foo@bar>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(3));
    
    $this->_buffer->expectMinimumCallCount('write', 3);
    
    $this->_finishBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('me@domain'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar'=>null));
    
    $this->_smtp->start();
    $this->_smtp->send($message);
  }
  
  public function testExtensionsCanModifyRcptParams()
  {
    $ext1 = new Swift_Transport_MockEsmtpHandler();
    $ext1->setReturnValue('getHandledKeyword', 'AUTH');
    $ext1->setReturnValue('getRcptParams', array('FOO'));
    $ext1->setReturnValue('getPriorityOver', -1);
    
    $ext2 = new Swift_Transport_MockEsmtpHandler();
    $ext2->setReturnValue('getHandledKeyword', 'SIZE');
    $ext2->setReturnValue('getRcptParams', array('ZIP'));
    $ext2->setReturnValue('getPriorityOver', 1);
    
    $ext3 = new Swift_Transport_MockEsmtpHandler();
    $ext3->setReturnValue('getHandledKeyword', 'STARTTLS');
    $ext3->expectNever('getRcptParams');
    
    $this->_smtp->setExtensionHandlers(array($ext1, $ext2, $ext3));
    
    $this->_buffer->setReturnValue(
      'readLine', '220 server.com foo' . "\r\n", array(0)
      );
    $this->_buffer->expectAt(
      0, 'write', array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValue(
      'write', 1, array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValueAt(1,
      'readLine', '250-ServerName.tld' . "\r\n", array(1)
      );
    $this->_buffer->setReturnValueAt(2,
      'readLine', '250-AUTH PLAIN LOGIN' . "\r\n", array(1)
      );
    $this->_buffer->setReturnValueAt(3,
      'readLine', '250 SIZE=123456' . "\r\n", array(1)
      );
    $this->_buffer->expectAt(1, 'write', array("MAIL FROM: <me@domain>\r\n"));
    $this->_buffer->setReturnValue('write', 2, array("MAIL FROM: <me@domain>\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(2));
    
    $this->_buffer->expectAt(2, 'write', array("RCPT TO: <foo@bar> FOO ZIP\r\n"));
    $this->_buffer->setReturnValue('write', 3, array("RCPT TO: <foo@bar> FOO ZIP\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array(3));
    
    $this->_buffer->expectMinimumCallCount('write', 3);
    
    $this->_finishBuffer();
    
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getFrom', array('me@domain'=>'Me'));
    $message->setReturnValue('getTo', array('foo@bar'=>null));
    
    $this->_smtp->start();
    $this->_smtp->send($message);
  }
  
  public function testExtensionsAreNotifiedOnCommand()
  {
    $ext1 = new Swift_Transport_MockEsmtpHandler();
    $ext1->setReturnValue('getHandledKeyword', 'AUTH');
    $ext1->setReturnValue('getPriorityOver', -1);
    $ext1->expectAt(0, 'onCommand', array($this->_smtp, "FOO\r\n", array(250, 251)));
    $ext1->expectAtLeastOnce('onCommand');
    
    $ext2 = new Swift_Transport_MockEsmtpHandler();
    $ext2->setReturnValue('getHandledKeyword', 'SIZE');
    $ext2->setReturnValue('getPriorityOver', 1);
    $ext2->expectAt(0, 'onCommand', array($this->_smtp, "FOO\r\n", array(250, 251)));
    $ext2->expectAtLeastOnce('onCommand');
    
    $ext3 = new Swift_Transport_MockEsmtpHandler();
    $ext3->setReturnValue('getHandledKeyword', 'STARTTLS');
    $ext3->expectNever('onCommand');
    
    $this->_smtp->setExtensionHandlers(array($ext1, $ext2, $ext3));
    
    $this->_buffer->setReturnValue(
      'readLine', '220 server.com foo' . "\r\n", array(0)
      );
    $this->_buffer->expectAt(
      0, 'write', array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValue(
      'write', 1, array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValueAt(1,
      'readLine', '250-ServerName.tld' . "\r\n", array(1)
      );
    $this->_buffer->setReturnValueAt(2,
      'readLine', '250-AUTH PLAIN LOGIN' . "\r\n", array(1)
      );
    $this->_buffer->setReturnValueAt(3,
      'readLine', '250 SIZE=123456' . "\r\n", array(1)
      );
    $this->_buffer->setReturnValue('write', 2, array("FOO\r\n"));
    $this->_buffer->setReturnValue('readLine', "251 Cool\r\n", array(2));
    
    $this->_finishBuffer();
    
    $this->_smtp->start();
    
    $this->_smtp->executeCommand("FOO\r\n", array(250, 251));
  }
  
  public function testChainOfCommandAlgorithmWhenNotifyingExtensions()
  {
    $e = new Swift_Transport_CommandSentException("250 OK\r\n");
    $ext1 = new Swift_Transport_MockEsmtpHandler();
    $ext1->setReturnValue('getHandledKeyword', 'AUTH');
    $ext1->setReturnValue('getPriorityOver', -1);
    $ext1->expectAt(0, 'onCommand', array($this->_smtp, "FOO\r\n", array(250, 251)));
    $ext1->throwOn('onCommand', $e);
    $ext1->expectAtLeastOnce('onCommand');
    
    $ext2 = new Swift_Transport_MockEsmtpHandler();
    $ext2->setReturnValue('getHandledKeyword', 'SIZE');
    $ext2->setReturnValue('getPriorityOver', 1);
    $ext2->expectNever('onCommand');
    
    $ext3 = new Swift_Transport_MockEsmtpHandler();
    $ext3->setReturnValue('getHandledKeyword', 'STARTTLS');
    $ext3->expectNever('onCommand');
    
    $this->_smtp->setExtensionHandlers(array($ext1, $ext2, $ext3));
    
    $this->_buffer->setReturnValue(
      'readLine', '220 server.com foo' . "\r\n", array(0)
      );
    $this->_buffer->expectAt(
      0, 'write', array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValue(
      'write', 1, array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValueAt(1,
      'readLine', '250-ServerName.tld' . "\r\n", array(1)
      );
    $this->_buffer->setReturnValueAt(2,
      'readLine', '250-AUTH PLAIN LOGIN' . "\r\n", array(1)
      );
    $this->_buffer->setReturnValueAt(3,
      'readLine', '250 SIZE=123456' . "\r\n", array(1)
      );
    
    $this->_finishBuffer();
    
    $this->_smtp->start();
    
    $this->_smtp->executeCommand("FOO\r\n", array(250, 251));
  }
  
  public function testExtensionsCanExposeMixinMethods()
  {
    $ext1 = new Swift_Transport_MockEsmtpHandlerMixin();
    $ext1->setReturnValue('getHandledKeyword', 'AUTH');
    $ext1->setReturnValue('getPriorityOver', 0, array('STARTTLS'));
    $ext1->setReturnValue('exposeMixinMethods', array('setUsername', 'setPassword'));
    $ext1->expectOnce('setUsername', array('mick'));
    $ext1->expectOnce('setPassword', array('pass'));
    
    $ext2 = new Swift_Transport_MockEsmtpHandler();
    $ext2->setReturnValue('getHandledKeyword', 'STARTTLS');
    $ext2->setReturnValue('getPriorityOver', -1, array('AUTH'));
    
    $this->_smtp->setExtensionHandlers(array($ext1, $ext2));
    
    $this->_smtp->setUsername('mick');
    $this->_smtp->setPassword('pass');
  }
  
  public function testMixinMethodsBeginningWithSetAndNullReturnAreFluid()
  {
    $ext1 = new Swift_Transport_MockEsmtpHandlerMixin();
    $ext1->setReturnValue('getHandledKeyword', 'AUTH');
    $ext1->setReturnValue('getPriorityOver', 0, array('STARTTLS'));
    $ext1->setReturnValue('exposeMixinMethods', array('setUsername', 'setPassword'));
    $ext1->expectOnce('setUsername', array('mick'));
    $ext1->setReturnValue('setUsername', null);
    $ext1->expectOnce('setPassword', array('pass'));
    $ext1->setReturnValue('setPassword', null);
    
    $ext2 = new Swift_Transport_MockEsmtpHandler();
    $ext2->setReturnValue('getHandledKeyword', 'STARTTLS');
    $ext2->setReturnValue('getPriorityOver', -1, array('AUTH'));
    
    $this->_smtp->setExtensionHandlers(array($ext1, $ext2));
    
    $this->assertReference($this->_smtp, $this->_smtp->setUsername('mick'));
    $this->assertReference($this->_smtp, $this->_smtp->setPassword('pass'));
  }
  
  public function testMixinSetterWhichReturnValuesAreNotFluid()
  {
    $ext1 = new Swift_Transport_MockEsmtpHandlerMixin();
    $ext1->setReturnValue('getHandledKeyword', 'AUTH');
    $ext1->setReturnValue('getPriorityOver', 0, array('STARTTLS'));
    $ext1->setReturnValue('exposeMixinMethods', array('setUsername', 'setPassword'));
    $ext1->expectOnce('setUsername', array('mick'));
    $ext1->setReturnValue('setUsername', 'x');
    $ext1->expectOnce('setPassword', array('pass'));
    $ext1->setReturnValue('setPassword', 'y');
    
    $ext2 = new Swift_Transport_MockEsmtpHandler();
    $ext2->setReturnValue('getHandledKeyword', 'STARTTLS');
    $ext2->setReturnValue('getPriorityOver', -1, array('AUTH'));
    
    $this->_smtp->setExtensionHandlers(array($ext1, $ext2));
    
    $this->assertEqual('x', $this->_smtp->setUsername('mick'));
    $this->assertEqual('y', $this->_smtp->setPassword('pass'));
  }
  
  public function testFluidInterface()
  {
    $smtp = new Swift_Transport_EsmtpTransport($this->_buffer, array());
    $ref = $smtp
      ->setHost('foo')
      ->setPort(25)
      ->setEncryption('tls')
      ->setTimeout(30)
      ;
    $this->assertReference($ref, $smtp);
  }
  
  // -- Private helpers
  
  /**
   * Fill in any gaps ;)
   */
  private function _finishBuffer()
  {
    $this->_buffer->setReturnValue(
      'readLine', '220 server.com foo' . "\r\n", array(0)
      );
    $this->_buffer->setReturnValue(
      'write', $x = uniqid(), array(new PatternExpectation('~^EHLO .*?\r\n$~D'))
      );
    $this->_buffer->setReturnValue(
      'readLine', '250 ServerName' . "\r\n", array($x)
      );
    $this->_buffer->setReturnValue(
      'write', $x = uniqid(), array(new PatternExpectation('~^MAIL FROM: <.*?>\r\n$~D'))
      );
    $this->_buffer->setReturnValue(
      'readLine', '250 OK' . "\r\n", array($x)
      );
    $this->_buffer->setReturnValue(
      'write', $x = uniqid(), array(new PatternExpectation('~^RCPT TO: <.*?>\r\n$~D'))
      );
    $this->_buffer->setReturnValue(
      'readLine', "250 OK\r\n", array($x)
      );
    $this->_buffer->setReturnValue('write', $x = uniqid(), array("DATA\r\n"));
    $this->_buffer->setReturnValue('readLine', "354 Go ahead\r\n", array($x));
    $this->_buffer->setReturnValue('write', $x = uniqid(), array("\r\n.\r\n"));
    $this->_buffer->setReturnValue('readLine', "250 OK\r\n", array($x));
    $this->_buffer->setReturnValue('readLine', false); //default return
  }
  
}
