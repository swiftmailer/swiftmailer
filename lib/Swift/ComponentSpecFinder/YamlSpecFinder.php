<?php

require_once dirname(__FILE__) . '/ArraySpecFinder.php';
require_once dirname(__FILE__) . '/../../Spyc.php';

/**
 * A ComponentSpecFinder which reads from a YAML file or markup.
 * The YAML takes the form:
 *
 * components:
 *   
 *   componentName1:
 *     className: ClassA
 *     constructorArgs:
 *       - arg1
 *       - arg2
 *     properties:
 *       prop1: foo
 *       prop2: bar
 *   
 *   componentName2:
 *     className: ClassB
 *     singleton: true
 *
 * @author Chris Corbyn
 * @package Swift
 * @subpackage DI
 */
class Swift_ComponentSpecFinder_YamlSpecFinder
  extends Swift_ComponentSpecFinder_ArraySpecFinder
{
  
  /**
   * Creates a new YamlSpecFinder with the given YAML file or source.
   * @param string $yaml
   */
  public function __construct($yaml)
  {
    $array = Spyc::YAMLLoad($yaml);
    if (isset($array['components']) && is_array($array['components']))
    {
      $array = $array['components'];
    }
    else
    {
      $array = array();
    }
    parent::__construct($array);
  }
  
}
