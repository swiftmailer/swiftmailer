<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/DependencyContainer.php';

class One
{
    public $arg1, $arg2;
    public function __construct($arg1 = null, $arg2 = null)
    {
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
    }
}

class Swift_DependencyContainerTest extends Swift_Tests_SwiftUnitTestCase
{
    private $_container;

    public function setUp()
    {
        $this->_container = new Swift_DependencyContainer();
    }

    public function testRegisterAndLookupValue()
    {
        $this->_container->register('foo')->asValue('bar');
        $this->assertIdentical('bar', $this->_container->lookup('foo'));
    }

    public function testHasReturnsTrueForRegisteredValue()
    {
        $this->_container->register('foo')->asValue('bar');
        $this->assertTrue($this->_container->has('foo'));
    }

    public function testHasReturnsFalseForUnregisteredValue()
    {
        $this->assertFalse($this->_container->has('foo'));
    }

    public function testRegisterAndLookupNewInstance()
    {
        $this->_container->register('one')->asNewInstanceOf('One');
        $this->assertIsA($this->_container->lookup('one'), 'One');
    }

    public function testHasReturnsTrueForRegisteredInstance()
    {
        $this->_container->register('one')->asNewInstanceOf('One');
        $this->assertTrue($this->_container->has('one'));
    }

    public function testNewInstanceIsAlwaysNew()
    {
        $this->_container->register('one')->asNewInstanceOf('One');
        $a = $this->_container->lookup('one');
        $b = $this->_container->lookup('one');
        $this->assertClone($a, $b);
    }

    public function testRegisterAndLookupSharedInstance()
    {
        $this->_container->register('one')->asSharedInstanceOf('One');
        $this->assertIsA($this->_container->lookup('one'), 'One');
    }

    public function testHasReturnsTrueForSharedInstance()
    {
        $this->_container->register('one')->asSharedInstanceOf('One');
        $this->assertTrue($this->_container->has('one'));
    }

    public function testMultipleSharedInstancesAreSameInstance()
    {
        $this->_container->register('one')->asSharedInstanceOf('One');
        $a = $this->_container->lookup('one');
        $b = $this->_container->lookup('one');
        $this->assertSame($a, $b);
    }

    public function testNewInstanceWithDependencies()
    {
        $this->_container->register('foo')->asValue('FOO');
        $this->_container->register('one')->asNewInstanceOf('One')
            ->withDependencies(array('foo'));
        $obj = $this->_container->lookup('one');
        $this->assertIdentical('FOO', $obj->arg1);
    }

    public function testNewInstanceWithMultipleDependencies()
    {
        $this->_container->register('foo')->asValue('FOO');
        $this->_container->register('bar')->asValue(42);
        $this->_container->register('one')->asNewInstanceOf('One')
            ->withDependencies(array('foo', 'bar'));
        $obj = $this->_container->lookup('one');
        $this->assertIdentical('FOO', $obj->arg1);
        $this->assertIdentical(42, $obj->arg2);
    }

    public function testNewInstanceWithInjectedObjects()
    {
        $this->_container->register('foo')->asValue('FOO');
        $this->_container->register('one')->asNewInstanceOf('One');
        $this->_container->register('two')->asNewInstanceOf('One')
            ->withDependencies(array('one', 'foo'));
        $obj = $this->_container->lookup('two');
        $this->assertClone($this->_container->lookup('one'), $obj->arg1);
        $this->assertIdentical('FOO', $obj->arg2);
    }

    public function testNewInstanceWithAddConstructorValue()
    {
        $this->_container->register('one')->asNewInstanceOf('One')
            ->addConstructorValue('x')
            ->addConstructorValue(99);
        $obj = $this->_container->lookup('one');
        $this->assertIdentical('x', $obj->arg1);
        $this->assertIdentical(99, $obj->arg2);
    }

    public function testNewInstanceWithAddConstructorLookup()
    {
        $this->_container->register('foo')->asValue('FOO');
        $this->_container->register('bar')->asValue(42);
        $this->_container->register('one')->asNewInstanceOf('One')
            ->addConstructorLookup('foo')
            ->addConstructorLookup('bar');

        $obj = $this->_container->lookup('one');
        $this->assertIdentical('FOO', $obj->arg1);
        $this->assertIdentical(42, $obj->arg2);
    }

    public function testResolvedDependenciesCanBeLookedUp()
    {
        $this->_container->register('foo')->asValue('FOO');
        $this->_container->register('one')->asNewInstanceOf('One');
        $this->_container->register('two')->asNewInstanceOf('One')
            ->withDependencies(array('one', 'foo'));
        $deps = $this->_container->createDependenciesFor('two');
        $this->assertEqual(
            array($this->_container->lookup('one'), 'FOO'), $deps
            );
    }

    public function testArrayOfDependenciesCanBeSpecified()
    {
        $this->_container->register('foo')->asValue('FOO');
        $this->_container->register('one')->asNewInstanceOf('One');
        $this->_container->register('two')->asNewInstanceOf('One')
            ->withDependencies(array(array('one', 'foo'), 'foo'));

        $obj = $this->_container->lookup('two');
        $this->assertEqual(array($this->_container->lookup('one'), 'FOO'), $obj->arg1);
        $this->assertIdentical('FOO', $obj->arg2);
    }

    public function testAliasCanBeSet()
    {
        $this->_container->register('foo')->asValue('FOO');
        $this->_container->register('bar')->asAliasOf('foo');

        $this->assertIdentical('FOO', $this->_container->lookup('bar'));
    }

    public function testAliasOfAliasCanBeSet()
    {
        $this->_container->register('foo')->asValue('FOO');
        $this->_container->register('bar')->asAliasOf('foo');
        $this->_container->register('zip')->asAliasOf('bar');
        $this->_container->register('button')->asAliasOf('zip');

        $this->assertIdentical('FOO', $this->_container->lookup('button'));
    }
}
