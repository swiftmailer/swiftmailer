<?php

require_once 'Swift/Tests/SwiftSmokeTestCase.php';

class Swift_Smoke_InternationalSmokeTest extends Swift_Tests_SwiftSmokeTestCase
{
  
  public function setUp()
  {
    $this->_attFile = dirname(__FILE__) . '/../../../_samples/files/textfile.zip';
  }
  
  public function testAttachmentSending()
  {
    $mailer = $this->_getMailer();
    $mimeFactory = Swift_MimeFactory::getInstance();
    $message = $mimeFactory->create('message')
      ->setCharset('utf-8')
      ->setSubject('[Swift Mailer] InternationalSmokeTest (διεθνής)')
      ->setFrom(array(SWIFT_SMOKE_EMAIL_ADDRESS => 'Χριστοφορου (Swift Mailer)'))
      ->setTo(SWIFT_SMOKE_EMAIL_ADDRESS)
      ->setBody('This message should contain an attached ZIP file (named "κείμενο, εδάφιο, θέμα.zip").' . PHP_EOL .
        'When unzipped, the archive should produce a text file which reads:' . PHP_EOL .
        '"This is part of a Swift Mailer v4 smoke test."' . PHP_EOL .
        PHP_EOL .
        'Following is some arbitrary Greek text:' . PHP_EOL .
        'Δεν βρέθηκαν λέξεις.'
        )
      ->attach($mimeFactory->create('attachment')
        ->setContentType('application/zip')
        ->setFile(new Swift_ByteStream_FileByteStream($this->_attFile))
        ->setFilename('κείμενο, εδάφιο, θέμα.zip')
        )
      ;
    $this->assertEqual(1, $mailer->send($message),
      '%s: The smoke test should send a single message'
      );
    $this->_visualCheck('http://swiftmailer.org/smoke/4.0.0/international.jpg');
  }
  
}
