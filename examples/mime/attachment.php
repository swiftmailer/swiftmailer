<?php

/*
 This example creates an email with a PDF attachment.

 First we need to load in the swift_required.php file.
 
 Next we instantiate the Mailer using a Transport of our choosing.
 
 Then we create a new Message with Swift_Message::newInstance() and set up
 that message by calling methods on it.
 
 Each setter method provides a fluid interface so it's possible to chain
 method calls together for convenience.
 
 The Attachment is added by using Swift_Attachment::fromPath(), which creates
 a new instance of Swift_Attachment based upon the file contents at the given
 path.  The content-type can be (and should be) specified since Swift won't
 determine the file type itself.
 
 Once the message has been created, it is sent using $mailer->send($message).
 
 */

$attachment_path = '../files/BeefStifado.pdf';

require_once '../../lib/swift_required.php';

$mailer = new Swift_Mailer(new Swift_SmtpTransport('smtp.isp.tld'));

$message = Swift_Message::newInstance()
  ->setSubject('A recipe for beef stifado')
  ->setTo(array('chris@w3style.co.uk' => 'Chris Corbyn'))
  ->setFrom(array('chris@w3style.co.uk' => 'Chris Corbyn'))
  ->setBody("Here's a recipe for beef stifado")
  ->attach(Swift_Attachment::fromPath($attachment_path, 'application/pdf'))
  ;

if ($mailer->send($message))
{
  echo 'Message was sent successfully';
}
else
{
  echo 'Message did not send';
}
