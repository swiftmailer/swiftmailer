<?php

/**
 * Spefication container for the Dependency Injection factory to operate.
 * @author Chris Corbyn
 * @package Swift
 * @subpackage DI
 */
class Swift_ComponentSpec
{
  
  /**
   * The class name.
   * @var string
   */
  private $_className;
  
  /**
   * Arguments to be passed to the constructor.
   * @var mixed[]
   */
  private $_constructorArgs = array();
  
  /**
   * Properties of the object.
   * @var mixed[]
   */
  private $_properties = array();
  
  /**
   * True if the object should only be created once.
   * @var boolean
   */
  private $_singleton = false;
  
  /**
   * Set the class name to $className.
   * @param string $className
   */
  public function setClassName($className)
  {
    $this->_className = $className;
  }
  
  /**
   * Get the class name.
   * @return string
   */
  public function getClassName()
  {
    return $this->_className;
  }
  
  /**
   * Set the arguments to be passed into the constructor.
   * @param mixed[] Constructor arguments
   */
  public function setConstructorArgs(array $constructorArgs)
  {
    $this->_constructorArgs = $constructorArgs;
  }
  
  /**
   * Get arguments to be passed to the constructor.
   * @return mixed[]
   */
  public function getConstructorArgs()
  {
    return $this->_constructorArgs;
  }
  
  /**
   * Set the property with name $key to value $value.
   * A public setter named setPropName() is expected where $key is propName.
   * @param string $key
   * @param mixed $value
   */
  public function setProperty($key, $value)
  {
    $this->_properties[$key] = $value;
  }
  
  /**
   * Get the value of the property named $key.
   * @param string $key
   * @return mixed
   */
  public function getProperty($key)
  {
    return $this->_properties[$key];
  }
  
  /**
   * Get all properties as an associative array.
   * @return mixed[]
   */
  public function getProperties()
  {
    return $this->_properties;
  }
  
  /**
   * Make this component a singleton, or turn singleton off.
   * @param boolean $singleton
   */
  public function setSingleton($singleton)
  {
    $this->_singleton = $singleton;
  }
  
  /**
   * Returns true if this component is a singleton.
   * @return boolean
   */
  public function isSingleton()
  {
    return $this->_singleton;
  }
  
}
