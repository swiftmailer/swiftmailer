<?php

/*
 General utility class from Swift Mailer.

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
 * General utility class in Swift Mailer, not to be instantiated.
 * 
 * @package Swift
 * 
 * @author Chris Corbyn
 */
abstract class Swift
{
  
  /** Swift Mailer Version number generated during dist release process */
  const VERSION = '@SWIFT_VERSION_NUMBER@';
  
  /**
   * Internal autoloader for spl_autoload_register().
   * 
   * @param string $class
   */
  public static function autoload($class)
  {
    //Don't interfere with other autoloaders
    if (substr($class, 0, strlen(__CLASS__)) != __CLASS__)
    {
      return;
    }
    
    $path = SWIFT_CLASS_DIRECTORY . '/' . str_replace('_', '/', $class) . '.php';
    
    if (file_exists($path))
    {
      require_once $path;                                                        
    }
  }
  
  /**
   * Configure autoloading using Swift Mailer.
   * 
   * This is designed to play nicely with other autoloaders.
   */
  public static function registerAutoload()
  {
    if (!$callbacks = spl_autoload_functions())
    {
      $callbacks = array();
    }
    foreach ($callbacks as $callback)
    {
      spl_autoload_unregister($callback);
    }
    spl_autoload_register(array('Swift', 'autoload'));
    foreach ($callbacks as $callback)
    {
      spl_autoload_register($callback);
    }
  }
  
}
