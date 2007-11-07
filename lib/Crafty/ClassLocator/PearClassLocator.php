<?php

require_once dirname(__FILE__) . '/../ClassLocator.php';

/**
 * A ClassLocator based on Pear naming conventions.
 * @author Chris Corbyn
 * @package Swift
 * @subpackage DI
 */
class Crafty_ClassLocator_PearClassLocator implements Crafty_ClassLocator
{
  
  /**
   * The base path for searching for class files.
   * @var string
   */
  private $basePath;
  
  /**
   * Creates a new instance of PearClassLocator with the $basePath.
   * @param string $basePath
   */
  public function __construct($basePath)
  {
    $this->_basePath = $basePath;
  }
  
  /**
   * Translate a class name into it's filesystem path.
   * @param string $className
   * @return string
   */
  private function _classToPath($className)
  {
    return $this->_basePath . '/' . str_replace('_', '/', $className) . '.php';
  }
  
  /**
   * Test if a class exists with this name.
   * @param string $className
   * @return boolean
   */
  public function classExists($className)
  {
    return class_exists($className) || is_file($this->_classToPath($className));
  }
  
  /**
   * Include the class with this $className.
   * @param string $className
   */
  public function includeClass($className)
  {
    if (!class_exists($className))
    {
      require_once $this->_classToPath($className);
    }
  }
  
}
