<?php

/*
 This example takes on message, then places it as an attachment of another
 message, thus creating a forwarded message.  This is fully compliant.
 */

//Enable full error reporting
error_reporting(E_ALL | E_STRICT); ini_set('display_errors', true);
//For E_STRICT you should set this
date_default_timezone_set('Australia/Melbourne');

//Require the injector
require_once dirname(__FILE__) . '/../../lib/swift_required.php';

$message = Swift_MimeFactory::create('message')
  ->setSubject('John???')
  ->setTo(array('rob@site.com' => 'Rob'))
  ->setFrom(array('chris@w3style.co.uk' => 'Myself'))
  ->setBody('john gets on my nerves')
  ;
  
$forward = Swift_MimeFactory::create('message')
  ->setSubject('FW: ' . $message->getSubject())
  ->setTo(array('john.smith@site.com' => 'John Smith'))
  ->setFrom($message->getTo())
  ->setContentType('text/plain')
  ->setBody('Guess what chris told me')
  ->attach(
    Swift_MimeFactory::create('attachment')
      ->setContentType('message/rfc822')
      ->setDisposition('inline')
      ->setEncoder(Swift_MimeFactory::create('7bitencoder'))
      ->setFilename($message->getSubject() . '.eml')
      ->setBody($message->toString())
    )
  ;
  
echo $forward->toString();
file_put_contents('/Users/d11wtq/forward.eml', $forward->toString());
