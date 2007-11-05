<?php

require_once dirname(__FILE__) . '/ClassLocator.php';
require_once dirname(__FILE__) . '/ComponentReference.php';
require_once dirname(__FILE__) . '/ComponentSpec.php';
require_once dirname(__FILE__) . '/ComponentSpecFinder.php';
require_once dirname(__FILE__) . '/ComponentFactoryException.php';

/**
 * A factory class for the dependency injection container.
 * Reads from specifications for components and creates configured instances
 * based upon them.
 * @author Chris Corbyn
 * @package Swift
 * @subpackage DI
 */
class Swift_ComponentFactory
{
  
  /**
   * ComponentSpec collection.
   * @var Swift_ComponentSpec[]
   */
  private $_specs = array();
  
  /**
   * ClassLocator collection.
   * @var Swift_ClassLocator[]
   */
  private $_classLocators = array();
  
  /**
   * ComponentSpecFinder collection.
   * @var Swift_ComponentSpecFinder[]
   */
  private $_specFinders = array();
  
  /**
   * Registered instances (pseudo-singletons)
   * @var mixed[]
   */
  private $_singletons = array();
  
  /**
   * Creates a new instance of the ComponentSpec class.
   * @return Swift_ComponentSpec
   */
  public function newComponentSpec()
  {
    return new Swift_ComponentSpec();
  }
  
  /**
   * Creates a new ComponentReference for the given $componentName.
   * @param string $componentName
   * @return Swift_ComponentReference
   */
  public function referenceFor($componentName)
  {
    return new Swift_ComponentReference($componentName);
  }
  
  /**
   * Sets the specification for the given $componentName.
   * @param string $componentName
   * @param Swift_ComponentSpec The specification for $componentName
   */
  public function setComponentSpec($componentName, Swift_ComponentSpec $spec)
  {
    $this->_specs[$componentName] = $spec;
  }
  
  /**
   * Gets the specification for the given $componentName.
   * @param string $componentName
   * @return Swift_ComponentSpec
   * @throws Swift_ComponentFactoryException If spec is not found
   */
  public function getComponentSpec($componentName)
  {
    if (!isset($this->_specs[$componentName]))
    {
      $spec = null;
      
      foreach ($this->_specFinders as $finder)
      {
        if ($spec = $finder->findSpecFor($componentName, $this))
        {
          $this->_specs[$componentName] = $spec;
          break;
        }
      }
      
      if (!$spec)
      {
        throw new Swift_ComponentFactoryException(
          $componentName . ' does not exist');
      }
    }
    
    return $this->_specs[$componentName];
  }
  
  /**
   * Register a new ClassLocator for finding and loading class files.
   * @param string $key
   * @param Swift_ClassLocator The ClassLocator to register
   */
  public function registerClassLocator($key, Swift_ClassLocator $locator)
  {
    $this->_classLocators[$key] = $locator;
  }
  
  /**
   * Registers a new ComponentSpec finder in this factory.
   * @param string $key
   * @param Swift_ComponentSpecFinder The spec finder instance
   */
  public function registerSpecFinder($key, Swift_ComponentSpecFinder $finder)
  {
    $this->_specFinders[$key] = $finder;
  }
  
  /**
   * Test if the given parameter is a dependency to be resolved.
   * @param mixed $input
   * @return boolean
   * @access private
   */
  private function _isDependency($input)
  {
    return ($input instanceof Swift_ComponentReference);
  }
  
  /**
   * Resolve all dependencies from ComponentReference objects into their
   * appropriate instances.
   * @param mixed $input
   * @return mixed
   * @access private
   */
  private function _resolveDependencies($input)
  {
    if (is_array($input))
    {
      $ret = array();
      foreach ($input as $value)
      {
        $ret[] = $this->_resolveDependencies($value);
      }
      return $ret;
    }
    else
    {
      if ($this->_isDependency($input))
      {
        $componentName = $input->getComponentName();
        return $this->create($componentName);
      }
      else
      {
        return $input;
      }
    }
  }
  
  /**
   * Create an instance of the given component.
   * @param string $componentName
   * @param mixed[] $constructorArgs, optional
   * @param mixed[] Associative array of properties, optional
   * @return mixed
   */
  public function create($componentName, $constructorArgs = null,
    $properties = null)
  {
    $spec = $this->getComponentSpec($componentName);
    
    //If a pseudo-singleton is used, try to return a registered instance
    // if not, reference it now
    if ($spec->isSingleton())
    {
      if (isset($this->_singletons[$componentName]))
      {
        return $this->_singletons[$componentName];
      }
      else
      {
        $o = null;
        $this->_singletons[$componentName] =& $o;
      }
    }
    
    $className = $spec->getClassName();
    
    //Load the class file
    foreach ($this->_classLocators as $locator)
    {
      if ($locator->classExists($className))
      {
        $locator->includeClass($className);
        break;
      }
    }
    
    $class = new ReflectionClass($className);
    
    //If the class has a constructor, use the constructor aguments,
    // otherwise instantiate with no arguments
    if ($class->getConstructor())
    {
      //Allow arguments to be given at runtime
      if (!is_array($constructorArgs))
      {
        $injectedArgs = $this->_resolveDependencies(
          $spec->getConstructorArgs());
      }
      else
      {
        $injectedArgs = $this->_resolveDependencies($constructorArgs);
      }
      
      $o = $class->newInstanceArgs($injectedArgs);
    }
    else
    {
      $o = $class->newInstance();
    }
    
    //Allow runtime injection of properties
    if (!is_array($properties))
    {
      $properties = $spec->getProperties();
    }
    
    //Run setter-based injection
    foreach ($properties as $key => $value)
    {
      $setter = 'set' . ucfirst($key);
      if ($class->hasMethod($setter))
      {
        $injectedValue = $this->_resolveDependencies($value);
        $class->getMethod($setter)->invoke($o, $injectedValue);
      }
    }
    
    return $o;
  }
  
}
