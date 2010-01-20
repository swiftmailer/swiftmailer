<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';

class Swift_Bug118Test extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_message;
  
  public function setUp()
  {
    $this->_message = new Swift_Message();
  }
  
  public function testCallingGenerateIdChangesTheMessageId()
  {
    $currentId = $this->_message->getId();
    $this->_message->generateId();
    $newId = $this->_message->getId();

    $this->assertNotEqual($currentId, $newId);
  }
  
}
