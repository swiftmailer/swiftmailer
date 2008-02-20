<?php

require_once 'swift_required.php';

//This is more of a "cross your fingers and hope it works" test!

class Swift_MimeFactoryAcceptanceTest extends UnitTestCase
{
  
  public function testInstantiatingAllClasses()
  {
    $map = Swift_MimeFactory::getInstance()->getDependencyMap();
    foreach ($map as $key => $spec)
    {
      $object = Swift_MimeFactory::create($key);
      $this->assertIsA($object, $spec['class']);
    }
  }
  
}
