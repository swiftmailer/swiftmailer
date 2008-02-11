<?php

/*
 This example creates an email with a PDF attachment using greek chars.
 */

//Enable full error reporting
error_reporting(E_ALL | E_STRICT); ini_set('display_errors', true);
//For E_STRICT you should set this
date_default_timezone_set('Australia/Melbourne');

//Require the injector
require_once dirname(__FILE__) . '/../../lib/swift_required.php';

Swift_MimeFactory::setCharset('utf-8');

$message = Swift_MimeFactory::create('message')
  ->setSubject('Μια συνταγη για να προσπαθησουμε')
  ->setTo(array('rob@site.com' => 'Rob'))
  ->setFrom(array('chris@w3style.co.uk' => 'Χριστοφορου'))
  ->setBody("Να μια συνταγη για το βοειο κρεας στιφαδο")
  ->attach(
    Swift_MimeFactory::create('attachment')
      ->setContentType('application/pdf')
      ->setFilename('μια ωραια συνταγη.pdf')
      ->setBody(file_get_contents(dirname(__FILE__) . '/files/stifado_recipe.pdf'))
    )
  ;
  
echo $message->toString();
file_put_contents('/Users/d11wtq/international.eml', $message->toString());
