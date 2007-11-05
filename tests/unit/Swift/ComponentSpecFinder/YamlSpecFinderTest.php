<?php

require_once dirname(__FILE__) . '/../../../config.php';
require_once dirname(__FILE__) . '/AbstractSpecFinderTest.php';
require_once LIB_PATH . '/Swift/ComponentSpecFinder/YamlSpecFinder.php';
require_once LIB_PATH . '/Swift/ComponentFactory.php';

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
    "  singletonComponent:\n" .
    "    className: stdClass\n" .
    "    singleton: true\n" .
    "  \n" .
    "  setterBased:\n" .
    "    className: SetterInjectionClass\n" .
    "    properties:\n" .
    "      prop1:\n" .
    "        - { value: empty, component: true }\n" .
    "        - { value: singletonComponent, component: true }\n" .
    "      prop2: { value: test }\n" .
    "  \n" .
    "  constructorBased:\n" .
    "    className: ConstructorInjectionClass\n" .
    "    constructorArgs:\n" .
    "      - { value: foo }\n" .
    "      -\n" .
    "        - { value: bar }\n" .
    "        - { value: test }\n";
    return new Swift_ComponentSpecFinder_YamlSpecFinder($yaml);
  }
  
}
