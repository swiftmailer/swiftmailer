<?php

require_once dirname(__FILE__) . '/../ComponentFactory.php';
require_once dirname(__FILE__) . '/../ComponentSpecFinder.php';

/**
 * A ComponentSpecFinder which reads from a complex array.
 * @author Chris Corbyn
 * @package Swift
 * @subpackage DI
 */
class Crafty_ComponentSpecFinder_ArraySpecFinder
  implements Crafty_ComponentSpecFinder
{
  
  /**
   * The array for the specs.
   * @var array
   */
  private $_list = array();
  
  /**
   * Creates a new ArraySpecFinder with the given array.
   * @param array $list
   */
  public function __construct(array $list)
  {
    $this->_list = $list;
  }
  
  /**
   * Test if the given unflattened value is a collection or a single value.
   * @param array $input
   * @return boolean
   */
  private function _isCollection(array $input)
  {
    $size = count($input);
    $numValues = 0;
    foreach ($input as $k => $v)
    {
      if (is_array($v) && count($v) == 1
        && (isset($v['value']) || isset($v['componentRef'])))
      {
        $numValues++;
      }
    }
    return ($numValues == $size); 
  }
  
  /**
   * Flatten out all array values from the spec into their single values.
   * @param array $input
   * @param Crafty_ComponentFactory $factory
   * @return array
   */
  private function _flatten(array $input, Crafty_ComponentFactory $factory)
  {
    $ret = array();
    foreach ($input as $k => $v)
    {
      if (!is_array($v))
      {
        continue;
      }
      
      if (!$this->_isCollection($v)) //Treat item as a single value
      {
        if (count($v) == 1)
        {
          if (isset($v['componentRef']))
          {
            $ret[$k] = $factory->referenceFor($v['componentRef']);
          }
          elseif (isset($v['value']))
          {
            $ret[$k] = $v['value'];
          }
        }
      }
      else //Treat item as a collection
      {
        foreach ($v as $vk => $vv)
        {
          if (!is_array($vv) || count($vv) != 1)
          {
            continue;
          }
          
          if (isset($vv['componentRef']))
          {
            $ret[$k][$vk] = $factory->referenceFor($vv['componentRef']);
          }
          elseif (isset($vv['value']))
          {
            $ret[$k][$vk] = $vv['value'];
          }
        }
      }
    }
    return $ret;
  }
  
  /**
   * Find and create the ComponentSpec for the given $componentName.
   * Returns NULL if no ComponentSpec can be found.
   * @param string $componentName
   * @param Crafty_ComponentFactory $factory
   * @return Crafty_ComponentSpec
   */
  public function findSpecFor($componentName, Crafty_ComponentFactory $factory)
  {
    //Look for component in the array
    if (isset($this->_list[$componentName]))
    {
      $details = $this->_list[$componentName];
      
      //Cannot do anything without the className
      if (!isset($details['className']))
      {
        return null;
      }
      
      $spec = $factory->newComponentSpec();
      
      $spec->setClassName($details['className']);
      
      //Resolve all constructorArgs
      $constructorArgs = array();
      if (isset($details['constructor'])
        && is_array($details['constructor']))
      {
        $constructorArgs = $this->_flatten($details['constructor'], $factory);
      }
      $spec->setConstructorArgs($constructorArgs);
      
      //Resolve all properties
      if (isset($details['properties']) && is_array($details['properties']))
      {
        $properties = $this->_flatten($details['properties'], $factory);
        foreach ($properties as $k => $v)
        {
          $spec->setProperty($k, $v);
        }
      }
      
      //Identify component as shared or not
      $spec->setShared(!empty($details['shared']));
      
      return $spec;
    }
    
    //Fallback to no spec found
    return null;
  }
  
}
