<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/FailoverTransport.php';
require_once 'Swift/Transport/TransportException.php';
require_once 'Swift/Transport/Log.php';
require_once 'Swift/Transport.php';

Mock::generate('Swift_Transport', 'Swift_MockTransport');
Mock::generate('Swift_Mime_Message', 'Swift_Mime_MockMessage');
Mock::generate('Swift_Transport_Log', 'Swift_Transport_MockLog');

class Swift_Transport_FailoverTransportTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testFirstTransportIsUsed()
  {
    $message1 = new Swift_Mime_MockMessage();
    $message2 = new Swift_Mime_MockMessage();
    
    $t1 = new Swift_MockTransport();
    $t1->setReturnValueAt(0, 'isStarted', false);
    $t1->setReturnValue('isStarted', true);
    $t1->expectOnce('start');
    $t1->expectAt(0, 'send', array($message1));
    $t1->setReturnValue('send', 1, array($message1));
    $t1->expectAt(1, 'send', array($message2));
    $t1->setReturnValue('send', 1, array($message2));
    $t1->expectCallCount('send', 2);
    
    $t2 = new Swift_MockTransport();
    
    $t2->expectNever('start');
    $t2->expectNever('send');
    
    $transport = $this->_getTransport(array($t1, $t2));
    $transport->start();
    $this->assertEqual(1, $transport->send($message1));
    $this->assertEqual(1, $transport->send($message2));
  }
  
  public function testMessageCanBeTriedOnNextTransportIfExceptionThrown()
  {
    $message = new Swift_Mime_MockMessage();
    
    $e = new Swift_Transport_TransportException('b0rken');
    
    $t1 = new Swift_MockTransport();
    $t1->setReturnValueAt(0, 'isStarted', false);
    $t1->setReturnValue('isStarted', true);
    $t1->expectOnce('start');
    $t1->expectOnce('send', array($message));
    $t1->throwOn('send', $e, array($message));
    
    $t2 = new Swift_MockTransport();
    
    $t2->setReturnValueAt(0, 'isStarted', false);
    $t2->setReturnValue('isStarted', true);
    $t2->expectOnce('start');
    $t2->expectOnce('send', array($message));
    $t2->setReturnValue('send', 1, array($message));
    
    $transport = $this->_getTransport(array($t1, $t2));
    $transport->start();
    
    $this->assertEqual(1, $transport->send($message));
  }
  
  public function testZeroIsReturnedIfTransportReturnsZero()
  {
    $message = new Swift_Mime_MockMessage();
    
    $t1 = new Swift_MockTransport();
    $t1->setReturnValueAt(0, 'isStarted', false);
    $t1->setReturnValue('isStarted', true);
    $t1->expectOnce('start');
    $t1->expectOnce('send', array($message));
    $t1->setReturnValue('send', 0, array($message));
    
    $t2 = new Swift_MockTransport();
    $t2->expectNever('start');
    $t2->expectNever('send');
    
    $transport = $this->_getTransport(array($t1, $t2));
    $transport->start();
    
    $this->assertEqual(0, $transport->send($message));
  }
  
  public function testTransportsWhichThrowExceptionsAreNotRetried()
  {
    $message1 = new Swift_Mime_MockMessage();
    $message2 = new Swift_Mime_MockMessage();
    $message3 = new Swift_Mime_MockMessage();
    $message4 = new Swift_Mime_MockMessage();
    
    $e = new Swift_Transport_TransportException('maur b0rken');
    
    $t1 = new Swift_MockTransport();
    $t1->setReturnValueAt(0, 'isStarted', false);
    $t1->setReturnValue('isStarted', true);
    $t1->expectOnce('start');
    $t1->expectOnce('send', array($message1));
    $t1->throwOn('send', $e, array($message1));
    
    $t2 = new Swift_MockTransport();
    $t2->setReturnValueAt(0, 'isStarted', false);
    $t2->setReturnValue('isStarted', true);
    $t2->expectOnce('start');
    $t2->expectAt(0, 'send', array($message1));
    $t2->setReturnValue('send', 1, array($message1));
    $t2->expectAt(1, 'send', array($message2));
    $t2->setReturnValue('send', 1, array($message2));
    $t2->expectAt(2, 'send', array($message3));
    $t2->setReturnValue('send', 1, array($message3));
    $t2->expectAt(3, 'send', array($message4));
    $t2->setReturnValue('send', 1, array($message4));
    $t2->expectCallCount('send', 4);
    
    $transport = $this->_getTransport(array($t1, $t2));
    $transport->start();
    
    $this->assertEqual(1, $transport->send($message1));
    $this->assertEqual(1, $transport->send($message2));
    $this->assertEqual(1, $transport->send($message3));
    $this->assertEqual(1, $transport->send($message4));
  }
  
  public function testExceptionIsThrownIfAllTransportsDie()
  {
    $message = new Swift_Mime_MockMessage();
    
    $e = new Swift_Transport_TransportException('maur b0rken');
    
    $t1 = new Swift_MockTransport();
    
    $t1->setReturnValueAt(0, 'isStarted', false);
    $t1->setReturnValue('isStarted', true);
    $t1->expectOnce('start');
    $t1->expectOnce('send', array($message));
    $t1->throwOn('send', $e, array($message));
    
    $t2 = new Swift_MockTransport();
    
    $t2->setReturnValueAt(0, 'isStarted', false);
    $t2->setReturnValue('isStarted', true);
    $t2->expectOnce('start');
    $t2->expectOnce('send', array($message));
    $t2->throwOn('send', $e, array($message));
    
    $transport = $this->_getTransport(array($t1, $t2));
    $transport->start();
    
    try
    {
      $transport->send($message);
      $this->fail('All transports failed so Exception should be thrown');
    }
    catch (Exception $e)
    {
      $this->pass();
    }
  }
  
  public function testStoppingTransportStopsAllDelegates()
  {
    $t1 = new Swift_MockTransport();
    $t1->setReturnValue('isStarted', true);
    $t1->expectOnce('stop');
    
    $t2 = new Swift_MockTransport();
    $t2->setReturnValue('isStarted', true);
    $t2->expectOnce('stop');
    
    $transport = $this->_getTransport(array($t1, $t2));
    
    $transport->start();
    $transport->stop();
  }
  
  public function testTransportShowsAsNotStartedIfAllDelegatesDead()
  {
    $message = new Swift_Mime_MockMessage();
    
    $e = new Swift_Transport_TransportException('maur b0rken');
    
    $t1 = new Swift_MockTransport();
    
    $t1->setReturnValueAt(0, 'isStarted', false);
    $t1->setReturnValue('isStarted', true);
    $t1->expectOnce('start');
    $t1->expectOnce('send', array($message));
    $t1->throwOn('send', $e, array($message));
    
    $t2 = new Swift_MockTransport();
    
    $t2->setReturnValueAt(0, 'isStarted', false);
    $t2->setReturnValue('isStarted', true);
    $t2->expectOnce('start');
    $t2->expectOnce('send', array($message));
    $t2->throwOn('send', $e, array($message));
    
    $transport = $this->_getTransport(array($t1, $t2));
    $transport->start();
    
    $this->assertTrue($transport->isStarted());
    
    try
    {
      $transport->send($message);
      $this->fail('All transports failed so Exception should be thrown');
    }
    catch (Exception $e)
    {
      $this->pass();
    }
    
    $this->assertFalse($transport->isStarted());
  }
  
  public function testRestartingTransportRestartsDeadDelegates()
  {
    $message1 = new Swift_Mime_MockMessage();
    $message2 = new Swift_Mime_MockMessage();
    
    $e = new Swift_Transport_TransportException('maur b0rken');
    
    $t1 = new Swift_MockTransport();
    
    $t1->setReturnValue('isStarted', false);
    $t1->expectCallCount('start', 2);
    $t1->expectAt(0, 'send', array(new ReferenceExpectation($message1)));
    $t1->throwOn('send', $e, array(new ReferenceExpectation($message1)));
    $t1->expectAt(1, 'send', array(new ReferenceExpectation($message2)));
    $t1->setReturnValue('send', 10, array(new ReferenceExpectation($message2)));
    $t1->expectCallCount('send', 2);
    
    $t2 = new Swift_MockTransport();
    
    $t2->setReturnValue('isStarted', false);
    $t2->expectOnce('start');
    $t2->expectOnce('send', array(new ReferenceExpectation($message1)));
    $t2->throwOn('send', $e, array(new ReferenceExpectation($message1)));
    
    $transport = $this->_getTransport(array($t1, $t2));
    $transport->start();
    
    $this->assertTrue($transport->isStarted());
    
    try
    {
      $transport->send($message1);
      $this->fail('All transports failed so Exception should be thrown');
    }
    catch (Exception $e)
    {
      $this->pass();
    }
    
    $this->assertFalse($transport->isStarted());
    
    $transport->start();
    
    $this->assertTrue($transport->isStarted());
    
    $this->assertEqual(10, $transport->send($message2));
  }
  
  // -- Private helpers
  
  private function _getTransport(array $transports)
  {
    $transport = new Swift_Transport_FailoverTransport(
      new Swift_Transport_MockLog()
      );
    $transport->setTransports($transports);
    return $transport;
  }
  
}