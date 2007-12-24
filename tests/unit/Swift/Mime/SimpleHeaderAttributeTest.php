<?php

require_once 'Swift/Mime/SimpleHeaderAttribute.php';
require_once 'Swift/Encoder.php';

Mock::generate('Swift_Encoder', 'Swift_MockEncoder');

class Swift_Mime_SimpleHeaderAttributeTest extends UnitTestCase
{

  public function testGetNameReturnsNameVerbatim()
  {
    $attribute = new Swift_Mime_SimpleHeaderAttribute('charset', 'utf-8');
    $this->assertEqual('charset', $attribute->getName());
  }
  
  public function testValueIsReturnedVerbatim()
  {
    $attribute = new Swift_Mime_SimpleHeaderAttribute('charset', 'utf-8');
    $this->assertEqual('utf-8', $attribute->getValue());
  }
  
  //TODO: Finish implementing SimpleHeaderAttribute
  
}
