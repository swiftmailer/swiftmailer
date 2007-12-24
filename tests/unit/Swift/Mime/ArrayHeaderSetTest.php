<?php

require_once 'Swift/Mime/ArrayHeaderSet.php';
require_once 'Swift/Mime/Header.php';

Mock::generate('Swift_Mime_Header', 'Swift_Mime_MockHeader');

class Swift_Mime_ArrayHeaderSetTest extends UnitTestCase
{
  
  public function testAddAndGetHeaderByName()
  {
    $header = new Swift_Mime_MockHeader();
    $header->setReturnValue('getName', 'Subject');
    $header->setReturnValue('getValue', 'Test');
    
    $set = new Swift_Mime_ArrayHeaderSet();
    $set->addHeader($header);
    
    $testHeader = $set->getHeaderByName('Subject');
    $this->assertEqual('Subject', $testHeader->getName());
    $this->assertEqual('Test', $testHeader->getValue());
  }
  
  public function testGetByNameIsCaseInsensitive()
  {
    $header = new Swift_Mime_MockHeader();
    $header->setReturnValue('getName', 'SUBJECT');
    
    $set = new Swift_Mime_ArrayHeaderSet();
    $set->addHeader($header);
    
    $testHeader = $set->getHeaderByName('Subject');
    $this->assertEqual('SUBJECT', $testHeader->getName());
  }
  
  public function testNullIsReturnedIfNoHeaderExists()
  {
    $set = new Swift_Mime_ArrayHeaderSet();
    $this->assertNull($set->getHeaderByName('foo'));
  }
  
  public function testRemoveHeaderByReference()
  {
    $header = new Swift_Mime_MockHeader();
    $header->setReturnValue('getName', 'Subject');
    
    $set = new Swift_Mime_ArrayHeaderSet();
    $set->addHeader($header);
    
    $testHeader = $set->getHeaderByName('Subject');
    $this->assertEqual('Subject', $testHeader->getName());
    
    $set->removeHeader($testHeader);
    
    $this->assertNull($set->getHeaderByName('Subject'));
  }
  
  public function testRemoveHeaderByName()
  {
    $header = new Swift_Mime_MockHeader();
    $header->setReturnValue('getName', 'Subject');
    
    $set = new Swift_Mime_ArrayHeaderSet();
    $set->addHeader($header);
    
    $testHeader = $set->getHeaderByName('Subject');
    $this->assertEqual('Subject', $testHeader->getName());
    
    $set->removeHeaderByName('Subject');
    
    $this->assertNull($set->getHeaderByName('Subject'));
  }
  
  public function testRemoveHeaderByNameIsCaseInsensitive()
  {
    $header = new Swift_Mime_MockHeader();
    $header->setReturnValue('getName', 'Subject');
    
    $set = new Swift_Mime_ArrayHeaderSet();
    $set->addHeader($header);
    
    $testHeader = $set->getHeaderByName('Subject');
    $this->assertEqual('Subject', $testHeader->getName());
    
    $set->removeHeaderByName('SUBJECT');
    
    $this->assertNull($set->getHeaderByName('Subject'));
  }
  
  public function testToArray()
  {
    $h1 = new Swift_Mime_MockHeader();
    $h1->setReturnValue('getName', 'Subject');
    
    $h2 = new Swift_Mime_MockHeader();
    $h2->setReturnValue('getName', 'From');
    
    $set = new Swift_Mime_ArrayHeaderSet();
    $set->addHeader($h1);
    $set->addHeader($h2);
    
    $array = $set->toArray();
    
    $this->assertEqual(array($h1, $h2), $array);
  }
  
}
