<?php

require_once dirname(__FILE__) . '/../../../config.php';

require_once 'Swift/ComponentSpecFinder/AbstractSpecFinderTest.php';
require_once 'Swift/ComponentSpecFinder/ArraySpecFinder.php';
require_once 'Swift/ComponentFactory.php';

class Swift_ComponentSpecFinder_ArraySpecFinderTest
  extends Swift_ComponentSpecFinder_AbstractSpecFinderTest
{
  
  public function getFactory()
  {
    return new Swift_ComponentFactory();
  }
  
  public function getFinder()
  {
    $list = array(
      
      'empty' => array(
        'className' => 'EmptyClass'
      ),
      
      'sharedComponent' => array(
        'className' => 'stdClass',
        'shared' => true
      ),
      
      'setterBased' => array(
        'className' => 'SetterInjectionClass',
        'properties' => array(
          //Collections are added as non-associative arrays
          'prop1' => array(
            array('componentRef' => 'empty'),
            array('componentRef' => 'sharedComponent')
          ),
          //Values are referenced by key 'value'
          'prop2' => array('value' => 'test')
        )
      ),
      
      'constructorBased' => array(
        'className' => 'ConstructorInjectionClass',
        'constructor' => array(
          //Values referenced by key 'value'
          array('value' => 'foo'),
          //Collections added as non-associative array
          array(
            array('value' => 'bar'),
            array('value' => 'test'),
            array('value' => 100),
            array('value' => 2),
            array('value' => 0.5)
          )
        )
      )
      
    );
    
    return new Swift_ComponentSpecFinder_ArraySpecFinder($list);
  }
  
}
