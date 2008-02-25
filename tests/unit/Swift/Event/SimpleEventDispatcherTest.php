<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Event/SimpleEventDispatcher.php';
require_once 'Swift/Event/EventListener.php';
require_once 'Swift/Event.php';

class Swift_Event_FooEvent extends Swift_Event { }
class Swift_Event_BarEvent extends Swift_Event { }

interface Swift_Event_FooListener extends Swift_Event_EventListener {
  public function fooPerformed();
}
interface Swift_Event_BarListener extends Swift_Event_EventListener {
  public function barPerformed();
}

Mock::generate('Swift_Event', 'Swift_Event_MockFooEvent');
Mock::generate('Swift_Event', 'Swift_Event_MockBarEvent');
Mock::generate('Swift_Event_FooListener', 'Swift_Event_MockFooListener');
Mock::generate('Swift_Event_BarListener', 'Swift_Event_MockBarListener');

class Swift_Event_SimpleEventDispatcherTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testEventsCanCreatedFromObjectAliases()
  {
    $source = new stdClass();
    
    $fooEvent = new Swift_Event_MockFooEvent();
    $fooEvent->expectOnce('cloneFor', array($source));
    $fooEvent->setReturnValue('cloneFor', clone $fooEvent);
    
    $barEvent = new Swift_Event_MockBarEvent();
    $barEvent->expectOnce('cloneFor', array($source));
    $barEvent->setReturnValue('cloneFor', clone $barEvent);
    
    $dispatcher = $this->_createDispatcher(array(
      'foo' => array(
        'event' => $fooEvent,
        'listener' => 'Swift_Event_FooListener'
        ),
      'bar' => array(
        'event' => $barEvent,
        'listener' => 'Swift_Event_BarListener'
        )
      ));
    $evt1 = $dispatcher->createEvent('foo', $source, array());
    $this->assertIsA($evt1, 'Swift_Event_MockFooEvent');
    $evt2 = $dispatcher->createEvent('bar', $source, array());
    $this->assertIsA($evt2, 'Swift_Event_MockBarEvent');
  }
  
  public function testEventsCanBeCreatedFromClassAliases()
  {
    $source = new stdClass();
    
    $dispatcher = $this->_createDispatcher(array(
      'foo' => array(
        'event' => 'Swift_Event_FooEvent',
        'listener' => 'Swift_Event_FooListener'
        ),
      'bar' => array(
        'event' => 'Swift_Event_BarEvent',
        'listener' => 'Swift_Event_BarListener'
        )
      ));
    $evt1 = $dispatcher->createEvent('foo', $source, array());
    $this->assertIsA($evt1, 'Swift_Event_FooEvent');
    $evt2 = $dispatcher->createEvent('bar', $source, array());
    $this->assertIsA($evt2, 'Swift_Event_BarEvent');
  }
  
  public function testListenersCanBeAdded()
  {
    $dispatcher = $this->_createDispatcher(array(
      'foo' => array(
        'event' => 'Swift_Event_FooEvent',
        'listener' => 'Swift_Event_FooListener'
        ),
      'bar' => array(
        'event' => 'Swift_Event_BarEvent',
        'listener' => 'Swift_Event_BarListener'
        )
      ));
    $dispatcher->bindEventListener(new Swift_Event_MockFooListener());
    $dispatcher->bindEventListener(new Swift_Event_MockBarListener());
  }
  
  public function testListenersAreNotifiedOfDispatchedEvent()
  {
    $source = new stdClass();
    
    $cloneEvt = new Swift_Event_MockFooEvent();
    $fooEvent = new Swift_Event_MockFooEvent();
    $fooEvent->setReturnValue('cloneFor', $cloneEvt, array($source));
    $fooEvent->setReturnValue('getSource', $source);
    
    $dispatcher = $this->_createDispatcher(array(
      'foo' => array(
        'event' => $fooEvent,
        'listener' => 'Swift_Event_FooListener'
        )
      ));
    
    $listenerA = new Swift_Event_MockFooListener();
    $listenerA->expectOnce('fooPerformed', array($cloneEvt));
    $listenerB = new Swift_Event_MockFooListener();
    $listenerB->expectOnce('fooPerformed', array($cloneEvt));
    $listenerC = new Swift_Event_MockFooListener();
    $listenerC->expectOnce('fooPerformed', array($cloneEvt));
    
    $dispatcher->bindEventListener($listenerA);
    $dispatcher->bindEventListener($listenerB);
    $dispatcher->bindEventListener($listenerC);
    
    $evt = $dispatcher->createEvent('foo', $source, array());
    $dispatcher->dispatchEvent($evt, 'fooPerformed');
  }
  
  public function testListenersAreOnlyCalledIfImplementingCorrectInterface()
  {
    $source = new stdClass();
    
    $cloneEvt = new Swift_Event_MockFooEvent();
    $fooEvent = new Swift_Event_MockFooEvent();
    $fooEvent->setReturnValue('cloneFor', $cloneEvt, array($source));
    $fooEvent->setReturnValue('getSource', $source);
    
    $dispatcher = $this->_createDispatcher(array(
      'foo' => array(
        'event' => $fooEvent,
        'listener' => 'Swift_Event_FooListener'
        )
      ));
    
    $listenerA = new Swift_Event_MockFooListener();
    $listenerA->expectOnce('fooPerformed', array($cloneEvt));
    
    $listenerB = new Swift_Event_MockBarListener();
    $listenerB->expectNever('barPerformed');
    
    $listenerC = new Swift_Event_MockFooListener();
    $listenerC->expectOnce('fooPerformed', array($cloneEvt));
    
    $dispatcher->bindEventListener($listenerA);
    $dispatcher->bindEventListener($listenerB);
    $dispatcher->bindEventListener($listenerC);
    
    $evt = $dispatcher->createEvent('foo', $source, array());
    $dispatcher->dispatchEvent($evt, 'fooPerformed');
  }
  
  public function testListenersCanCancelBubblingOfEvent()
  {
    $source = new stdClass();
    
    $cloneEvt = new Swift_Event_MockFooEvent();
    //A
    $cloneEvt->setReturnValueAt(0, 'bubbleCancelled', false);
    //B
    $cloneEvt->setReturnValueAt(1, 'bubbleCancelled', false);
    //C
    $cloneEvt->setReturnValueAt(2, 'bubbleCancelled', true);
    
    $fooEvent = new Swift_Event_MockFooEvent();
    $fooEvent->setReturnValue('cloneFor', $cloneEvt, array($source));
    $fooEvent->setReturnValue('getSource', $source);
    
    $dispatcher = $this->_createDispatcher(array(
      'foo' => array(
        'event' => $fooEvent,
        'listener' => 'Swift_Event_FooListener'
        )
      ));
    
    $listenerA = new Swift_Event_MockFooListener();
    $listenerA->expectOnce('fooPerformed', array($cloneEvt));
    
    $listenerB = new Swift_Event_MockFooListener();
    $listenerB->expectOnce('fooPerformed', array($cloneEvt));
    
    $listenerC = new Swift_Event_MockFooListener();
    $listenerC->expectNever('fooPerformed'); //Bubble was cancelled at ListenerB
    
    $dispatcher->bindEventListener($listenerA);
    $dispatcher->bindEventListener($listenerB);
    $dispatcher->bindEventListener($listenerC);
    
    $evt = $dispatcher->createEvent('foo', $source, array());
    $dispatcher->dispatchEvent($evt, 'fooPerformed');
  }
  
  public function testListenersCanBeBoundToExplicitSource()
  {
    $source = new stdClass();
    $otherSource = new stdClass();
    
    $cloneEvt = new Swift_Event_MockFooEvent();
    $cloneEvt->setReturnValue('getSource', $source);
    
    $fooEvent = new Swift_Event_MockFooEvent();
    $fooEvent->setReturnValue('cloneFor', $cloneEvt, array($source));
    $fooEvent->setReturnValue('getSource', $source);
    
    $dispatcher = $this->_createDispatcher(array(
      'foo' => array(
        'event' => $fooEvent,
        'listener' => 'Swift_Event_FooListener'
        )
      ));
    
    $listenerA = new Swift_Event_MockFooListener();
    $listenerA->expectOnce('fooPerformed', array($cloneEvt));
    
    $listenerB = new Swift_Event_MockFooListener();
    $listenerB->expectNever('fooPerformed');
    
    $listenerC = new Swift_Event_MockFooListener();
    $listenerC->expectOnce('fooPerformed', array($cloneEvt));
    
    $dispatcher->bindEventListener($listenerA, $source);
    $dispatcher->bindEventListener($listenerB, $otherSource);
    $dispatcher->bindEventListener($listenerC, $source);
    
    $evt = $dispatcher->createEvent('foo', $source, array());
    $dispatcher->dispatchEvent($evt, 'fooPerformed');
  }
  
  // -- Private methods
  
  private function _createDispatcher(array $map)
  {
    $dispatcher = new Swift_Event_SimpleEventDispatcher($map);
    return $dispatcher;
  }
  
}
