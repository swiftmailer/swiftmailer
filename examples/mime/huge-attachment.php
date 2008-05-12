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

for ($i = 1; $i <= 10; $i++)
{
  @unlink('sample-file');
  `dd if=/dev/random of=sample-file bs=1048576 count=$i`;
  
  $message = Swift_Message::newInstance()
  ->setSubject('Last.fm download')
  ->setTo(array('rob@site.com' => 'Rob'))
  ->setFrom(array('chris@w3style.co.uk' => 'Myself'))
  ->setBody("Here's the last.fm dmg you needed")
  ->attach(Swift_Attachment::fromPath('sample-file'))
  ;
  
  $message->toString();

  echo "Memory @ {$i}MB = ";
  echo round((memory_get_peak_usage() / ( 1024 * 1024)), 3) . PHP_EOL;

  $message = null;
  unset($message);
}

