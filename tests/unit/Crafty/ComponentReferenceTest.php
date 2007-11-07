<?php

require_once 'Crafty/ComponentReference.php';

class Crafty_ComponentReferenceTest extends UnitTestCase
{
  
  public function testGetComponentName()
  {
    $ref = new Crafty_ComponentReference('test');
    $this->assertEqual('test', $ref->getComponentName());
    
    $ref = new Crafty_ComponentReference('other');
    $this->assertEqual('other', $ref->getComponentName());
  }
  
}
