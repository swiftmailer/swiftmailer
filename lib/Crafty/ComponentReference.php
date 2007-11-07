<?php

/**
 * Provides information about a component referenced within the DI container.
 * @author Chris Corbyn
 * @package Swift
 * @subpackage DI
 */
class Crafty_ComponentReference
{
  
  /**
   * The name of the component being referenced.
   * @var string
   */
  private $_componentName;
  
  /**
   * Create a new ComponentReference for $componentName.
   * @param string $componentName
   */
  public function __construct($componentName)
  {
    $this->_componentName = $componentName;
  }
  
  /**
   * Get the name of the component referenced.
   * @return string
   */
  public function getComponentName()
  {
    return $this->_componentName;
  }
  
}
