<?php

/**
 * Reflection class which knows about setter based injection depedencies.
 * @package Crafty
 * @author Chris Corbyn
 */
class Crafty_ComponentReflector extends ReflectionClass
{
  
  /**
   * Properties to be injected when instantiated.
   * @var mixed[]
   */
  private $_properties = array();
  
  /**
   * Create a new ComponentReflector for the given $className with the given
   * $properties.
   * @param string $className
   * @param mixed[] $properties
   */
  public function __construct($className, $properties = array())
  {
    parent::__construct($className);
    $this->_properties = $properties;
  }
  
  /**
   * Create an instance with a list of arguments passed to the constructor.
   * @param mixed $param1
   * @param mixed $param2...
   * @return object
   */
  public function newInstance()
  {
    return $this->newInstanceArgs(func_get_args());
  }
  
  /**
   * Create an instance with the given array of arguments in the constructor.
   * @param mixed[] $args
   * @return object
   */
  public function newInstanceArgs(array $args)
  {
    if ($this->getConstructor())
    {
      $o = parent::newInstanceArgs($args);
    }
    else
    {
      $o = parent::newInstance();
    }
    
    foreach ($this->_properties as $k => $v)
    {
      $setter = 'set' . ucfirst($k);
      $this->getMethod($setter)->invoke($o, $v);
    }
    
    return $o;
  }
  
}
