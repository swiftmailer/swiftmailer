<?php

/*
 Dependency Injection container class Swift Mailer.
 
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
 */

//@require 'Swift/DependencyException.php';

/**
 * Dependency Injection container.
 * @package Swift
 * @author Chris Corbyn
 */
class Swift_DependencyContainer
{
  
  /** Constant for literal value types */
  const TYPE_VALUE = 0x001;
  
  /** Constant for new instance types */
  const TYPE_INSTANCE = 0x010;
  
  /** Constant for shared instance types */
  const TYPE_SHARED = 0x100;
  
  /** The data container */
  private $_store = array();
  
  /** The current endpoint in the data container */
  private $_endPoint;
  
  /**
   * Constructor should not be used.
   * Use {@link getInstance()} instead.
   */
  public function __construct() { }
  
  /**
   * Returns a singleton of the DependencyContainer.
   * @return Swift_DependencyContainer
   */
  public static function getInstance()
  {
    if (!isset(self::$_instance))
    {
      self::$_instance = new self();
    }
    return self::$_instance;
  }
  
  /**
   * Test if an item is registered in this container with the given name.
   * @param string $itemName
   * @return boolean
   * @see register()
   */
  public function has($itemName)
  {
    return array_key_exists($itemName, $this->_store)
      && isset($this->_store[$itemName]['lookupType']);
  }
  
  /**
   * Lookup the item with the given $itemName.
   * @param string $itemName
   * @return mixed
   * @throws Swift_DependencyException If the dependency is not found
   * @see register()
   */
  public function lookup($itemName)
  {
    if (!$this->has($itemName))
    {
      throw new Swift_DependencyException(
        'Cannot lookup dependency "' . $itemName . '" since it is not registered.'
        );
    }
    
    switch ($this->_store[$itemName]['lookupType'])
    {
      case self::TYPE_VALUE:
        return $this->_getValue($itemName);
      case self::TYPE_INSTANCE:
        return $this->_createNewInstance($itemName);
      case self::TYPE_SHARED:
        return $this->_createSharedInstance($itemName);
    }
  }
  
  /**
   * Register a new dependency with $itemName.
   * This method returns the current DependencyContainer instance because it
   * requires the use of the fluid interface to set the specific details for the
   * dependency.
   *
   * @param string $itemName
   * @return Swift_DependencyContainer
   * @see asNewInstanceOf(), asSharedInstanceOf(), asValue()
   */
  public function register($itemName)
  {
    $this->_store[$itemName] = array();
    $this->_endPoint =& $this->_store[$itemName];
    return $this;
  }
  
  /**
   * Specify the previously registered item as a literal value.
   * {@link register()} must be called before this will work.
   *
   * @param mixed $value
   * @return Swift_DependencyContainer
   */
  public function asValue($value)
  {
    $endPoint =& $this->_getEndPoint();
    $endPoint['lookupType'] = self::TYPE_VALUE;
    $endPoint['value'] = $value;
    return $this;
  }
  
  /**
   * Specify the previously registered item as a new instance of $className.
   * {@link register()} must be called before this will work.
   * Any arguments can be set with {@link withDependencies()},
   * {@link addConstructorValue()} or {@link addConstructorLookup()}.
   *
   * @param string $className
   * @return Swift_DependencyContainer
   * @see withDependencies(), addConstructorValue(), addConstructorLookup()
   */
  public function asNewInstanceOf($className)
  {
    $endPoint =& $this->_getEndPoint();
    $endPoint['lookupType'] = self::TYPE_INSTANCE;
    $endPoint['className'] = $className;
    return $this;
  }
  
  /**
   * Specify the previously registered item as a shared instance of $className.
   * {@link register()} must be called before this will work.
   * @param string $className
   * @return Swift_DependencyContainer
   */
  public function asSharedInstanceOf($className)
  {
    $endPoint =& $this->_getEndPoint();
    $endPoint['lookupType'] = self::TYPE_SHARED;
    $endPoint['className'] = $className;
    return $this;
  }
  
  /**
   * Specify a list of injected dependencies for the previously registered item.
   * This method takes an array of lookup names.
   * 
   * @param array $lookups
   * @return Swift_DependencyContainer
   * @see addConstructorValue(), addConstructorLookup()
   */
  public function withDependencies(array $lookups)
  {
    $endPoint =& $this->_getEndPoint();
    $endPoint['args'] = array();
    foreach ($lookups as $arg)
    {
      $endPoint['args'][] = array('type' => 'lookup', 'item' => $arg);
    }
    return $this;
  }
  
  /**
   * Specify a literal (non looked up) value for the constructor of the
   * previously registered item.
   * 
   * @param mixed $value
   * @return Swift_DependencyContainer
   * @see withDependencies(), addConstructorLookup()
   */
  public function addConstructorValue($value)
  {
    $endPoint =& $this->_getEndPoint();
    if (!isset($endPoint['args']))
    {
      $endPoint['args'] = array();
    }
    $endPoint['args'][] = array('type' => 'value', 'item' => $value);
    return $this;
  }
  
  /**
   * Specify a dependency lookup for the constructor of the previously
   * registered item.
   * 
   * @param string $lookup
   * @return Swift_DependencyContainer
   * @see withDependencies(), addConstructorValue()
   */
  public function addConstructorLookup($lookup)
  {
    $endPoint =& $this->_getEndPoint();
    if (!isset($this->_endPoint['args']))
    {
      $endPoint['args'] = array();
    }
    $endPoint['args'][] = array('type' => 'lookup', 'item' => $lookup);
    return $this;
  }
  
  // -- Private methods
  
  /** Get the literal value with $itemName */
  private function _getValue($itemName)
  {
    return $this->_store[$itemName]['value'];
  }
  
  /** Create a fresh instance of $itemName */
  private function _createNewInstance($itemName)
  {
    $reflector = new ReflectionClass($this->_store[$itemName]['className']);
    if ($reflector->getConstructor())
    {
      $args = array();
      if (isset($this->_store[$itemName]['args']))
      {
        foreach ($this->_store[$itemName]['args'] as $argDefinition)
        {
          switch ($argDefinition['type'])
          {
            case 'lookup':
              $args[] = $this->lookup($argDefinition['item']);
              break;
            case 'value':
              $args[] = $argDefinition['item'];
              break;
          }
        }
      }
      return $reflector->newInstanceArgs($args);
    }
    else
    {
      return $reflector->newInstance();
    }
  }
  
  /** Create and register a shared instance of $itemName */
  private function _createSharedInstance($itemName)
  {
    if (!isset($this->_store[$itemName]['instance']))
    {
      $this->_store[$itemName]['instance'] = $this->_createNewInstance($itemName);
    }
    return $this->_store[$itemName]['instance'];
  }
  
  /** Get the current endpoint in the store */
  private function &_getEndPoint()
  {
    if (!isset($this->_endPoint))
    {
      throw new BadMethodCallException(
        'Component must first be registered by calling register()'
        );
    }
    return $this->_endPoint;
  }
  
}
