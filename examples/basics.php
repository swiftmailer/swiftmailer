<?php

/*
 This example creates a message from a single sender to a single recipient.
 */

//Require the injector
require_once dirname(__FILE__) . '/../../lib/swift_required.php';

//Create the mailer
$mailer = new Swift_Mailer(new Swift_SmtpTransport('smtp.isp.tld'));

//Create a message
$message = Swift_Message::newInstance('A basic message', 'just testing')
  ->setTo(array('chris.corbyn@swiftmailer.org' => 'Test: Testing'))
  ->setFrom(array('chris@w3style.co.uk' => 'Myself'))
  ;

//Send it
if ($mailer->send($message))
{
  echo "Message sent!";
}
else
{
  echo "Error sending";
}
