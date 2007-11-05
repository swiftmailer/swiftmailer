<?php

require_once dirname(__FILE__) . '/../ComponentFactory.php';
require_once dirname(__FILE__) . '/../ComponentFactoryException.php';
require_once dirname(__FILE__) . '/../ComponentSpecFinder.php';

/**
 * A ComponentSpecFinder which reads from a XML file or markup.
 * @author Chris Corbyn
 * @package Swift
 * @subpackage DI
 */
class Swift_ComponentSpecFinder_XmlSpecFinder
  implements Swift_ComponentSpecFinder
{
  
  /**
   * SimpleXMLElement instance.
   * @var SimpleXMLElement
   */
  private $_xml;
  
  /**
   * Creates a new YamlSpecFinder with the given YAML file or source.
   * @param string $yaml
   */
  public function __construct($xml)
  {
    if (is_file($xml))
    {
      $this->_xml = simplexml_load_file($xml);
    }
    else
    {
      $this->_xml = simplexml_load_string($xml);
    }
  }
  
  /**
   * Get the value of an XML node reading its type attribute, if any.
   * @param SimpleXMLElement $element
   * @return mixed
   */
  private function _valueOf(SimpleXMLElement $element)
  {
    $strValue = (string) $element;
    switch (strtolower((string) array_shift($element->xpath('./@type'))))
    {
      case 'int':
      case 'integer':
        return (int) $strValue;
        
      case 'float':
        return (float) $strValue;
      
      case 'str':
      case 'string':
      default:
        return $strValue;
    }
  }
  
  /**
   * Find component references, values, or collections in an element and set
   * into a variable $v passed by-reference.
   * Returns true if anything is set, or false if not.
   * @param SimpleXMLElement $element
   * @param Swift_ComponentFactory $factory
   * @param mixed &$v
   * @return boolean
   */
  private function _setValueByReference(SimpleXMLElement $element,
      Swift_ComponentFactory $factory, &$v)
  {
    //Element contains a collection of values
    if ($collection = array_shift($element->xpath("./collection")))
    {
      $v = array();
      foreach ($collection->children() as $child)
      {
        switch($child->getName())
        {
          case 'value':
            $v[] = $this->_valueOf($child);
            break;
          case 'componentRef':
            $v[] = $factory->referenceFor((string) $child);
            break;
        }
      }
      return true;
    }
    // Element is a single value
    elseif ($value = $this->_valueOf(array_shift(
      $element->xpath("./value"))))
    {
      $v = $value;
      return true;
    }
    //Element references another component
    elseif ($componentRef = (string) array_shift(
      $element->xpath("./componentRef")))
    {
      $v = $factory->referenceFor($componentRef);
      return true;
    }
    //Nothing found
    else
    {
      return false;
    }
  }
  
  /**
   * Try create the ComponentSpec for $componentName.
   * Returns NULL on failure.
   * @param string $componentName
   * @param Swift_ComponentFactory $factory
   * @return Swift_ComponentSpec
   */
  public function findSpecFor($componentName, Swift_ComponentFactory $factory)
  {
    //If a <component> element with this name is found
    if ($component = array_shift($this->_xml->xpath(
      "/components/component[name='" . $componentName . "']")))
    {
      //Cannot make a spec with no className
      if (!$className = (string) array_shift($component->xpath("./className")))
      {
        return null;
      }
      
      $spec = $factory->newComponentSpec();
      
      $spec->setClassName($className);
      
      //Loop over all <property> elements (possibly none)
      foreach ($component->xpath("./properties/property") as $i => $property)
      {
        if ($key = (string) array_shift($property->xpath("./key")))
        {
          //Set property key and value where possible
          if ($this->_setValueByReference($property, $factory, $valueRef))
          {
            $spec->setProperty($key, $valueRef);
          }
          else
          {
            throw new Swift_ComponentFactoryException(
              'Missing value(s) for property ' . $key . ' in component ' .
              $componentName);
          }
        }
        else
        {
          throw new Swift_ComponentFactoryException(
            'Missing <key> for property ' . $i . ' in component ' .
            $componentName);
        }
      }
      
      $constructorArgs = array();
      
      //Loop over all constructor arguments (possibly none)
      foreach ($component->xpath("./constructor/arg") as $i => $arg)
      {
        //Get value were possible
        if ($this->_setValueByReference($arg, $factory, $valueRef))
        {
          $constructorArgs[] = $valueRef;
        }
        else //Throw an Exception because it's not possible to know what to do
        {
          throw new Swift_ComponentFactoryException(
            'Failed getting value of constructor arg ' . $i . ' in component ' .
            $componentName);
        }
      }
      
      $spec->setConstructorArgs($constructorArgs);
      
      //Determine if component should be a singleton
      if ($singleton = (string) array_shift($component->xpath("./singleton")))
      {
        if (in_array(strtolower($singleton), array('true', 'yes', 'on', '1')))
        {
          $spec->setSingleton(true);
        }
      }
      
      return $spec;
    }
    else
    {
      return null;
    }
  }
  
}
