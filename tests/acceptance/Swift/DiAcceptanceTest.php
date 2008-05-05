<?php

require_once 'swift_required.php';
require_once 'Swift/Tests/SwiftUnitTestCase.php';

//This is more of a "cross your fingers and hope it works" test!

class Swift_DiAcceptanceTest extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testInstantiatingAllClasses()
  {
    $di = Swift_Di::getInstance();
    $di->setLookup('cache', 'di:mime.arraycache');
    $di->setLookup('charset', 'string:utf-8');
    $di->setLookup('temppath', 'string:/tmp');
    $di->setLookup('xheadername', 'string:X-Foo');
    $map = $di->getDependencyMap();
    foreach ($map as $key => $spec)
    {
      $object = $di->create($key);
      $this->assertIsA($object, $spec['class']);
    }
  }
  
}
