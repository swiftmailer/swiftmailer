<?php

require_once 'Swift/Mime/Header.php';
require_once 'Swift/CharacterStream/ArrayCharacterStream.php';
require_once 'Swift/CharacterReaderFactory.php';
require_once 'Swift/CharacterReader/Utf8Reader.php';
require_once 'Swift/Mime/HeaderEncoder/QpHeaderEncoder.php';
require_once 'Swift/Mime/HeaderEncoder/Base64HeaderEncoder.php';
require_once 'Swift/Encoder/Rfc2231Encoder.php';
require_once 'Swift/Mime/HeaderFactory/SimpleHeaderFactory.php';

Mock::Generate('Swift_CharacterReaderFactory', 'Swift_MockCharacterReaderFactory');

class Swift_Mime_HeaderFactory_SimpleHeaderFactoryAcceptanceTest
  extends UnitTestCase
{
  
  private $_factory;
  private $_timestamp = 1200310208;
  private $_rfc2822Date = 'Mon, 14 Jan 2008 22:30:08 +1100';
  
  public function setUp()
  {
    $mockReaderFactory = new Swift_MockCharacterReaderFactory();
    $mockReaderFactory->setReturnValue(
      'getReaderFor', new Swift_CharacterReader_Utf8Reader()
      );
    $charStream = new Swift_CharacterStream_ArrayCharacterStream(
      null, 'utf-8', $mockReaderFactory
      );
    $this->_factory = new Swift_Mime_HeaderFactory_SimpleHeaderFactory();
    $this->_factory->setQEncoder(
      new Swift_Mime_HeaderEncoder_QpHeaderEncoder($charStream)
      );
    $this->_factory->setBEncoder(
      new Swift_Mime_HeaderEncoder_Base64HeaderEncoder()
      );
    $this->_factory->setAttributeEncoder(
      new Swift_Encoder_Rfc2231Encoder($charStream)
      );
    $this->_factory->setDefaultEncodingMethod('Q');
    $this->_factory->setDefaultCharacterSet('utf-8');
    $this->_factory->setDefaultLanguage('en');
    $this->_factory->setMaxLineLength(78);
  }
  
  public function testAllHeadersAreInstancesOfHeader()
  {
    foreach (array(
      'Return-Path', 'Received',
      'Resent-Date', 'Resent-From', 'Resent-Sender',
      'Resent-To', 'Resent-Cc', 'Resent-Bcc',
      'Resent-Message-ID',
      'Date',
      'From', 'Sender', 'Reply-To',
      'To', 'Cc', 'Bcc',
      'Message-ID', 'In-Reply-To', 'References',
      'Subject',
      'Comments', 'Keywords',
      'X-My-Header'
      ) as $name)
    {
      $header = $this->_factory->createHeader($name);
      $this->assertIsA($header, 'Swift_Mime_Header',
        '%s: All produced Headers should implement Swift_Mime_Header.'
        );
    }
  }
  
  public function testCreatingReturnPathHeader()
  {
    $return = $this->_factory->createHeader(
      'Return-Path', 'chris@swiftmailer.org'
      );
    $this->assertEqual('Return-Path: <chris@swiftmailer.org>' . "\r\n",
      $return->toString()
      );
  }
  
  public function testCreatingEmptyReturnPath()
  {
    $return = $this->_factory->createHeader('Return-Path');
    $this->assertEqual('Return-Path: <>' . "\r\n",
      $return->toString()
      );
  }
  
  public function testCreatingReceivedHeader()
  {
    $received = $this->_factory->createHeader('Received', $this->_timestamp);
    $this->assertEqual('Received: ; ' . $this->_rfc2822Date . "\r\n",
      $received->toString()
      );
  }
  
  public function testCreatingReceivedHeaderWithInfo()
  {
    $received = $this->_factory->createHeader('Received', $this->_timestamp,
      array(
        array('name' => 'from', 'value' => 'mx1.googlemail.com'),
        array('name' => 'by', 'value' => 'mail.swiftmailer.org'),
        array('name' => 'with', 'value' => 'ESMTP', 'comment' => 'Exim4'),
        array('name' => 'for', 'value' => 'chris@swiftmailer.org')
        )
      );
    $this->assertEqual(
      'Received: from mx1.googlemail.com by mail.swiftmailer.org' . "\r\n" .
      ' with ESMTP (Exim4) for chris@swiftmailer.org; ' . $this->_rfc2822Date . "\r\n",
      $received->toString()
      );
  }
  
  public function testCreatingResentDateHeader()
  {
    $resentDate = $this->_factory->createHeader('Resent-Date', $this->_timestamp);
    $this->assertEqual('Resent-Date: ' . $this->_rfc2822Date . "\r\n",
      $resentDate->toString()
      );
  }
  
  public function testCreatingResentFromHeader()
  {
    $resentFrom = $this->_factory->createHeader('Resent-From', 'chris@swiftmailer.org');
    $this->assertEqual('Resent-From: chris@swiftmailer.org' . "\r\n",
      $resentFrom->toString()
      );
  }
  
  public function testCreatingResentFromWithName()
  {
    $resentFrom = $this->_factory->createHeader(
      'Resent-From', array('chris@swiftmailer.org' => 'Chris Corbyn')
      );
    $this->assertEqual('Resent-From: Chris Corbyn <chris@swiftmailer.org>' . "\r\n",
      $resentFrom->toString()
      );
  }
  
  public function testCreatingResentFromWithUtf8Name()
  {
    $resentFrom = $this->_factory->createHeader(
      'Resent-From', array('chris@swiftmailer.org' => 'Код')
      );
    $this->assertEqual('Resent-From: =?utf-8?Q?=D0=9A=D0=BE=D0=B4?= <chris@swiftmailer.org>' . "\r\n",
      $resentFrom->toString()
      );
  }
  
  public function testCreatingResentSenderHeader()
  {
    $resentSender = $this->_factory->createHeader('Resent-Sender', 'chris@swiftmailer.org');
    $this->assertEqual('Resent-Sender: chris@swiftmailer.org' . "\r\n",
      $resentSender->toString()
      );
  }
  
  public function testCreatingResentSenderWithName()
  {
    $resentSender = $this->_factory->createHeader(
      'Resent-Sender', array('chris@swiftmailer.org' => 'Chris Corbyn')
      );
    $this->assertEqual('Resent-Sender: Chris Corbyn <chris@swiftmailer.org>' . "\r\n",
      $resentSender->toString()
      );
  }
  
  public function testCreatingResentSenderWithUtf8Name()
  {
    $resentSender = $this->_factory->createHeader(
      'Resent-Sender', array('chris@swiftmailer.org' => 'Код')
      );
    $this->assertEqual('Resent-Sender: =?utf-8?Q?=D0=9A=D0=BE=D0=B4?= <chris@swiftmailer.org>' . "\r\n",
      $resentSender->toString()
      );
  }
  
  public function testCreatingResentToHeader()
  {
    $resentTo = $this->_factory->createHeader('Resent-To', 'mark@swiftmailer.org');
    $this->assertEqual('Resent-To: mark@swiftmailer.org' . "\r\n",
      $resentTo->toString()
      );
  }
  
  public function testCreatingResentToWithName()
  {
    $resentTo = $this->_factory->createHeader(
      'Resent-To', array('mark@swiftmailer.org' => 'Mark')
      );
    $this->assertEqual('Resent-To: Mark <mark@swiftmailer.org>' . "\r\n",
      $resentTo->toString()
      );
  }
  
  public function testCreatingResentToWithUtf8Name()
  {
    $resentTo = $this->_factory->createHeader(
      'Resent-To', array('mark@swiftmailer.org' => 'Код')
      );
    $this->assertEqual(
      'Resent-To: =?utf-8?Q?=D0=9A=D0=BE=D0=B4?= <mark@swiftmailer.org>' . "\r\n",
      $resentTo->toString()
      );
  }
  
  public function testCreatingResentToList()
  {
    $resentTo = $this->_factory->createHeader(
      'Resent-To', array('mark@swiftmailer.org' => 'Mark',
        'chris@swiftmailer.org' => 'Chris Corbyn',
        'someone@anotherdomain.com')
      );
    $this->assertEqual(
      'Resent-To: Mark <mark@swiftmailer.org>, Chris Corbyn <chris@swiftmailer.org>,' . "\r\n" .
      ' someone@anotherdomain.com' . "\r\n",
      $resentTo->toString()
      );
  }
  
  public function testCreatingResentCcHeader()
  {
    $resentCc = $this->_factory->createHeader('Resent-Cc', 'mark@swiftmailer.org');
    $this->assertEqual('Resent-Cc: mark@swiftmailer.org' . "\r\n",
      $resentCc->toString()
      );
  }
  
  public function testCreatingResentCcWithName()
  {
    $resentCc = $this->_factory->createHeader(
      'Resent-Cc', array('mark@swiftmailer.org' => 'Mark')
      );
    $this->assertEqual('Resent-Cc: Mark <mark@swiftmailer.org>' . "\r\n",
      $resentCc->toString()
      );
  }
  
  public function testCreatingResentCcWithUtf8Name()
  {
    $resentCc = $this->_factory->createHeader(
      'Resent-Cc', array('mark@swiftmailer.org' => 'Код')
      );
    $this->assertEqual(
      'Resent-Cc: =?utf-8?Q?=D0=9A=D0=BE=D0=B4?= <mark@swiftmailer.org>' . "\r\n",
      $resentCc->toString()
      );
  }
  
  public function testCreatingResentCcList()
  {
    $resentCc = $this->_factory->createHeader(
      'Resent-Cc', array('mark@swiftmailer.org' => 'Mark',
        'chris@swiftmailer.org' => 'Chris Corbyn',
        'someone@anotherdomain.com')
      );
    $this->assertEqual(
      'Resent-Cc: Mark <mark@swiftmailer.org>, Chris Corbyn <chris@swiftmailer.org>,' . "\r\n" .
      ' someone@anotherdomain.com' . "\r\n",
      $resentCc->toString()
      );
  }
  
  public function testCreatingResentBccHeader()
  {
    $resentBcc = $this->_factory->createHeader('Resent-Bcc', 'mark@swiftmailer.org');
    $this->assertEqual('Resent-Bcc: mark@swiftmailer.org' . "\r\n",
      $resentBcc->toString()
      );
  }
  
  public function testCreatingResentBccWithName()
  {
    $resentBcc = $this->_factory->createHeader(
      'Resent-Bcc', array('mark@swiftmailer.org' => 'Mark')
      );
    $this->assertEqual('Resent-Bcc: Mark <mark@swiftmailer.org>' . "\r\n",
      $resentBcc->toString()
      );
  }
  
  public function testCreatingResentBccWithUtf8Name()
  {
    $resentBcc = $this->_factory->createHeader(
      'Resent-Bcc', array('mark@swiftmailer.org' => 'Код')
      );
    $this->assertEqual(
      'Resent-Bcc: =?utf-8?Q?=D0=9A=D0=BE=D0=B4?= <mark@swiftmailer.org>' . "\r\n",
      $resentBcc->toString()
      );
  }
  
  public function testCreatingResentBccList()
  {
    $resentBcc = $this->_factory->createHeader(
      'Resent-Bcc', array('mark@swiftmailer.org' => 'Mark',
        'chris@swiftmailer.org' => 'Chris Corbyn',
        'someone@anotherdomain.com')
      );
    $this->assertEqual(
      'Resent-Bcc: Mark <mark@swiftmailer.org>, Chris Corbyn <chris@swiftmailer.org>,' . "\r\n" .
      ' someone@anotherdomain.com' . "\r\n",
      $resentBcc->toString()
      );
  }
  
  public function testCreatingResentMessageIdHeader()
  {
    $resentMsgId = $this->_factory->createHeader('Resent-Message-ID', 'foo@bar');
    $this->assertEqual('Resent-Message-ID: <foo@bar>' . "\r\n",
      $resentMsgId->toString()
      );
  }
  
  public function testCreatingDateHeader()
  {
    $date = $this->_factory->createHeader('Date', $this->_timestamp);
    $this->assertEqual('Date: ' . $this->_rfc2822Date . "\r\n",
      $date->toString()
      );
  }
  
  public function testCreatingFromHeader()
  {
    $from = $this->_factory->createHeader('From', 'chris@swiftmailer.org');
    $this->assertEqual('From: chris@swiftmailer.org' . "\r\n",
      $from->toString()
      );
  }
  
  public function testCreatingFromWithName()
  {
    $from = $this->_factory->createHeader(
      'From', array('chris@swiftmailer.org' => 'Chris Corbyn')
      );
    $this->assertEqual('From: Chris Corbyn <chris@swiftmailer.org>' . "\r\n",
      $from->toString()
      );
  }
  
  public function testCreatingFromWithUtf8Name()
  {
    $from = $this->_factory->createHeader(
      'From', array('chris@swiftmailer.org' => 'Код')
      );
    $this->assertEqual('From: =?utf-8?Q?=D0=9A=D0=BE=D0=B4?= <chris@swiftmailer.org>' . "\r\n",
      $from->toString()
      );
  }
  
  public function testCreatingSenderHeader()
  {
    $sender = $this->_factory->createHeader('Sender', 'chris@swiftmailer.org');
    $this->assertEqual('Sender: chris@swiftmailer.org' . "\r\n",
      $sender->toString()
      );
  }
  
  public function testCreatingSenderWithName()
  {
    $sender = $this->_factory->createHeader(
      'Sender', array('chris@swiftmailer.org' => 'Chris Corbyn')
      );
    $this->assertEqual('Sender: Chris Corbyn <chris@swiftmailer.org>' . "\r\n",
      $sender->toString()
      );
  }
  
  public function testCreatingSenderWithUtf8Name()
  {
    $sender = $this->_factory->createHeader(
      'Sender', array('chris@swiftmailer.org' => 'Код')
      );
    $this->assertEqual('Sender: =?utf-8?Q?=D0=9A=D0=BE=D0=B4?= <chris@swiftmailer.org>' . "\r\n",
      $sender->toString()
      );
  }
  
  public function testCreatingReplyToHeader()
  {
    $replyTo = $this->_factory->createHeader('Reply-To', 'mark@swiftmailer.org');
    $this->assertEqual('Reply-To: mark@swiftmailer.org' . "\r\n",
      $replyTo->toString()
      );
  }
  
  public function testCreatingReplyToWithName()
  {
    $replyTo = $this->_factory->createHeader(
      'Reply-To', array('mark@swiftmailer.org' => 'Mark')
      );
    $this->assertEqual('Reply-To: Mark <mark@swiftmailer.org>' . "\r\n",
      $replyTo->toString()
      );
  }
  
  public function testCreatingReplyToWithUtf8Name()
  {
    $replyTo = $this->_factory->createHeader(
      'Reply-To', array('mark@swiftmailer.org' => 'Код')
      );
    $this->assertEqual(
      'Reply-To: =?utf-8?Q?=D0=9A=D0=BE=D0=B4?= <mark@swiftmailer.org>' . "\r\n",
      $replyTo->toString()
      );
  }
  
  public function testCreatingReplyToList()
  {
    $replyTo = $this->_factory->createHeader(
      'Reply-To', array('mark@swiftmailer.org' => 'Mark',
        'chris@swiftmailer.org' => 'Chris Corbyn',
        'someone@anotherdomain.com')
      );
    $this->assertEqual(
      'Reply-To: Mark <mark@swiftmailer.org>, Chris Corbyn <chris@swiftmailer.org>,' . "\r\n" .
      ' someone@anotherdomain.com' . "\r\n",
      $replyTo->toString()
      );
  }
  
  public function testCreatingToHeader()
  {
    $to = $this->_factory->createHeader('To', 'mark@swiftmailer.org');
    $this->assertEqual('To: mark@swiftmailer.org' . "\r\n",
      $to->toString()
      );
  }
  
  public function testCreatingToWithName()
  {
    $to = $this->_factory->createHeader(
      'To', array('mark@swiftmailer.org' => 'Mark')
      );
    $this->assertEqual('To: Mark <mark@swiftmailer.org>' . "\r\n",
      $to->toString()
      );
  }
  
  public function testCreatingToWithUtf8Name()
  {
    $to = $this->_factory->createHeader(
      'To', array('mark@swiftmailer.org' => 'Код')
      );
    $this->assertEqual(
      'To: =?utf-8?Q?=D0=9A=D0=BE=D0=B4?= <mark@swiftmailer.org>' . "\r\n",
      $to->toString()
      );
  }
  
  public function testCreatingToList()
  {
    $to = $this->_factory->createHeader(
      'To', array('mark@swiftmailer.org' => 'Mark',
        'chris@swiftmailer.org' => 'Chris Corbyn',
        'someone@anotherdomain.com')
      );
    $this->assertEqual(
      'To: Mark <mark@swiftmailer.org>, Chris Corbyn <chris@swiftmailer.org>,' . "\r\n" .
      ' someone@anotherdomain.com' . "\r\n",
      $to->toString()
      );
  }
  
  public function testCreatingCcHeader()
  {
    $cc = $this->_factory->createHeader('Cc', 'mark@swiftmailer.org');
    $this->assertEqual('Cc: mark@swiftmailer.org' . "\r\n",
      $cc->toString()
      );
  }
  
  public function testCreatingCcWithName()
  {
    $cc = $this->_factory->createHeader(
      'Cc', array('mark@swiftmailer.org' => 'Mark')
      );
    $this->assertEqual('Cc: Mark <mark@swiftmailer.org>' . "\r\n",
      $cc->toString()
      );
  }
  
  public function testCreatingCcWithUtf8Name()
  {
    $cc = $this->_factory->createHeader(
      'Cc', array('mark@swiftmailer.org' => 'Код')
      );
    $this->assertEqual(
      'Cc: =?utf-8?Q?=D0=9A=D0=BE=D0=B4?= <mark@swiftmailer.org>' . "\r\n",
      $cc->toString()
      );
  }
  
  public function testCreatingCcList()
  {
    $cc = $this->_factory->createHeader(
      'Cc', array('mark@swiftmailer.org' => 'Mark',
        'chris@swiftmailer.org' => 'Chris Corbyn',
        'someone@anotherdomain.com')
      );
    $this->assertEqual(
      'Cc: Mark <mark@swiftmailer.org>, Chris Corbyn <chris@swiftmailer.org>,' . "\r\n" .
      ' someone@anotherdomain.com' . "\r\n",
      $cc->toString()
      );
  }
  
  public function testCreatingBccHeader()
  {
    $bcc = $this->_factory->createHeader('Bcc', 'mark@swiftmailer.org');
    $this->assertEqual('Bcc: mark@swiftmailer.org' . "\r\n",
      $bcc->toString()
      );
  }
  
  public function testCreatingBccWithName()
  {
    $bcc = $this->_factory->createHeader(
      'Bcc', array('mark@swiftmailer.org' => 'Mark')
      );
    $this->assertEqual('Bcc: Mark <mark@swiftmailer.org>' . "\r\n",
      $bcc->toString()
      );
  }
  
  public function testCreatingBccWithUtf8Name()
  {
    $bcc = $this->_factory->createHeader(
      'Bcc', array('mark@swiftmailer.org' => 'Код')
      );
    $this->assertEqual(
      'Bcc: =?utf-8?Q?=D0=9A=D0=BE=D0=B4?= <mark@swiftmailer.org>' . "\r\n",
      $bcc->toString()
      );
  }
  
  public function testCreatingBccList()
  {
    $bcc = $this->_factory->createHeader(
      'Bcc', array('mark@swiftmailer.org' => 'Mark',
        'chris@swiftmailer.org' => 'Chris Corbyn',
        'someone@anotherdomain.com')
      );
    $this->assertEqual(
      'Bcc: Mark <mark@swiftmailer.org>, Chris Corbyn <chris@swiftmailer.org>,' . "\r\n" .
      ' someone@anotherdomain.com' . "\r\n",
      $bcc->toString()
      );
  }
  
  public function testCreatingMessageIdHeader()
  {
    $msgId = $this->_factory->createHeader('Message-ID', 'foo@bar');
    $this->assertEqual('Message-ID: <foo@bar>' . "\r\n", $msgId->toString());
  }
  
  public function testCreatingInReplyToHeader()
  {
    $irp = $this->_factory->createHeader('In-Reply-To', 'foo@bar');
    $this->assertEqual('In-Reply-To: <foo@bar>' . "\r\n", $irp->toString());
  }
  
  public function testCreatingInReplyToHeaderWithList()
  {
    $irp = $this->_factory->createHeader('In-Reply-To',
      array('foo@bar', 'zip@button')
      );
    $this->assertEqual('In-Reply-To: <foo@bar> <zip@button>' . "\r\n",
      $irp->toString()
      );
  }
  
  public function testCreatingReferencesHeader()
  {
    $references = $this->_factory->createHeader('References', 'foo@bar');
    $this->assertEqual('References: <foo@bar>' . "\r\n", $references->toString());
  }
  
  public function testCreatingReferenceseaderWithList()
  {
    $references = $this->_factory->createHeader('References',
      array('foo@bar', 'zip@button')
      );
    $this->assertEqual('References: <foo@bar> <zip@button>' . "\r\n",
      $references->toString()
      );
  }
  
  public function testCreatingSubjectHeader()
  {
    $subject = $this->_factory->createHeader('Subject', 'Testing');
    $this->assertEqual('Subject: Testing' . "\r\n", $subject->toString());
  }
  
  public function testCreatingUtf8Subject()
  {
    $subject = $this->_factory->createHeader('Subject', 'руководишь');
    $this->assertEqual(
      'Subject: =?utf-8?Q?=D1=80=D1=83=D0=BA=D0=BE=D0=B2=D0=BE=D0=B4=D0=B8?=' . "\r\n" .
      ' =?utf-8?Q?=D1=88=D1=8C?=' . "\r\n",
      $subject->toString()
      );
  }
  
  public function testCreatingCommentsHeader()
  {
    $comments = $this->_factory->createHeader('Comments', 'Testing');
    $this->assertEqual('Comments: Testing' . "\r\n", $comments->toString());
  }
  
  public function testCreatingUtf8Comments()
  {
    $comments = $this->_factory->createHeader('Comments', 'руководишь');
    $this->assertEqual(
      'Comments: =?utf-8?Q?=D1=80=D1=83=D0=BA=D0=BE=D0=B2=D0=BE=D0=B4=D0=B8?=' . "\r\n" .
      ' =?utf-8?Q?=D1=88=D1=8C?=' . "\r\n",
      $comments->toString()
      );
  }
  
  public function testCreatingKeywordsHeader()
  {
    $keywords = $this->_factory->createHeader('Keywords',
      array('foo bar', 'zip button')
      );
    $this->assertEqual('Keywords: foo bar, zip button' . "\r\n",
      $keywords->toString()
      );
  }
  
  public function testCreatingKeywordsHeaderWithUtf8()
  {
    $keywords = $this->_factory->createHeader('Keywords',
      array('руководишь', 'zip button')
      );
    $this->assertEqual(
      'Keywords: =?utf-8?Q?=D1=80=D1=83=D0=BA=D0=BE=D0=B2=D0=BE=D0=B4=D0=B8?=' . "\r\n" .
      ' =?utf-8?Q?=D1=88=D1=8C?=, zip button' . "\r\n",
      $keywords->toString()
      );
  }
  
  public function testCreatingReturnPathHeaderFromString()
  {
    $return = $this->_factory->createHeaderFromString(
      'Return-Path: <noreply@devnetwork.net>'
      );
    $this->assertEqual('Return-Path', $return->getName());
    $this->assertEqual('noreply@devnetwork.net', $return->getAddress());
  }
  
}
