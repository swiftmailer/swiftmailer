<?php

require_once dirname(__FILE__) . '/../../config.php';

require_once 'Crafty/ComponentReflector.php';
require_once 'HybridInjectionClass.php';

class Crafty_ComponentReflectorTest extends UnitTestCase
{
  
  private $_reflector;
  
  public function setUp()
  {
    $this->_reflector = new Crafty_ComponentReflector('HybridInjectionClass',
      array('prop3' => 'def'));
  }
  
  public function testReflectorIsReflectionClass()
  {
    $this->assertIsA($this->_reflector, 'ReflectionClass');
  }
  
  public function testNewInstanceType()
  {
    $o = $this->_reflector->newInstance('');
    $this->assertIsA($o, 'HybridInjectionClass');
  }
  
  public function testNewInstanceArgs()
  {
    $o = $this->_reflector->newInstance('abc');
    $this->assertIdentical('def', $o->getProp3());
    $this->assertIdentical('abc', $o->getProp1());
    
    $o = $this->_reflector->newInstance('xyz', 123);
    $this->assertIdentical('def', $o->getProp3());
    $this->assertIdentical('xyz', $o->getProp1());
    $this->assertIdentical(123, $o->getProp2());
  }

}
