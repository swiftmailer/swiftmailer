<?php

require_once dirname(__FILE__) . '/../../../config.php';

require_once 'Swift/ComponentSpecFinder/AbstractSpecFinderTest.php';
require_once 'Swift/ComponentSpecFinder/XmlSpecFinder.php';
require_once 'Swift/ComponentFactory.php';

class Swift_ComponentSpecFinder_XmlSpecFinderTest
  extends Swift_ComponentSpecFinder_AbstractSpecFinderTest
{
  
  public function getFactory()
  {
    return new Swift_ComponentFactory();
  }
  
  public function getFinder()
  {
    $xml =
    '<?xml version="1.0" ?>' .
    '<components>' .
    
    '  <component>' .
    '    <name>empty</name>' .
    '    <className>EmptyClass</className>' .
    '  </component>' .
    
    '  <component>' .
    '    <name>singletonComponent</name>' .
    '    <className>stdClass</className>' .
    '    <singleton>true</singleton>' .
    '  </component>' .
    
    '  <component>' .
    '    <name>setterBased</name>' .
    '    <className>SetterInjectionClass</className>' .
    '    <properties>' .
    '      <property>' .
    '        <key>prop1</key>' .
    '        <collection>' .
    '          <componentRef>empty</componentRef>' .
    '          <componentRef>singletonComponent</componentRef>' .
    '        </collection>' .
    '      </property>' .
    '      <property>' .
    '        <key>prop2</key>' .
    '        <value>test</value>' .
    '      </property>' .
    '    </properties>' .
    '  </component>' .
    
    '  <component>' .
    '    <name>constructorBased</name>' .
    '    <className>ConstructorInjectionClass</className>' .
    '    <constructor>' .
    '      <arg>' .
    '        <value>foo</value>' .
    '      </arg>' .
    '      <arg>' .
    '        <collection>' .
    '          <value>bar</value>' .
    '          <value>test</value>' .
    '          <value type="integer">100</value>' .
    '          <value type="int">2</value>' .
    '          <value type="float">0.5</value>' .
    '        </collection>' .
    '      </arg>' .
    '    </constructor>' .
    '  </component>' .
    
    '</components>';
    
    return new Swift_ComponentSpecFinder_XmlSpecFinder($xml);
  }
  
}
