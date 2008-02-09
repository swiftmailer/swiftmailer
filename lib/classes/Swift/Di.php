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
  
  public static $_shared = array();
  
  public static function createFromMap(array $map, $name)
  {
    if (!array_key_exists($name, $map))
    {
      throw new ClassNotFoundException(
        'Cannot create ' . $name . ' since no implemenation is registered for it'
        );
    }
    else
    {
      $spec = $map[$name];
      if ($spec['shared'] && array_key_exists($name, self::$_shared))
      {
        return self::$_shared[$name];
      }
      else
      {
        $className = $spec['class'];
        $instanceArgs = self::resolveArgs($spec['args'], $map);
        $reflector = new ReflectionClass($className);
        if ($reflector->getConstructor())
        {
          return $reflector->newInstanceArgs($instanceArgs);
        }
        else
        {
          return $reflector->newInstance();
        }
      }
    }
  }
  
  public static function resolveArgs(array $args, array $map)
  {
    $instanceArgs = array();
    foreach ($args as $i => $arg)
    {
      if (is_array($arg))
      {
        $instanceArgs[$i] = self::resolveArgs($arg, $map);
      }
      else
      {
        $colonPos = strpos($arg, ':');
        $type = substr($arg, 0, $colonPos);
        $value = substr($arg, $colonPos + 1);
        switch ($type)
        {
          case 'di':
            $instanceArgs[$i] = self::createFromMap($map, $value);
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
    
    $path = SWIFT_INTERNAL_DIRECTORY . '/' . str_replace('_', '/', $class) . '.php';
    
    if (file_exists($path))
    {
      require_once $path; //change to "require" ?
    }
  }
  
}
