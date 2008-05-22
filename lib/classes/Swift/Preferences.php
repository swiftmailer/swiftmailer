<?php

/*
 Library Preferences class in Swift Mailer.
 
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

//@require 'Swift/DependencyContainer.php';

/**
 * Changes some global preference settings in Swift Mailer.
 * @package Swift
 * @author Chris Corbyn
 */
class Swift_Preferences
{
  
  /** Singleton instance */
  private static $_instance = null;
  
  /** Constructor not to be used */
  private function __construct() { }
  
  /**
   * Get a new instance of Preferences.
   * @return Swift_Preferences
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
   * Set the default charset used.
   * @param string
   * @return Swift_Preferences
   */
  public function setCharset($charset)
  {
    Swift_DependencyContainer::getInstance()
      ->register('properties.charset')->asValue($charset);
    return $this;
  }
  
  /**
   * Set the directory where temporary files can be saved.
   * @param string $dir
   * @return Swift_Preferences
   */
  public function setTempDir($dir)
  {
    Swift_DependencyContainer::getInstance()
      ->register('tempdir')->asValue($dir);
    return $this;
  }
  
  /**
   * Set the type of cache to use (i.e. "disk" or "array").
   * @param string $type
   * @return Swift_Preferences
   */
  public function setCacheType($type)
  {
    Swift_DependencyContainer::getInstance()
      ->register('cache')->asAliasOf(sprintf('cache.%s', $type));
    return $this;
  }
  
}
