<?php

/*
 This example creates an email with an embedded image.
 */

//Enable full error reporting
error_reporting(E_ALL | E_STRICT); ini_set('display_errors', true);
//For E_STRICT you should set this
date_default_timezone_set('Australia/Melbourne');

//Require the injector
require_once dirname(__FILE__) . '/../../lib/swift_required.php';

$message = Swift_MimeFactory::create('message');
$message->setSubject('An image and a recipe!')
  ->setTo(array('rob@site.com' => 'Rob'))
  ->setFrom(array('chris@w3style.co.uk' => 'Myself'))
  ->attach(
    Swift_MimeFactory::create('part')
      ->setContentType('text/html')
      ->setBody(
        "Here's the Swift Mailer logo <img src=\"" . $message->embed(
          Swift_MimeFactory::create('image')
            ->setContentType('image/gif')
            ->setBody(file_get_contents(dirname(__FILE__) . '/files/swift_logo.gif'))
          ) . "\" >," .
        " it needs a face lift!"
        )
    )
    ->attach(
    Swift_MimeFactory::create('attachment')
      ->setContentType('application/pdf')
      ->setFilename('stifado.pdf')
      ->setBody(file_get_contents(dirname(__FILE__) . '/files/BeefStifado.pdf'))
    )
  ;
  
echo $message->toString();
file_put_contents('/Users/d11wtq/complex.eml', $message->toString());
