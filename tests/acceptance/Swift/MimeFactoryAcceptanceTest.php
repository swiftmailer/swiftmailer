<?php

require_once 'swift_required.php';

//This is more of a "cross your fingers and hope it works" test!

class Swift_MimeFactoryAcceptanceTest extends UnitTestCase
{
  
  public function testCreateForMessage()
  {
    $fMime = Swift_MimeFactory::getInstance();
    $message = $fMime->create('message')
      ->setSubject('Some subject')
      ->setBody('test body')
      ->setContentType('text/html')
      ->setCharset('utf-8')
      ;
    $id = $message->getId();
    $date = date('r', $message->getDate());
    $enc = $message->getEncoder()->getName();
    $this->assertEqual(
      'Message-ID: <' . $id . '>' . "\r\n" .
      'Date: ' . $date . "\r\n" .
      'Subject: Some subject' . "\r\n" .
      'From: '. "\r\n" .
      'MIME-Version: 1.0' . "\r\n" .
      'Content-Type: text/html; charset=utf-8' . "\r\n" .
      'Content-Transfer-Encoding: ' . $enc . "\r\n" .
      "\r\n" .
      'test body',
      $message->toString()
      );
  }
  
  public function testCreateMessageWrapper()
  {
    $fMime = Swift_MimeFactory::getInstance();
    $message = $fMime->createMessage('Some subject', 'test body', 'text/html', 'utf-8');
    $id = $message->getId();
    $date = date('r', $message->getDate());
    $enc = $message->getEncoder()->getName();
    $this->assertEqual(
      'Message-ID: <' . $id . '>' . "\r\n" .
      'Date: ' . $date . "\r\n" .
      'Subject: Some subject' . "\r\n" .
      'From: '. "\r\n" .
      'MIME-Version: 1.0' . "\r\n" .
      'Content-Type: text/html; charset=utf-8' . "\r\n" .
      'Content-Transfer-Encoding: ' . $enc . "\r\n" .
      "\r\n" .
      'test body',
      $message->toString()
      );
  }
  
  public function testCreatePart()
  {
    $fMime = Swift_MimeFactory::getInstance();
    $part = $fMime->createPart('example', 'text/plain', 'us-ascii');
    $enc = $part->getEncoder()->getName();
    $this->assertEqual(
      'Content-Type: text/plain; charset=us-ascii' . "\r\n" .
      'Content-Transfer-Encoding: ' . $enc . "\r\n" .
      "\r\n" .
      'example',
      $part->toString()
      );
  }
  
  public function testCreateAttachment()
  {
    $fMime = Swift_MimeFactory::getInstance();
    $attachment = $fMime->createAttachment('body', 'foo.pdf', 'application/pdf');
    $this->assertEqual(
      'Content-Type: application/pdf; name=foo.pdf' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n" .
      'Content-Disposition: attachment; filename=foo.pdf' . "\r\n" .
      "\r\n" .
      base64_encode('body'),
      $attachment->toString()
      );
  }
  
  public function testCreateEmbeddedFile()
  {
    $fMime = Swift_MimeFactory::getInstance();
    $file = $fMime->createEmbeddedFile('body', 'foo.pdf', 'application/pdf');
    $id = $file->getId();
    $this->assertEqual(
      'Content-Type: application/pdf; name=foo.pdf' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n" .
      'Content-Disposition: inline; filename=foo.pdf' . "\r\n" .
      'Content-ID: <' . $id . '>' . "\r\n" .
      "\r\n" .
      base64_encode('body'),
      $file->toString()
      );
  }
  
}
