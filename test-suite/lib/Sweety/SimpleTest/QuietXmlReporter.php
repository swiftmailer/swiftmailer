<?php

class Sweety_SimpleTest_QuietXmlReporter extends XmlReporter
{
  
  private $_parentPassMethod;
  
  public function __construct($namespace = false, $indent = '  ')
  {
    parent::__construct($namespace, $indent);
    $reflector = new ReflectionObject($this);
    $this->_parentPassMethod = $reflector
      ->getParentClass()
      ->getParentClass()
      ->getMethod('paintPass')
      ;
  }
  
  public function paintPass($message)
  {
    $this->_parentPassMethod->invoke($this, $message);
  }
  
}
