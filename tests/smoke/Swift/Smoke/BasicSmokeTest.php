<?php

require_once 'Swift/Tests/SwiftSmokeTestCase.php';

class Swift_Smoke_BasicSmokeTest extends Swift_Tests_SwiftSmokeTestCase
{
  
  public function testBasicSending()
  {
    $transportFactory = Swift_TransportFactory::getInstance();
    $log = $transportFactory->create('arraylog');
    $log->setLogEnabled(true);
    
    $mailer = $this->_getMailer();
    $mimeFactory = Swift_MimeFactory::getInstance();
    $message = $mimeFactory->create('message')
      ->setSubject('[Swift Mailer] BasicSmokeTest')
      ->setFrom(array(SWIFT_SMOKE_EMAIL_ADDRESS => 'Chris Corbyn (Swift Mailer)'))
      ->setTo(SWIFT_SMOKE_EMAIL_ADDRESS)
      ->setBody('One, two, three, four, five...' . PHP_EOL .
        'six, seven, eight...'
        )
      ;
    $this->assertEqual(1, $mailer->send($message),
      '%s: The smoke test should send a single message'
      );
    $this->_visualCheck('http://swiftmailer.org/smoke/4.0.0/basic.jpg');
    
    $this->dump($log->dump());
  }
  
}
