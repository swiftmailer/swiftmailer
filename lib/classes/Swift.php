<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
