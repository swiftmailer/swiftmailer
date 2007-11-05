<?php

require_once dirname(__FILE__) . '/ComponentFactory.php';

/**
 * A ComponentSpec finding interface when no such component is registered.
 * @author Chris Corbyn
 * @package Swift
 * @subpackage DI
 */
interface Swift_ComponentSpecFinder
{
  
  /**
   * Try to find and create a specification for $componentName.
   * Returns NULL on failure.
   * @param string $componentName
   * @param Swift_ComponentFactory The factory currently instantiated
   * @return Swift_ComponentSpec
   */
  public function findSpecFor($componentName, Swift_ComponentFactory $factory);
  
}
