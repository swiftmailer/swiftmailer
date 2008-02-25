<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Event/EventObject.php';

class Swift_Event_EventObjectTest extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_event;
  
  public function setUp()
  {
    $this->_event = new Swift_Event_EventObject();
  }
  
  public function testEventCanBeCreatedByPrototype()
  {
    $source = new stdClass();
    $evt = $this->_event->cloneFor($source);
    $this->assertIsA($evt, 'Swift_Event_EventObject');
    $this->assertReference($source, $evt->getSource());
  }
  
  public function testEventDoesNotHaveCancelledBubbleAfterClone()
  {
    $source = new stdClass();
    $evt = $this->_event->cloneFor($source);
    $this->assertFalse($evt->bubbleCancelled());
    
    $this->_event->cancelBubble(true);
    $evt = $this->_event->cloneFor($source);
    $this->assertFalse($evt->bubbleCancelled());
  }
  
  public function testBubbleCanBeCancelledInEvent()
  {
    $source = new stdClass();
    $evt = $this->_event->cloneFor($source);
    $this->assertFalse($evt->bubbleCancelled());
    $evt->cancelBubble();
    $this->assertTrue($evt->bubbleCancelled());
  }
  
}
