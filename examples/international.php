<?php

/*
 This example creates an email with a PDF attachment using greek chars.
 */

//Require the injector
require_once dirname(__FILE__) . '/../../lib/swift_required.php';

$attachment_path = '.files/BeefStifado.pdf';

Swift_MimeFactory::setCharset('utf-8');

$mailer = new Swift_Mailer(new Swift_SmtpTransport('smtp.isp.tld'));

$message = Swift_Message::newInstance('Μια συνταγη για να προσπαθησουμε')
  ->setTo(array('rob@site.com' => 'Rob'))
  ->setFrom(array('chris@w3style.co.uk' => 'Χριστοφορου'))
  ->setBody("Να μια συνταγη για το βοειο κρεας στιφαδο")
  ->attach(
    Swift_Attachment::fromPath($attachment_path)
      ->setFilename('μια ωραια συνταγη.pdf')
    )
  ;

if ($mailer->send($message))
{
  echo 'Message was sent successfully';
}                                                                                
else
{
  echo 'Message did not send';
}
