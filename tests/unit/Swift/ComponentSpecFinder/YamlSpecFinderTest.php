<?php

require_once dirname(__FILE__) . '/../../../config.php';

require_once 'Swift/ComponentSpecFinder/AbstractSpecFinderTest.php';
require_once 'Swift/ComponentSpecFinder/YamlSpecFinder.php';
require_once 'Swift/ComponentFactory.php';

class Swift_ComponentSpecFinder_YamlSpecFinderTest
  extends Swift_ComponentSpecFinder_AbstractSpecFinderTest
{
  
  public function getFactory()
  {
    return new Swift_ComponentFactory();
  }
  
  public function getFinder()
  {
    $yaml =
    "components:\n" .
    "  empty:\n" .
    "    className: EmptyClass\n" .
    "  \n" .
    "  sharedComponent:\n" .
    "    className: stdClass\n" .
    "    shared: true\n" .
    "  \n" .
    "  setterBased:\n" .
    "    className: SetterInjectionClass\n" .
    "    properties:\n" .
    "      prop1:\n" .
    "        - { componentRef: empty }\n" .
    "        - { componentRef: sharedComponent }\n" .
    "      prop2: { value: test }\n" .
    "  \n" .
    "  constructorBased:\n" .
    "    className: ConstructorInjectionClass\n" .
    "    constructor:\n" .
    "      - { value: foo }\n" .
    "      -\n" .
    "        - { value: bar }\n" .
    "        - { value: test }\n" .
    "        - { value: 100 }\n" .
    "        - { value: 2 }\n" . 
    "        - { value: 0.5 }\n";
    
    return new Swift_ComponentSpecFinder_YamlSpecFinder($yaml);
  }
  
}
