<?php

/*
 Dependency Injection factory for MIME components in Swift Mailer.
 
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

require_once dirname(__FILE__) . '/Di.php';

/**
 * The factory for making classes from the MIME subpackage.
 * @package Swift
 * @author Chris Corbyn
 */
class Swift_MimeFactory extends Swift_Di
{
  
  /**
   * Singleton instance.
   * @var Swift_MimeFactory
   * @access private
   */
  private static $_instance = null;
  
  /**
   * Constructor cannot be used.
   * @access private
   */
  private function __construct()
  {
    $this->setLookup('charset', 'string:utf-8');
  }
  
  /**
   * Set the default character set for mime entities.
   * @param string $charset
   */
  public static function setCharset($charset)
  {
    self::getInstance()->setLookup('charset', 'string:' . $charset);
  }
  
  /**
   * Get an instance as a singleton.
   * @return Swift_MimeFactory
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
   * Create a dependency from the injector.
   * @param string $name
   * @return object
   */
  public static function create($name)
  {
    return self::getInstance()->createDependency($name);
  }
  
}
