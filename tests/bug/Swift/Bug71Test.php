<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';

class Swift_Bug71Test extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_message;
  
  public function setUp()
  {
    $this->_message = new Swift_Message('test');
  }
  
  public function testCallingToStringAfterSettingNewBodyReflectsChanges()
  {
    $this->_message->setBody('BODY1');
    $this->assertPattern('/BODY1/', $this->_message->toString());
    
    $this->_message->setBody('BODY2');
    $this->assertPattern('/BODY2/', $this->_message->toString());
  }
  
}
