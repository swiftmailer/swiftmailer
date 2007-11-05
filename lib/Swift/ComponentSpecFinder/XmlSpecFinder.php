<?php

require_once dirname(__FILE__) . '/../ComponentFactory.php';
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
   * Try create the ComponentSpec for $componentName.
   * Returns NULL on failure.
   * @param string $componentName
   * @param Swift_ComponentFactory $factory
   * @return Swift_ComponentSpec
   */
  public function findSpecFor($componentName, Swift_ComponentFactory $factory)
  {
    if ($component = array_shift($this->_xml->xpath(
      "/components/component[name='" . $componentName . "']")))
    {
      if (!$className = (string) array_shift($component->xpath("./className")))
      {
        return null;
      }
      $spec = $factory->newComponentSpec();
      
      $spec->setClassName($className);
      
      foreach ($component->xpath("./properties/property") as $property)
      {
        if ($key = (string) array_shift($property->xpath("./key")))
        {
          if ($collection = array_shift($property->xpath("./collection")))
          {
            $array = array();
            foreach ($collection->children() as $child)
            {
              switch($child->getName())
              {
                case 'value':
                  $array[] = $this->_valueOf($child);
                  break;
                case 'componentRef':
                  $array[] = $factory->referenceFor((string) $child);
                  break;
              }
            }
            $spec->setProperty($key, $array);
          }
          elseif ($value = $this->_valueOf(array_shift(
            $property->xpath("./value"))))
          {
            $spec->setProperty($key, $value);
          }
          elseif ($componentRef = (string) array_shift(
            $property->xpath("./componentRef")))
          {
            $spec->setProperty($key, $factory->referenceFor($componentRef));
          }
        }
      }
      
      $constructorArgs = array();
      
      foreach ($component->xpath("./constructor/arg") as $arg)
      {
        if ($collection = array_shift($arg->xpath("./collection")))
        {
          $array = array();
          foreach ($collection->children() as $child)
          {
            switch($child->getName())
            {
              case 'value':
                $array[] = $this->_valueOf($child);
                break;
              case 'componentRef':
                $array[] = $factory->referenceFor((string) $child);
                break;
            }
          }
          $constructorArgs[] = $array;
        }
        elseif ($value = $this->_valueOf(array_shift($arg->xpath("./value"))))
        {
          $constructorArgs[] = $value;
        }
        elseif ($componentRef = (string) array_shift(
          $arg->xpath("./componentRef")))
        {
          $constructorArgs[] = $factory->referenceFor($componentRef);
        }
      }
      
      $spec->setConstructorArgs($constructorArgs);
      
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
