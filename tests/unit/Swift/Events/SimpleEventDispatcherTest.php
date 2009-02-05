<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Events/SimpleEventDispatcher.php';
require_once 'Swift/Events/EventListener.php';
require_once 'Swift/Transport.php';
require_once 'Swift/Mime/Message.php';
require_once 'Swift/TransportException.php';

class Swift_Events_SimpleEventDispatcherTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_dispatcher;
  
  public function setUp()
  {
    $this->_dispatcher = new Swift_Events_SimpleEventDispatcher();
  }
  
  public function testSendEventCanBeCreated()
  {
    $transport = $this->_stub('Swift_Transport');
    $message = $this->_stub('Swift_Mime_Message');
    $evt = $this->_dispatcher->createSendEvent($transport, $message);
    $this->assertIsA($evt, 'Swift_Events_SendEvent');
    $this->assertSame($message, $evt->getMessage());
    $this->assertSame($transport, $evt->getTransport());
  }
  
  public function testCommandEventCanBeCreated()
  {
    $buf = $this->_stub('Swift_Transport');
    $evt = $this->_dispatcher->createCommandEvent($buf, "FOO\r\n", array(250));
    $this->assertIsA($evt, 'Swift_Events_CommandEvent');
    $this->assertSame($buf, $evt->getSource());
    $this->assertEqual("FOO\r\n", $evt->getCommand());
    $this->assertEqual(array(250), $evt->getSuccessCodes());
  }
  
  public function testResponseEventCanBeCreated()
  {
    $buf = $this->_stub('Swift_Transport');
    $evt = $this->_dispatcher->createResponseEvent($buf, "250 Ok\r\n", true);
    $this->assertIsA($evt, 'Swift_Events_ResponseEvent');
    $this->assertSame($buf, $evt->getSource());
    $this->assertEqual("250 Ok\r\n", $evt->getResponse());
    $this->assertTrue($evt->isValid());
  }
  
  public function testTransportChangeEventCanBeCreated()
  {
    $transport = $this->_stub('Swift_Transport');
    $evt = $this->_dispatcher->createTransportChangeEvent($transport);
    $this->assertIsA($evt, 'Swift_Events_TransportChangeEvent');
    $this->assertSame($transport, $evt->getSource());
  }
  
  public function testTransportExceptionEventCanBeCreated()
  {
    $transport = $this->_stub('Swift_Transport');
    $ex = new Swift_TransportException('');
    $evt = $this->_dispatcher->createTransportExceptionEvent($transport, $ex);
    $this->assertIsA($evt, 'Swift_Events_TransportExceptionEvent');
    $this->assertSame($transport, $evt->getSource());
    $this->assertSame($ex, $evt->getException());
  }
  
  public function testListenersAreNotifiedOfDispatchedEvent()
  {
    $transport = $this->_stub('Swift_Transport');
    
    $evt = $this->_dispatcher->createTransportChangeEvent($transport);
    
    $listenerA = $this->_mock('Swift_Events_TransportChangeListener');
    $listenerB = $this->_mock('Swift_Events_TransportChangeListener');
    
    $this->_dispatcher->bindEventListener($listenerA);
    $this->_dispatcher->bindEventListener($listenerB);
    
    $this->_checking(Expectations::create()
      -> one($listenerA)->transportStarted($evt)
      -> one($listenerB)->transportStarted($evt)
      );
    
    $this->_dispatcher->dispatchEvent($evt, 'transportStarted');
  }
  
  public function testListenersAreOnlyCalledIfImplementingCorrectInterface()
  {
    $transport = $this->_stub('Swift_Transport');
    $message = $this->_stub('Swift_Mime_Message');
    
    $evt = $this->_dispatcher->createSendEvent($transport, $message);
    
    $targetListener = $this->_mock('Swift_Events_SendListener');
    $otherListener = $this->_mock('Swift_Events_TransportChangeListener');
    
    $this->_dispatcher->bindEventListener($targetListener);
    $this->_dispatcher->bindEventListener($otherListener);
    
    $this->_checking(Expectations::create()
      -> one($targetListener)->sendPerformed($evt)
      -> never($otherListener)
      );
    
    $this->_dispatcher->dispatchEvent($evt, 'sendPerformed');
  }
  
  public function testListenersCanCancelBubblingOfEvent()
  {
    $transport = $this->_stub('Swift_Transport');
    $message = $this->_stub('Swift_Mime_Message');
    
    $evt = $this->_dispatcher->createSendEvent($transport, $message);
    
    $listenerA = $this->_mock('Swift_Events_SendListener');
    $listenerB = $this->_mock('Swift_Events_SendListener');
    
    $this->_dispatcher->bindEventListener($listenerA);
    $this->_dispatcher->bindEventListener($listenerB);
    
    $this->_checking(Expectations::create()
      -> one($listenerA)->sendPerformed($evt) -> calls(array($this, '_cancelBubble'))
      -> never($listenerB)
      );
    
    $this->_dispatcher->dispatchEvent($evt, 'sendPerformed');
    
    $this->assertTrue($evt->bubbleCancelled());
  }
  
  public function testAddingListenerTwiceDoesNotReceiveEventTwice()
  {
    $transport = $this->_stub('Swift_Transport');
    
    $evt = $this->_dispatcher->createTransportChangeEvent($transport);
    
    $listener = $this->_mock('Swift_Events_TransportChangeListener');
    
    $this->_dispatcher->bindEventListener($listener);
    $this->_dispatcher->bindEventListener($listener);
    
    $this->_checking(Expectations::create()
      -> one($listener)->transportStarted($evt)
      -> never($listener)->transportStarted($evt)
      );
    
    $this->_dispatcher->dispatchEvent($evt, 'transportStarted');
  }
  
  // -- Mock callbacks
  
  public function _cancelBubble(Yay_Invocation $inv)
  {
    $args = $inv->getArguments();
    $args[0]->cancelBubble(true);
  }
  
  // -- Private methods
  
  private function _createDispatcher(array $map)
  {
    $dispatcher = new Swift_Events_SimpleEventDispatcher($map);
    return $dispatcher;
  }
  
}
