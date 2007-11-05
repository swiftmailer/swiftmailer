<?php

require_once dirname(__FILE__) . '/../ComponentFactory.php';
require_once dirname(__FILE__) . '/../ComponentSpecFinder.php';

/**
 * A ComponentSpecFinder which reads from a complex array.
 * @author Chris Corbyn
 * @package Swift
 * @subpackage DI
 */
class Swift_ComponentSpecFinder_ArraySpecFinder
  implements Swift_ComponentSpecFinder
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
      if (is_int($k) && is_array($v) && isset($v['value']))
      {
        $numValues++;
      }
    }
    return ($numValues == $size); 
  }
  
  /**
   * Flatten out all array values from the spec into their single values.
   * @param array $input
   * @param Swift_ComponentFactory $factory
   * @return array
   */
  private function _flatten(array $input, Swift_ComponentFactory $factory)
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
        if (!empty($v['component']))
        {
          $ret[$k] = $factory->referenceFor($v['value']);
        }
        else
        {
          $ret[$k] = $v['value'];
        }
      }
      else //Treat item as a collection
      {
        foreach ($v as $vk => $vv)
        {
          if (!is_array($vv) || !isset($vv['value']))
          {
            continue;
          }
          if (!empty($vv['component']))
          {
            $ret[$k][$vk] = $factory->referenceFor($vv['value']);
          }
          else
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
   * @param Swift_ComponentFactory $factory
   * @return Swift_ComponentSpec
   */
  public function findSpecFor($componentName, Swift_ComponentFactory $factory)
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
      if (isset($details['constructorArgs'])
        && is_array($details['constructorArgs']))
      {
        $constructorArgs = $this->_flatten($details['constructorArgs'], $factory);
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
      
      //Identify component as singleton or not
      $spec->setSingleton(!empty($details['singleton']));
      
      return $spec;
    }
    
    //Fallback to no spec found
    return null;
  }
  
}
