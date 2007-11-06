<?php

require_once dirname(__FILE__) . '/../../config.php';

require_once 'Swift/ComponentReference.php';

class Swift_ComponentReferenceTest extends UnitTestCase
{
  
  public function testGetComponentName()
  {
    $ref = new Swift_ComponentReference('test');
    $this->assertEqual('test', $ref->getComponentName());
    
    $ref = new Swift_ComponentReference('other');
    $this->assertEqual('other', $ref->getComponentName());
  }
  
}
