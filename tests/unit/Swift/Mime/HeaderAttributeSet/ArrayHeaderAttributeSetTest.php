<?php

require_once 'Swift/Mime/HeaderAttributeSet/ArrayHeaderAttributeSet.php';
require_once 'Swift/Mime/HeaderAttribute.php';

Mock::generate('Swift_Mime_HeaderAttribute', 'Swift_Mime_MockHeaderAttribute');

class Swift_Mime_HeaderAttributeSet_ArrayHeaderAttributeSetTest
  extends UnitTestCase
{
  
  public function testAddAndGetHeaderByName()
  {
    $attribute = new Swift_Mime_MockHeaderAttribute();
    $attribute->setReturnValue('getName', 'charset');
    $attribute->setReturnValue('getValue', 'utf-8');
    
    $set = new Swift_Mime_HeaderAttributeSet_ArrayHeaderAttributeSet();
    $set->addAttribute($attribute);
    
    $testAttribute = $set->getAttributeByName('charset');
    $this->assertEqual('charset', $testAttribute->getName());
    $this->assertEqual('utf-8', $testAttribute->getValue());
  }
  
  public function testGetByNameIsCaseInsensitive()
  {
    $attribute = new Swift_Mime_MockHeaderAttribute();
    $attribute->setReturnValue('getName', 'CHARSET');
    
    $set = new Swift_Mime_HeaderAttributeSet_ArrayHeaderAttributeSet();
    $set->addAttribute($attribute);
    
    $testAttribute = $set->getAttributeByName('charset');
    $this->assertEqual('CHARSET', $testAttribute->getName());
  }
  
  public function testNullIsReturnedIfNoAttributeExists()
  {
    $set = new Swift_Mime_HeaderAttributeSet_ArrayHeaderAttributeSet();
    $this->assertNull($set->getAttributeByName('foo'));
  }
  
  public function testRemoveAttributeByReference()
  {
    $attribute = new Swift_Mime_MockHeaderAttribute();
    $attribute->setReturnValue('getName', 'charset');
    
    $set = new Swift_Mime_HeaderAttributeSet_ArrayHeaderAttributeSet();
    $set->addAttribute($attribute);
    
    $testAttribute = $set->getAttributeByName('charset');
    $this->assertEqual('charset', $testAttribute->getName());
    
    $set->removeAttribute($testAttribute);
    
    $this->assertNull($set->getAttributeByName('charset'));
  }
  
  public function testRemoveAttributeByName()
  {
    $attribute = new Swift_Mime_MockHeaderAttribute();
    $attribute->setReturnValue('getName', 'charset');
    
    $set = new Swift_Mime_HeaderAttributeSet_ArrayHeaderAttributeSet();
    $set->addAttribute($attribute);
    
    $testAttribute = $set->getAttributeByName('charset');
    $this->assertEqual('charset', $testAttribute->getName());
    
    $set->removeAttributeByName('charset');
    
    $this->assertNull($set->getAttributeByName('charset'));
  }
  
  public function testRemoveAttributeByNameIsCaseInsensitive()
  {
    $attribute = new Swift_Mime_MockHeaderAttribute();
    $attribute->setReturnValue('getName', 'charset');
    
    $set = new Swift_Mime_HeaderAttributeSet_ArrayHeaderAttributeSet();
    $set->addAttribute($attribute);
    
    $testAttribute = $set->getAttributeByName('charset');
    $this->assertEqual('charset', $testAttribute->getName());
    
    $set->removeAttributeByName('CHARSET');
    
    $this->assertNull($set->getAttributeByName('charset'));
  }
  
  public function testToArray()
  {
    $att1 = new Swift_Mime_MockHeaderAttribute();
    $att1->setReturnValue('getName', 'charset');
    
    $att2 = new Swift_Mime_MockHeaderAttribute();
    $att2->setReturnValue('getName', 'lang');
    
    $set = new Swift_Mime_HeaderAttributeSet_ArrayHeaderAttributeSet();
    $set->addAttribute($att1);
    $set->addAttribute($att2);
    
    $array = $set->toArray();
    
    $this->assertEqual(array($att1, $att2), $array);
  }
  
}
