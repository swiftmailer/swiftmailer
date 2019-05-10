<?php

class One
{
    public $arg1;
    public $arg2;

    public function __construct($arg1 = null, $arg2 = null)
    {
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
    }
}

class Swift_DependencyContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testRegisterAndLookupValue()
    {
        $container = Swift_DependencyContainer::getInstance();

        $container->register('foo')->asValue('bar');
        $this->assertEquals('bar', $container->lookup('foo'));
    }

    public function testHasReturnsTrueForRegisteredValue()
    {
        $container = Swift_DependencyContainer::getInstance();
        
        $container->register('foo')->asValue('bar');
        $this->assertTrue($container->has('foo'));
    }

    public function testHasReturnsFalseForUnregisteredValue()
    {
        $container = Swift_DependencyContainer::getInstance();
        
        $this->assertFalse($container->has('foo'));
    }

    public function testRegisterAndLookupNewInstance()
    {
        $container = Swift_DependencyContainer::getInstance();
        
        $container->register('one')->asNewInstanceOf('One');
        $this->assertInstanceOf('One', $container->lookup('one'));
    }

    public function testHasReturnsTrueForRegisteredInstance()
    {
        $container = Swift_DependencyContainer::getInstance();
        
        $container->register('one')->asNewInstanceOf('One');
        $this->assertTrue($container->has('one'));
    }

    public function testNewInstanceIsAlwaysNew()
    {
        $container = Swift_DependencyContainer::getInstance();
        
        $container->register('one')->asNewInstanceOf('One');
        $a = $container->lookup('one');
        $b = $container->lookup('one');
        $this->assertEquals($a, $b);
    }

    public function testRegisterAndLookupSharedInstance()
    {
        $container = Swift_DependencyContainer::getInstance();
        
        $container->register('one')->asSharedInstanceOf('One');
        $this->assertInstanceOf('One', $container->lookup('one'));
    }

    public function testHasReturnsTrueForSharedInstance()
    {
        $container = Swift_DependencyContainer::getInstance();
        
        $container->register('one')->asSharedInstanceOf('One');
        $this->assertTrue($container->has('one'));
    }

    public function testMultipleSharedInstancesAreSameInstance()
    {
        $container = Swift_DependencyContainer::getInstance();
        
        $container->register('one')->asSharedInstanceOf('One');
        $a = $container->lookup('one');
        $b = $container->lookup('one');
        $this->assertEquals($a, $b);
    }

    public function testRegisterAndLookupArray()
    {
        $container = Swift_DependencyContainer::getInstance();
        
        $container->register('One')->asArray();
        $this->assertSame([], $container->lookup('One'));
    }

    public function testNewInstanceWithDependencies()
    {
        $container = Swift_DependencyContainer::getInstance();
        
        $container->register('foo')->asValue('FOO');
        $container->register('one')->asNewInstanceOf('One')
            ->withDependencies(['foo']);
        $obj = $container->lookup('one');
        $this->assertSame('FOO', $obj->arg1);
    }

    public function testNewInstanceWithMultipleDependencies()
    {
        $container = Swift_DependencyContainer::getInstance();
        
        $container->register('foo')->asValue('FOO');
        $container->register('bar')->asValue(42);
        $container->register('one')->asNewInstanceOf('One')
            ->withDependencies(['foo', 'bar']);
        $obj = $container->lookup('one');
        $this->assertSame('FOO', $obj->arg1);
        $this->assertSame(42, $obj->arg2);
    }

    public function testNewInstanceWithInjectedObjects()
    {
        $container = Swift_DependencyContainer::getInstance();
        
        $container->register('foo')->asValue('FOO');
        $container->register('one')->asNewInstanceOf('One');
        $container->register('two')->asNewInstanceOf('One')
            ->withDependencies(['one', 'foo']);
        $obj = $container->lookup('two');
        $this->assertEquals($container->lookup('one'), $obj->arg1);
        $this->assertSame('FOO', $obj->arg2);
    }

    public function testNewInstanceWithAddConstructorValue()
    {
        $container = Swift_DependencyContainer::getInstance();
        
        $container->register('one')->asNewInstanceOf('One')
            ->addConstructorValue('x')
            ->addConstructorValue(99);
        $obj = $container->lookup('one');
        $this->assertSame('x', $obj->arg1);
        $this->assertSame(99, $obj->arg2);
    }

    public function testNewInstanceWithAddConstructorLookup()
    {
        $container = Swift_DependencyContainer::getInstance();
        
        $container->register('foo')->asValue('FOO');
        $container->register('bar')->asValue(42);
        $container->register('one')->asNewInstanceOf('One')
            ->addConstructorLookup('foo')
            ->addConstructorLookup('bar');

        $obj = $container->lookup('one');
        $this->assertSame('FOO', $obj->arg1);
        $this->assertSame(42, $obj->arg2);
    }

    public function testResolvedDependenciesCanBeLookedUp()
    {
        $container = Swift_DependencyContainer::getInstance();
        
        $container->register('foo')->asValue('FOO');
        $container->register('one')->asNewInstanceOf('One');
        $container->register('two')->asNewInstanceOf('One')
            ->withDependencies(['one', 'foo']);
        $deps = $container->createDependenciesFor('two');
        $this->assertEquals(
            [$container->lookup('one'), 'FOO'], $deps
        );
    }

    public function testArrayOfDependenciesCanBeSpecified()
    {
        $container = Swift_DependencyContainer::getInstance();
        
        $container->register('foo')->asValue('FOO');
        $container->register('one')->asNewInstanceOf('One');
        $container->register('two')->asNewInstanceOf('One')
            ->withDependencies([['one', 'foo'], 'foo']);

        $obj = $container->lookup('two');
        $this->assertEquals([$container->lookup('one'), 'FOO'], $obj->arg1);
        $this->assertSame('FOO', $obj->arg2);
    }

    public function testArrayWithDependencies()
    {
        $container = Swift_DependencyContainer::getInstance();
        
        $container->register('foo')->asValue('FOO');
        $container->register('bar')->asValue(42);
        $container->register('one')->asArray('One')
            ->withDependencies(['foo', 'bar']);
        $this->assertSame(['FOO', 42], $container->lookup('one'));
    }

    public function testAliasCanBeSet()
    {
        $container = Swift_DependencyContainer::getInstance();
        
        $container->register('foo')->asValue('FOO');
        $container->register('bar')->asAliasOf('foo');

        $this->assertSame('FOO', $container->lookup('bar'));
    }

    public function testAliasOfAliasCanBeSet()
    {
        $container = Swift_DependencyContainer::getInstance();
        
        $container->register('foo')->asValue('FOO');
        $container->register('bar')->asAliasOf('foo');
        $container->register('zip')->asAliasOf('bar');
        $container->register('button')->asAliasOf('zip');

        $this->assertSame('FOO', $container->lookup('button'));
    }
}
