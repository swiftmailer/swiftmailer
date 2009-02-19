<?php

/*
 Autoloader and dependency injector for Swift Mailer.
 
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

define('SWIFT_CLASS_DIRECTORY', dirname(__FILE__) . '/classes');
define('SWIFT_MAP_DIRECTORY', dirname(__FILE__) . '/dependency_maps');
define('SWIFT_CLASSPATH', SWIFT_CLASS_DIRECTORY);

/**
 * Swift's autoload implementation.
 * @param string $class
 */
function swift_autoload($class)
{
  if (substr($class, 0, 5) != 'Swift')
  {
    return;
  }
  
  foreach (explode(PATH_SEPARATOR, SWIFT_CLASSPATH) as $classPath)
  {
    $path = $classPath . '/' . str_replace('_', '/', $class) . '.php';
  
    if (file_exists($path))
    {
      require_once $path;
    }
  }
}

/**
 * Put Swift's autoloader at the start of the autoload stack.
 * This is needed in Zend Framework due to their poor autoloader.
 * Swift's autoloader won't process non-swift classes.
 */
function swift_autoload_register()
{
  if (!$callbacks = spl_autoload_functions())
  {
    $callbacks = array();
  }
  foreach ($callbacks as $callback)
  {
    spl_autoload_unregister($callback);
  }
  spl_autoload_register('swift_autoload');
  foreach ($callbacks as $callback)
  {
    spl_autoload_register($callback);
  }
}

swift_autoload_register();

//Load in dependency maps
require_once SWIFT_MAP_DIRECTORY . '/cache_deps.php';
require_once SWIFT_MAP_DIRECTORY . '/mime_deps.php';
require_once SWIFT_MAP_DIRECTORY . '/transport_deps.php';

//Load in global library preferences
require_once dirname(__FILE__) . '/preferences.php';
