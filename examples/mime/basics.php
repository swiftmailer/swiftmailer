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

$message = Swift_Message::newInstance('A basic message', 'just testing')
  ->setTo(array('chris.corbyn@swiftmailer.org' => 'Test: Testing'))
  ->setFrom(array('chris@w3style.co.uk' => 'Myself'))
  ;
file_put_contents('mail.eml', $message->toString());
