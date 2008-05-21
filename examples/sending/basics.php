<?php

/*
 This example creates a message from a single sender to a single recipient.
 */

//Enable full error reporting
error_reporting(E_ALL | E_STRICT); ini_set('display_errors', true);
//For E_STRICT you should set this
date_default_timezone_set('Australia/Melbourne');

//Require the injector
require_once dirname(__FILE__) . '/../../lib/swift_required.php';

$message = Swift_MimeFactory::create('message')
  ->setSubject('A basic message')
  ->setTo(array('chris.corbyn@sitepoint.com' => 'Chris Corbyn'))
  ->setFrom(array('chris.corbyn@sitepoint.com' => 'Myself'))
  ->setContentType('text/plain')
  ->setCharset('utf-8')
  ->setBody('just testing')
  ;
  
$smtp = new Swift_Transport_EsmtpTransport(new Swift_Transport_StreamBuffer(), array());
$smtp->setHost('gravity.sitepoint.com');

$mailer = new Swift_Mailer($smtp);
var_dump($mailer->send($message));

//echo $message->toString();
//echo PHP_EOL;
//echo round(memory_get_peak_usage() / 1024 / 1024, 4) . PHP_EOL;
