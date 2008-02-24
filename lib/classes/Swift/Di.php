<?php

/*
 Dependency Injector in Swift Mailer.
 
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


/**
 * The dependency injector.
 * @package Swift
 * @author Chris Corbyn
 */
class Swift_Di
{
  
  /**
   * Paths to scan for class files.
   * @var array
   * @access private
   */
  private static $_classPaths = array();
  
  /**
   * Shared instance collection.
   * @var object[]
   * @access private
   */
  private $_shared = array();
  
  /**
   * The dependency map.
   * @var array
   * @access private
   */
  private $_map = array();
  
  /**
   * Lookups for getting values at runtime, rather than straight from the map.
   * @var array
   * @access private
   */
  private $_lookups = array();
  
  private static $_singleton = null;
  
  /**
   * Create a new instance of the component named $name.
   * @param string $name
   * @param array $lookup to override any pre-defined lookups
   * @return object
   * @throws ClassNotFoundException if no such component exists
   */
  public function create($name, $lookup = array())
  {
    if (!array_key_exists($name, $this->_map))
    {
      throw new ClassNotFoundException(
        'Cannot create ' . $name . ' since no implemenation is registered for it'
        );
    }
    else
    {
      //Find the details of this dependency
      $spec = $this->_map[$name];
      if ($spec['shared'] && array_key_exists($name, $this->_shared))
      {
        //Return a shared instance if one exists
        return $this->_shared[$name];
      }
      else
      {
        //Otherwise, create it through reflection
        $className = $spec['class'];
        
        //Resolve dependencies within the constructor itself
        $instanceArgs = $this->_resolveArgs($spec['args'], $lookup);
        
        $reflector = new ReflectionClass($className);
        if ($reflector->getConstructor())
        {
          $instance = $reflector->newInstanceArgs($instanceArgs);
        }
        else
        {
          $instance = $reflector->newInstance();
        }
        
        //Register a shared instance where needed
        if ($spec['shared'])
        {
          $this->_shared[$name] = $instance;
        }
        
        return $instance;
      }
    }
  }
  
  /**
   * Merge the provided dependency map onto the existing one.
   * @param array $map
   */
  public function registerDependencyMap(array $map)
  {
    $this->_map = array_merge($this->_map, $map);
  }
  
  /**
   * Get the dependency map in its entirety.
   * @return array
   */
  public function getDependencyMap()
  {
    return $this->_map;
  }
  
  /**
   * Set a lookup reference.
   * @param string $name
   * @param mixed $value
   */
  public function setLookup($name, $value)
  {
    $this->_lookups[$name] = $value;
  }
  
  /**
   * Lookup a dependency at runtime.
   * @param string $name
   * @return mixed
   */
  public function lookup($name)
  {
    if (array_key_exists($name, $this->_lookups))
    {
      return $this->_lookups[$name];
    }
  }
  
  // -- Private methods
  
  /**
   * Recrusively resolves dependencies in an array.
   * @param array $args
   * @param array $lookup to override any pre-defined lookups
   * @return array
   * @access private
   */
  private function _resolveArgs(array $args, $lookup = array())
  {
    $instanceArgs = array();
    foreach ($args as $i => $arg)
    {
      if (is_array($arg))
      {
        $instanceArgs[$i] = $this->_resolveArgs($arg, $lookup);
      }
      elseif (is_object($arg))
      {
        $instanceArgs[$i] = $arg;
      }
      else
      {
        do
        {
          list($type, $value) = sscanf($arg, '%[^:]:%s');
          switch ($type)
          {
            case 'di':
              $instanceArgs[$i] = $this->create($value, $lookup);
              break;
            case 'lookup': //Value is looked up at runtime
              $arg = array_key_exists($value, $lookup)
                ? $lookup[$value]
                : $this->lookup($value);
              break;
            case 'string':
              $instanceArgs[$i] = (string) $value;
              break;
            case 'int':
              $instanceArgs[$i] = (int) $value;
              break;
            case 'null':
              $instanceArgs[$i] = null;
              break;
          }
        }
        while ('lookup' == $type);
      }
    }
    return $instanceArgs;
  }
  
  // -- Static functions
  
  public static function getInstance()
  {
    if (!isset(self::$_singleton))
    {
      self::$_singleton = new self();
    }
    return self::$_singleton;
  }
  
  /**
   * Require classes which are not loaded.
   * This must be registered with spl_autoload_register().
   * @param string $class
   */
  public static function autoload($class)
  {
    if (substr($class, 0, 5) != 'Swift')
    {
      return;
    }
    
    foreach (self::$_classPaths as $classPath)
    {
      $path = $classPath . '/' . str_replace('_', '/', $class) . '.php';
    
      if (file_exists($path))
      {
        require_once $path; //change to "require" ?
      }
    }
  }
  
  /**
   * Set the path to class files, separated by PATH_SEPARATOR.
   * @param string $classPath
   */
  public static function setClassPath($classPath)
  {
    self::$_classPaths = explode(PATH_SEPARATOR, $classPath);
  }
  
}
