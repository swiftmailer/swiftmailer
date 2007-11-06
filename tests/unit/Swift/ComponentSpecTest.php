<?php

require_once dirname(__FILE__) . '/../../config.php';

require_once 'Swift/ComponentSpec.php';

class Swift_ComponentSpecTest extends UnitTestCase
{
  
  private $_spec;
  
  public function setUp()
  {
    $this->_spec = new Swift_ComponentSpec();
  }
  
  public function testSetAndGetClassName()
  {
    $this->_spec->setClassName('EmptyClass');
    $this->assertEqual('EmptyClass', $this->_spec->getClassName());
  }
  
  public function testSetAndGetConstructorArgs()
  {
    $o = new stdClass();
    $this->_spec->setConstructorArgs(array($o, 'foo', 123));
    $this->assertIdentical(array($o, 'foo', 123),
      $this->_spec->getConstructorArgs());
  }
  
  public function testSetAndGetProperty()
  {
    $this->_spec->setProperty('propName1', 'value');
    $this->assertIdentical('value', $this->_spec->getProperty('propName1'));
    
    $o = new stdClass();
    $o->foo = 'bar';
    
    $this->_spec->setProperty('propName2', $o);
    $this->assertIdentical($o, $this->_spec->getProperty('propName2'));
    
    $this->_spec->setProperty('propName3', array('one', 2, '3'));
    $this->assertIdentical(array('one', 2, '3'),
      $this->_spec->getProperty('propName3'));
  }
  
  public function testGetProperties()
  {
    $this->_spec->setProperty('testProp1', 'x');
    $this->_spec->setProperty('testProp2', 'y');
    $this->_spec->setProperty('testProp3', 'z');
    
    $this->assertEqual(array('testProp1'=>'x', 'testProp2'=>'y', 'testProp3'=>'z'),
      $this->_spec->getProperties());
  }
  
  public function testSetAndGetSingleton()
  {
    $this->assertFalse($this->_spec->isSingleton(),
      'Singletons should be off by default');
    
    $this->_spec->setSingleton(true);
    $this->assertTrue($this->_spec->isSingleton(),
      'Singleton should be turned on');
    
    $this->_spec->setSingleton(false);
    $this->assertFalse($this->_spec->isSingleton(),
      'Singleton should be turned off');
  }
  
}
