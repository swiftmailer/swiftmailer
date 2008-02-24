<?php

require_once 'swift_required.php';

//This is more of a "cross your fingers and hope it works" test!

class Swift_TransportFactoryAcceptanceTest extends UnitTestCase
{
  
  public function testInstantiatingAllClasses()
  {
    $fTransport = Swift_TransportFactory::getInstance();
    $map = $fTransport->getDependencyMap();
    foreach ($map as $key => $spec)
    {
      $object = $fTransport->create($key);
      $this->assertIsA($object, $spec['class']);
    }
  }
  
  public function testCreateSmtp()
  {
    $fTransport = Swift_TransportFactory::getInstance();
    $smtp = $fTransport->createSmtp(
      'smtp.isp.tld', 465, Swift_TransportFactory::SMTP_ENC_TLS
      );
    $this->assertIsA($smtp, 'Swift_Transport');
  }
  
  public function testCreateSendmail()
  {
    $fTransport = Swift_TransportFactory::getInstance();
    $sendmail = $fTransport->createSendmail(
      '/var/qmail/bin/sendmail -t'
      );
    $this->assertIsA($sendmail, 'Swift_Transport');
  }
  
  public function testCreateMail()
  {
    $fTransport = Swift_TransportFactory::getInstance();
    $mail = $fTransport->createMail(
      '-f%s'
      );
    $this->assertIsA($mail, 'Swift_Transport');
  }
  
  public function testCreateFailover()
  {
    $fTransport = Swift_TransportFactory::getInstance();
    $t = $fTransport->createFailover(array(
      $fTransport->createSmtp(),
      $fTransport->createMail()
      ));
    $this->assertIsA($t, 'Swift_Transport');
  }
  
  public function testCreateLoadBalanced()
  {
    $fTransport = Swift_TransportFactory::getInstance();
    $t = $fTransport->createLoadBalanced(array(
      $fTransport->createSmtp(),
      $fTransport->createMail()
      ));
    $this->assertIsA($t, 'Swift_Transport');
  }
  
}
