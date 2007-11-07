<?php

class HybridInjectionClass
{
  
  private $_prop1;
  
  private $_prop2;
  
  private $_prop3;
  
  public function __construct($prop1, $prop2 = null)
  {
    $this->_prop1 = $prop1;
    $this->_prop2 = $prop2;
  }
  
  public function getProp1()
  {
    return $this->_prop1;
  }
  
  public function getProp2()
  {
    return $this->_prop2;
  }
  
  public function setProp3($prop3)
  {
    return $this->_prop3 = $prop3;
  }
  
  public function getProp3()
  {
    return $this->_prop3;
  }
  
}
