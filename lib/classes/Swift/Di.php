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
abstract class Swift_Di
{
  
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
   * Create a new instance of the component named $name.
   * @param string $name
   * @return object
   * @throws ClassNotFoundException if no such component exists
   */
  public function createInstance($name)
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
        $instanceArgs = $this->_resolveArgs($spec['args']);
        
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
  
  // -- Private methods
  
  /**
   * Recrusively resolves dependencies in an array.
   * @param array $args
   * @return array
   * @access private
   */
  private function _resolveArgs(array $args)
  {
    $instanceArgs = array();
    foreach ($args as $i => $arg)
    {
      if (is_array($arg))
      {
        $instanceArgs[$i] = $this->_resolveArgs($arg);
      }
      else
      {
        $colonPos = strpos($arg, ':');
        $type = substr($arg, 0, $colonPos);
        $value = substr($arg, $colonPos + 1);
        switch ($type)
        {
          case 'di':
            $instanceArgs[$i] = $this->createInstance($value);
            break;
          case 'string':
            $instanceArgs[$i] = (string) $value;
            break;
          case 'int':
            $instanceArgs[$i] = (int) $value;
            break;
        }
      }
    }
    return $instanceArgs;
  }
  
  // -- Static functions
  
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
    
    $path = SWIFT_CLASS_DIRECTORY . '/' . str_replace('_', '/', $class) . '.php';
    
    if (file_exists($path))
    {
      require_once $path; //change to "require" ?
    }
  }
  
}
