<?php

require_once dirname(__FILE__) . '/../../config.php';

require_once 'Crafty/ComponentFactory.php';
require_once 'Crafty/ComponentSpec.php';
require_once 'Crafty/ComponentReference.php';
require_once 'Crafty/ClassLocator.php';
require_once 'Crafty/ComponentSpecFinder.php';
require_once 'Crafty/ComponentFactoryException.php';
require_once 'EmptyClass.php';
require_once 'EmptyInterface.php';
require_once 'ConstructorInjectionClass.php';
require_once 'SetterInjectionClass.php';

Mock::generate('Crafty_ClassLocator', 'MockClassLocator');
Mock::generate('Crafty_ComponentSpecFinder', 'MockSpecFinder');

class Crafty_ComponentFactoryTest extends UnitTestCase
{
  
  private $_factory;
  
  public function setUp()
  {
    $this->_factory = new Crafty_ComponentFactory();
  }
  
  public function testNewComponentSpec()
  {
    $spec = $this->_factory->newComponentSpec();
    $this->assertIsA($spec, 'Crafty_ComponentSpec');
  }
  
  public function testNewComponentSpecWithArgs()
  {
    $spec = $this->_factory->newComponentSpec(
      'SomeClass', array(), array('foo' => 'bar'), true);
    
    $this->assertIsA($spec, 'Crafty_ComponentSpec');
    $this->assertEqual('SomeClass', $spec->getClassName());
    $this->assertEqual(array(), $spec->getConstructorArgs());
    $this->assertEqual(array('foo' => 'bar'), $spec->getProperties());
    $this->assertTrue($spec->isShared(),
      'ComponentSpec should be for a shared component');
  }
  
  public function testReferenceFor()
  {
    $ref = $this->_factory->referenceFor('test');
    $this->assertIsA($ref, 'Crafty_ComponentReference');
    $this->assertEqual('test', $ref->getComponentName());
  }
  
  public function testSetAndGetComponentSpec()
  {
    $spec = $this->_factory->newComponentSpec();
    $spec->setClassName('stdClass');
    
    $this->_factory->setComponentSpec('testClass', $spec);
    $this->assertIdentical($spec,
      $this->_factory->getComponentSpec('testClass'));
  }
  
  public function testClassLocatorStrategy()
  {
    $locator1 = new MockClassLocator();
    $locator1->expectOnce('classExists');
    $locator1->setReturnValue('classExists', false);
    $locator1->expectNever('includeClass');
    
    $locator2 = new MockClassLocator();
    $locator2->expectOnce('classExists');
    $locator2->setReturnValue('classExists', true);
    $locator2->expectOnce('includeClass');
    
    $spec = $this->_factory->newComponentSpec();
    $spec->setClassName('stdClass');
    
    $this->_factory->setComponentSpec('testClass', $spec);
    
    $this->_factory->registerClassLocator('one', $locator1);
    $this->_factory->registerClassLocator('two', $locator2);
    
    $o = $this->_factory->create('testClass');
  }
  
  public function testCreateReturnsCorrectType()
  {
    $spec = $this->_factory->newComponentSpec();
    $spec->setClassName('EmptyClass');
    
    $this->_factory->setComponentSpec('testClass', $spec);
    
    $o = $this->_factory->create('testClass');
    
    $this->assertIsA($o, 'EmptyClass');
    $this->assertIsA($o, 'EmptyInterface');
  }
  
  public function testConstructorBasedInjectionByValue()
  {
    $spec = $this->_factory->newComponentSpec();
    $spec->setClassName('ConstructorInjectionClass');
    $spec->setConstructorArgs(array('foo', 'bar'));
    
    $this->_factory->setComponentSpec('constructorClass', $spec);
    
    $o = $this->_factory->create('constructorClass');
    
    $this->assertIsA($o, 'ConstructorInjectionClass');
    $this->assertEqual('foo', $o->getProp1());
    $this->assertEqual('bar', $o->getProp2());
  }
  
  public function testSetterBasedInjectionByValue()
  {
    $spec = $this->_factory->newComponentSpec();
    $spec->setClassName('SetterInjectionClass');
    $spec->setProperty('prop1', 'foo');
    $spec->setProperty('prop2', 'bar');
    
    $this->_factory->setComponentSpec('setterClass', $spec);
    
    $o = $this->_factory->create('setterClass');
    
    $this->assertIsA($o, 'SetterInjectionClass');
    $this->assertEqual('foo', $o->getProp1());
    $this->assertEqual('bar', $o->getProp2());
  }
  
  public function testConstructorBasedDependencyInjection()
  {
    $emptyClassSpec = $this->_factory->newComponentSpec();
    $emptyClassSpec->setClassName('EmptyClass');
    
    $setterClassSpec = $this->_factory->newComponentSpec();
    $setterClassSpec->setClassName('SetterInjectionClass');
    $setterClassSpec->setProperty('prop1', 'one');
    $setterClassSpec->setProperty('prop2', 'two');
    
    $diSpec = $this->_factory->newComponentSpec();
    $diSpec->setClassName('ConstructorInjectionClass');
    $diSpec->setConstructorArgs(array(
      $this->_factory->referenceFor('emptyClass'),
      $this->_factory->referenceFor('setterClass')
    ));
    
    $this->_factory->setComponentSpec('emptyClass', $emptyClassSpec);
    $this->_factory->setComponentSpec('setterClass', $setterClassSpec);
    $this->_factory->setComponentSpec('testClass', $diSpec);
    
    $o = $this->_factory->create('testClass');
    
    $this->assertIsA($o, 'ConstructorInjectionClass');
    $this->assertIsA($o->getProp1(), 'EmptyClass');
    $this->assertIsA($o->getProp2(), 'SetterInjectionClass');
    
    $prop2 = $o->getProp2();
    
    $this->assertEqual('one', $prop2->getProp1());
    $this->assertEqual('two', $prop2->getProp2());
  }
  
  public function testSetterBasedDependencyInjection()
  {
    $emptyClassSpec = $this->_factory->newComponentSpec();
    $emptyClassSpec->setClassName('EmptyClass');
    
    $constructorClassSpec = $this->_factory->newComponentSpec();
    $constructorClassSpec->setClassName('ConstructorInjectionClass');
    $constructorClassSpec->setConstructorArgs(array(123, 456));
    
    $diSpec = $this->_factory->newComponentSpec();
    $diSpec->setClassName('SetterInjectionClass');
    $diSpec->setProperty('prop1', $this->_factory->referenceFor('emptyClass'));
    $diSpec->setProperty('prop2', $this->_factory
      ->referenceFor('constructorClass'));
    
    $this->_factory->setComponentSpec('emptyClass', $emptyClassSpec);
    $this->_factory->setComponentSpec('constructorClass', $constructorClassSpec);
    $this->_factory->setComponentSpec('testClass', $diSpec);
    
    $o = $this->_factory->create('testClass');
    
    $this->assertIsA($o, 'SetterInjectionClass');
    $this->assertIsA($o->getProp1(), 'EmptyClass');
    $this->assertIsA($o->getProp2(), 'ConstructorInjectionClass');
    
    $prop2 = $o->getProp2();
    
    $this->assertEqual(123, $prop2->getProp1());
    $this->assertEqual(456, $prop2->getProp2());
  }
  
  public function testRuntimeConstructorArgInjection()
  {
    $spec = $this->_factory->newComponentSpec();
    $spec->setClassName('ConstructorInjectionClass');
    $spec->setConstructorArgs(array('foo', 'bar'));
    
    $this->_factory->setComponentSpec('test', $spec);
    
    $o = $this->_factory->create('test', array('x', 'y'));
    
    $this->assertIsA($o, 'ConstructorInjectionClass');
    $this->assertEqual('x', $o->getProp1());
    $this->assertEqual('y', $o->getProp2());
  }
  
  public function testRuntimeSetterInjection()
  {
    $spec = $this->_factory->newComponentSpec();
    $spec->setClassName('SetterInjectionClass');
    $spec->setProperty('prop1', 'foo');
    $spec->setProperty('prop2', 'bar');
    
    $this->_factory->setComponentSpec('test', $spec);
    
    $o = $this->_factory->create('test', null, array('prop1'=>'x', 'prop2'=>'y'));
    
    $this->assertIsA($o, 'SetterInjectionClass');
    $this->assertEqual('x', $o->getProp1());
    $this->assertEqual('y', $o->getProp2());
  }
  
  public function testSharedInstances()
  {
    $spec = $this->_factory->newComponentSpec();
    $spec->setClassName('stdClass');
    $spec->setShared(true);
    
    $this->_factory->setComponentSpec('test', $spec);
    
    $o1 = $this->_factory->create('test');
    $o2 = $this->_factory->create('test');
    
    $this->assertReference($o1, $o2);
  }
  
  public function testExceptionThrownForBadComponentName()
  {
    try
    {
      $o = $this->_factory->create('noSuchComponent');
      $this->fail('An exception should have been thrown because a component ' .
        'named noSuchComponent is not registered.');
    }
    catch (Crafty_ComponentFactoryException $e)
    {
      $this->pass();
    }
  }
  
  public function testSpecFinderStrategy()
  {
    $spec = $this->_factory->newComponentSpec();
    $spec->setClassName('stdClass');
    
    $finder1 = new MockSpecFinder();
    $finder1->setReturnValue('findSpecFor', null);
    $finder1->expectOnce('findSpecFor', array(
      'testComponent', new ReferenceExpectation($this->_factory)
    ));
    
    $finder2 = new MockSpecFinder();
    $finder2->setReturnValue('findSpecFor', $spec);
    $finder2->expectOnce('findSpecFor', array(
      'testComponent', new ReferenceExpectation($this->_factory)
    ));
    
    //Strategy should already have loaded $spec
    $finder3 = new MockSpecFinder();
    $finder3->setReturnValue('findSpecFor', null);
    $finder3->expectNever('findSpecFor');
    
    $this->_factory->registerSpecFinder('finder1', $finder1);
    $this->_factory->registerSpecFinder('finder2', $finder2);
    $this->_factory->registerSpecFinder('finder3', $finder3);
    
    $o = $this->_factory->create('testComponent');
    
    $this->assertIsA($o, 'stdClass');
  }

}
