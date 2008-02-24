<?php

require_once 'swift_required.php';

//This is more of a "cross your fingers and hope it works" test!

class Swift_TransportFactoryAcceptanceTest extends UnitTestCase
{
  
  public function testInstantiatingAllClasses()
  {
    $factory = Swift_TransportFactory::getInstance();
    $map = $factory->getDependencyMap();
    foreach ($map as $key => $spec)
    {
      $object = $factory->create($key);
      $this->assertIsA($object, $spec['class']);
    }
  }
  
}
