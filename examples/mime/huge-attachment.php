<?php

/*
 This example creates an email with a massive attachment.
 You will need to modify the file path to some large (100MB+) file, and you
 will need to set a writable directory.
 The demonstration is to show that you can attach several gigabytes of data
 to an email in theory, without coming even close to your PHP memory limit.
 */

//Enable full error reporting
error_reporting(E_ALL | E_STRICT); ini_set('display_errors', true);
//For E_STRICT you should set this
date_default_timezone_set('Australia/Melbourne');

//Require the injector
require_once dirname(__FILE__) . '/../../lib/swift_required.php';

$hugeFile = '/Users/d11wtq/last.fm.dmg'; //Change this

Swift_MimeFactory::setCacheType('disk');
Swift_MimeFactory::setTempPath('/tmp');

$message = Swift_MimeFactory::create('message')
  ->setSubject('Last.fm download')
  ->setTo(array('rob@site.com' => 'Rob'))
  ->setFrom(array('chris@w3style.co.uk' => 'Myself'))
  ->setBody("Here's the last.fm dmg you needed")
  ->attach(
    Swift_MimeFactory::create('attachment')
      ->setContentType('application/octet-stream')
      ->setFilename('photoshop.dmg')
      ->setFile(new Swift_ByteStream_FileByteStream($hugeFile))
    )
  ;
 
$ios = new Swift_ByteStream_FileByteStream('/Users/d11wtq/email.eml', true);
$message->toByteStream($ios);
echo PHP_EOL;
echo round(memory_get_peak_usage() / 1024 / 1024, 4) . PHP_EOL;
