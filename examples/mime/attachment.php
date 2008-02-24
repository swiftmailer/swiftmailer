<?php

/*
 This example creates an email with a PDF attachment.
 */

//Enable full error reporting
error_reporting(E_ALL | E_STRICT); ini_set('display_errors', true);
//For E_STRICT you should set this
date_default_timezone_set('Australia/Melbourne');

//Require the injector
require_once dirname(__FILE__) . '/../../lib/swift_required.php';

$message = Swift_MimeFactory::create('message')
  ->setSubject('A recipe to try')
  ->setTo(array('rob@site.com' => 'Rob'))
  ->setFrom(array('chris@w3style.co.uk' => 'Myself'))
  ->setBody("Here's a recipe for beef stifado")
  ->attach(
    Swift_MimeFactory::create('attachment')
      ->setContentType('application/pdf')
      ->setFilename('stifado.pdf')
      ->setBody(file_get_contents(dirname(__FILE__) . '/../files/BeefStifado.pdf'))
    )
  ;
