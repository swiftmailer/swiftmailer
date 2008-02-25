<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Events/SimpleEventDispatcher.php';
require_once 'Swift/Events/EventListener.php';
require_once 'Swift/Events/EventObject.php';

class Swift_Events_FooEvent extends Swift_Events_EventObject { }
class Swift_Events_BarEvent extends Swift_Events_EventObject { }

interface Swift_Events_FooListener extends Swift_Events_EventListener {
  public function fooPerformed();
}
interface Swift_Events_BarListener extends Swift_Events_EventListener {
  public function barPerformed();
}

Mock::generate('Swift_Events_EventObject', 'Swift_Events_MockFooEvent');
Mock::generate('Swift_Events_EventObject', 'Swift_Events_MockBarEvent');
Mock::generate('Swift_Events_FooListener', 'Swift_Events_MockFooListener');
Mock::generate('Swift_Events_BarListener', 'Swift_Events_MockBarListener');

class Swift_Events_SimpleEventDispatcherTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testEventsCanCreatedFromObjectAliases()
  {
    $source = new stdClass();
    
    $fooEvent = new Swift_Events_MockFooEvent();
    $fooEvent->expectOnce('cloneFor', array($source));
    $fooEvent->setReturnValue('cloneFor', clone $fooEvent);
    
    $barEvent = new Swift_Events_MockBarEvent();
    $barEvent->expectOnce('cloneFor', array($source));
    $barEvent->setReturnValue('cloneFor', clone $barEvent);
    
    $dispatcher = $this->_createDispatcher(array(
      'foo' => array(
        'event' => $fooEvent,
        'listener' => 'Swift_Events_FooListener'
        ),
      'bar' => array(
        'event' => $barEvent,
        'listener' => 'Swift_Events_BarListener'
        )
      ));
    $evt1 = $dispatcher->createEvent('foo', $source, array());
    $this->assertIsA($evt1, 'Swift_Events_MockFooEvent');
    $evt2 = $dispatcher->createEvent('bar', $source, array());
    $this->assertIsA($evt2, 'Swift_Events_MockBarEvent');
  }
  
  public function testEventsCanBeCreatedFromClassAliases()
  {
    $source = new stdClass();
    
    $dispatcher = $this->_createDispatcher(array(
      'foo' => array(
        'event' => 'Swift_Events_FooEvent',
        'listener' => 'Swift_Events_FooListener'
        ),
      'bar' => array(
        'event' => 'Swift_Events_BarEvent',
        'listener' => 'Swift_Events_BarListener'
        )
      ));
    $evt1 = $dispatcher->createEvent('foo', $source, array());
    $this->assertIsA($evt1, 'Swift_Events_FooEvent');
    $evt2 = $dispatcher->createEvent('bar', $source, array());
    $this->assertIsA($evt2, 'Swift_Events_BarEvent');
  }
  
  public function testListenersCanBeAdded()
  {
    $dispatcher = $this->_createDispatcher(array(
      'foo' => array(
        'event' => 'Swift_Events_FooEvent',
        'listener' => 'Swift_Events_FooListener'
        ),
      'bar' => array(
        'event' => 'Swift_Events_BarEvent',
        'listener' => 'Swift_Events_BarListener'
        )
      ));
    $dispatcher->bindEventListener(new Swift_Events_MockFooListener());
    $dispatcher->bindEventListener(new Swift_Events_MockBarListener());
  }
  
  public function testListenersAreNotifiedOfDispatchedEvent()
  {
    $source = new stdClass();
    
    $cloneEvt = new Swift_Events_MockFooEvent();
    $fooEvent = new Swift_Events_MockFooEvent();
    $fooEvent->setReturnValue('cloneFor', $cloneEvt, array($source));
    $fooEvent->setReturnValue('getSource', $source);
    
    $dispatcher = $this->_createDispatcher(array(
      'foo' => array(
        'event' => $fooEvent,
        'listener' => 'Swift_Events_FooListener'
        )
      ));
    
    $listenerA = new Swift_Events_MockFooListener();
    $listenerA->expectOnce('fooPerformed', array($cloneEvt));
    $listenerB = new Swift_Events_MockFooListener();
    $listenerB->expectOnce('fooPerformed', array($cloneEvt));
    $listenerC = new Swift_Events_MockFooListener();
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
    
    $cloneEvt = new Swift_Events_MockFooEvent();
    $fooEvent = new Swift_Events_MockFooEvent();
    $fooEvent->setReturnValue('cloneFor', $cloneEvt, array($source));
    $fooEvent->setReturnValue('getSource', $source);
    
    $dispatcher = $this->_createDispatcher(array(
      'foo' => array(
        'event' => $fooEvent,
        'listener' => 'Swift_Events_FooListener'
        )
      ));
    
    $listenerA = new Swift_Events_MockFooListener();
    $listenerA->expectOnce('fooPerformed', array($cloneEvt));
    
    $listenerB = new Swift_Events_MockBarListener();
    $listenerB->expectNever('barPerformed');
    
    $listenerC = new Swift_Events_MockFooListener();
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
    
    $cloneEvt = new Swift_Events_MockFooEvent();
    //A
    $cloneEvt->setReturnValueAt(0, 'bubbleCancelled', false);
    //B
    $cloneEvt->setReturnValueAt(1, 'bubbleCancelled', false);
    //C
    $cloneEvt->setReturnValueAt(2, 'bubbleCancelled', true);
    
    $fooEvent = new Swift_Events_MockFooEvent();
    $fooEvent->setReturnValue('cloneFor', $cloneEvt, array($source));
    $fooEvent->setReturnValue('getSource', $source);
    
    $dispatcher = $this->_createDispatcher(array(
      'foo' => array(
        'event' => $fooEvent,
        'listener' => 'Swift_Events_FooListener'
        )
      ));
    
    $listenerA = new Swift_Events_MockFooListener();
    $listenerA->expectOnce('fooPerformed', array($cloneEvt));
    
    $listenerB = new Swift_Events_MockFooListener();
    $listenerB->expectOnce('fooPerformed', array($cloneEvt));
    
    $listenerC = new Swift_Events_MockFooListener();
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
    
    $cloneEvt = new Swift_Events_MockFooEvent();
    $cloneEvt->setReturnValue('getSource', $source);
    
    $fooEvent = new Swift_Events_MockFooEvent();
    $fooEvent->setReturnValue('cloneFor', $cloneEvt, array($source));
    $fooEvent->setReturnValue('getSource', $source);
    
    $dispatcher = $this->_createDispatcher(array(
      'foo' => array(
        'event' => $fooEvent,
        'listener' => 'Swift_Events_FooListener'
        )
      ));
    
    $listenerA = new Swift_Events_MockFooListener();
    $listenerA->expectOnce('fooPerformed', array($cloneEvt));
    
    $listenerB = new Swift_Events_MockFooListener();
    $listenerB->expectNever('fooPerformed');
    
    $listenerC = new Swift_Events_MockFooListener();
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
    $dispatcher = new Swift_Events_SimpleEventDispatcher($map);
    return $dispatcher;
  }
  
}
