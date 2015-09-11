<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/MappedTransport.php';
require_once 'Swift/TransportException.php';
require_once 'Swift/Transport.php';
require_once 'Swift/Events/EventListener.php';

class Swift_Transport_MappedTransportTest
    extends Swift_Tests_SwiftUnitTestCase
{
    public function testDefaultTransport()
    {
        $context = new Mockery();
        $message1 = $context->mock('Swift_Mime_Message');
        $message2 = $context->mock('Swift_Mime_Message');
        $t1 = $context->mock('Swift_Transport');
        $t2 = $context->mock('Swift_Transport');
        $con1 = $context->states('Connection 1')->startsAs('off');
        $con2 = $context->states('Connection 2')->startsAs('off');
        $context->checking(Expectations::create()
            -> one($message1)->getFrom()->returns(array('foo@bar.com'=>'Name'))
            -> one($message2)->getFrom()->returns(array('foo@bar.com'=>'Name'))
            -> allowing($t1)->isStarted() -> returns(false) -> when($con1->is('off'))
            -> allowing($t1)->isStarted() -> returns(true) -> when($con1->is('on'))
            -> allowing($t1)->start() -> when($con1->is('off')) -> then($con1->is('on'))
            -> one($t1)->send($message1, optional()) -> returns(1) -> when($con1->is('on'))
            -> one($t1)->send($message2, optional()) -> returns(1) -> when($con1->is('on'))
            -> ignoring($t1)
            -> allowing($t2)->isStarted() -> returns(false) -> when($con2->is('off'))
            -> allowing($t2)->isStarted() -> returns(true) -> when($con2->is('on'))
            -> allowing($t2)->start() -> when($con2->is('off')) -> then($con2->is('on'))
            -> never($t2)->send($message1, optional())
            -> never($t2)->send($message2, optional())
            -> ignoring($t2)
            );

        $transport = $this->_getTransport(array('t1' => $t1, 't2' => $t2), 't1');
        $transport->setMappings('t2', array(array('getFrom' => 'foobar@bar.com')));
        $transport->start();
        $this->assertEqual(1, $transport->send($message1));
        $this->assertEqual(1, $transport->send($message2));

        $context->assertIsSatisfied();
    }

    public function testGetFromMapping()
    {
        $context = new Mockery();
        $message1 = $context->mock('Swift_Mime_Message');
        $message2 = $context->mock('Swift_Mime_Message');
        $t1 = $context->mock('Swift_Transport');
        $t2 = $context->mock('Swift_Transport');
        $con1 = $context->states('Connection 1')->startsAs('off');
        $con2 = $context->states('Connection 2')->startsAs('off');
        $context->checking(Expectations::create()
            -> one($message1)->getFrom()->returns(array('foo@bar.com'=>'Name'))
            -> one($message2)->getFrom()->returns(array('foobar@bar.com'=>'Name'))
            -> allowing($t1)->isStarted() -> returns(false) -> when($con1->is('off'))
            -> allowing($t1)->isStarted() -> returns(true) -> when($con1->is('on'))
            -> allowing($t1)->start() -> when($con1->is('off')) -> then($con1->is('on'))
            -> one($t1)->send($message1, optional()) -> returns(1) -> when($con1->is('on'))
            -> never($t1)->send($message2, optional())
            -> ignoring($t1)
            -> allowing($t2)->isStarted() -> returns(false) -> when($con2->is('off'))
            -> allowing($t2)->isStarted() -> returns(true) -> when($con2->is('on'))
            -> allowing($t2)->start() -> when($con2->is('off')) -> then($con2->is('on'))
            -> one($t2)->send($message2, optional()) -> returns(1) -> when($con2->is('on'))
            -> never($t2)->send($message1, optional())
            -> ignoring($t2)
            );

        $transport = $this->_getTransport(array('t1' => $t1, 't2' => $t2), 't1');
        $transport->setMappings('t2', array(array('getFrom' => 'foobar@bar.com')));
        $transport->start();
        $this->assertEqual(1, $transport->send($message1));
        $this->assertEqual(1, $transport->send($message2));

        $context->assertIsSatisfied();
    }
    
    public function testGetFromCaseInsensitiveMapping()
    {
        $context = new Mockery();
        $message1 = $context->mock('Swift_Mime_Message');
        $message2 = $context->mock('Swift_Mime_Message');
        $t1 = $context->mock('Swift_Transport');
        $t2 = $context->mock('Swift_Transport');
        $con1 = $context->states('Connection 1')->startsAs('off');
        $con2 = $context->states('Connection 2')->startsAs('off');
        $context->checking(Expectations::create()
            -> one($message1)->getFrom()->returns(array('foo@bar.com'=>'Name'))
            -> one($message2)->getFrom()->returns(array('FoObaR@bar.COM'=>'Name'))
            -> allowing($t1)->isStarted() -> returns(false) -> when($con1->is('off'))
            -> allowing($t1)->isStarted() -> returns(true) -> when($con1->is('on'))
            -> allowing($t1)->start() -> when($con1->is('off')) -> then($con1->is('on'))
            -> one($t1)->send($message1, optional()) -> returns(1) -> when($con1->is('on'))
            -> never($t1)->send($message2, optional())
            -> ignoring($t1)
            -> allowing($t2)->isStarted() -> returns(false) -> when($con2->is('off'))
            -> allowing($t2)->isStarted() -> returns(true) -> when($con2->is('on'))
            -> allowing($t2)->start() -> when($con2->is('off')) -> then($con2->is('on'))
            -> one($t2)->send($message2, optional()) -> returns(1) -> when($con2->is('on'))
            -> never($t2)->send($message1, optional())
            -> ignoring($t2)
            );

        $transport = $this->_getTransport(array('t1' => $t1, 't2' => $t2), 't1');
        $transport->setMappings('t2', array(array('getFrom' => 'foobar@bar.com')));
        $transport->start();
        $this->assertEqual(1, $transport->send($message1));
        $this->assertEqual(1, $transport->send($message2));

        $context->assertIsSatisfied();
    }

    public function testGetFromRegExMapping()
    {
        $context = new Mockery();
        $message1 = $context->mock('Swift_Mime_Message');
        $message2 = $context->mock('Swift_Mime_Message');
        $t1 = $context->mock('Swift_Transport');
        $t2 = $context->mock('Swift_Transport');
        $con1 = $context->states('Connection 1')->startsAs('off');
        $con2 = $context->states('Connection 2')->startsAs('off');
        $context->checking(Expectations::create()
            -> one($message1)->getFrom()->returns(array('foo@bar.com'=>'Name'))
            -> one($message2)->getFrom()->returns(array('foobar@bar.com'=>'Name'))
            -> allowing($t1)->isStarted() -> returns(false) -> when($con1->is('off'))
            -> allowing($t1)->isStarted() -> returns(true) -> when($con1->is('on'))
            -> allowing($t1)->start() -> when($con1->is('off')) -> then($con1->is('on'))
            -> one($t1)->send($message1, optional()) -> returns(1) -> when($con1->is('on'))
            -> never($t1)->send($message2, optional())
            -> ignoring($t1)
            -> allowing($t2)->isStarted() -> returns(false) -> when($con2->is('off'))
            -> allowing($t2)->isStarted() -> returns(true) -> when($con2->is('on'))
            -> allowing($t2)->start() -> when($con2->is('off')) -> then($con2->is('on'))
            -> one($t2)->send($message2, optional()) -> returns(1) -> when($con2->is('on'))
            -> never($t2)->send($message1, optional())
            -> ignoring($t2)
            );

        $transport = $this->_getTransport(array('t1' => $t1, 't2' => $t2), 't1');
        $transport->setMappings('t2', array(array('getFrom' => '/^foobar@.*$/')));
        $transport->start();
        $this->assertEqual(1, $transport->send($message1));
        $this->assertEqual(1, $transport->send($message2));

        $context->assertIsSatisfied();
    }
    
    public function testGetToMapping()
    {
        $context = new Mockery();
        $message1 = $context->mock('Swift_Mime_Message');
        $message2 = $context->mock('Swift_Mime_Message');
        $t1 = $context->mock('Swift_Transport');
        $t2 = $context->mock('Swift_Transport');
        $con1 = $context->states('Connection 1')->startsAs('off');
        $con2 = $context->states('Connection 2')->startsAs('off');
        $context->checking(Expectations::create()
            -> one($message1)->getTo()->returns(array('foo@bar.com'=>'Name'))
            -> one($message2)->getTo()->returns(array('foobar@bar.com'=>'Name'))
            -> allowing($t1)->isStarted() -> returns(false) -> when($con1->is('off'))
            -> allowing($t1)->isStarted() -> returns(true) -> when($con1->is('on'))
            -> allowing($t1)->start() -> when($con1->is('off')) -> then($con1->is('on'))
            -> one($t1)->send($message1, optional()) -> returns(1) -> when($con1->is('on'))
            -> never($t1)->send($message2, optional())
            -> ignoring($t1)
            -> allowing($t2)->isStarted() -> returns(false) -> when($con2->is('off'))
            -> allowing($t2)->isStarted() -> returns(true) -> when($con2->is('on'))
            -> allowing($t2)->start() -> when($con2->is('off')) -> then($con2->is('on'))
            -> one($t2)->send($message2, optional()) -> returns(1) -> when($con2->is('on'))
            -> never($t2)->send($message1, optional())
            -> ignoring($t2)
            );

        $transport = $this->_getTransport(array('t1' => $t1, 't2' => $t2), 't1');
        $transport->setMappings('t2', array(array('getTo' => 'foobar@bar.com')));
        $transport->start();
        $this->assertEqual(1, $transport->send($message1));
        $this->assertEqual(1, $transport->send($message2));

        $context->assertIsSatisfied();
    }
    
    public function testGetSubjectMapping()
    {
        $context = new Mockery();
        $message1 = $context->mock('Swift_Mime_Message');
        $message2 = $context->mock('Swift_Mime_Message');
        $t1 = $context->mock('Swift_Transport');
        $t2 = $context->mock('Swift_Transport');
        $con1 = $context->states('Connection 1')->startsAs('off');
        $con2 = $context->states('Connection 2')->startsAs('off');
        $context->checking(Expectations::create()
            -> one($message1)->getSubject()->returns('notfoobar')
            -> one($message2)->getSubject()->returns('foobar')
            -> allowing($t1)->isStarted() -> returns(false) -> when($con1->is('off'))
            -> allowing($t1)->isStarted() -> returns(true) -> when($con1->is('on'))
            -> allowing($t1)->start() -> when($con1->is('off')) -> then($con1->is('on'))
            -> one($t1)->send($message1, optional()) -> returns(1) -> when($con1->is('on'))
            -> never($t1)->send($message2, optional())
            -> ignoring($t1)
            -> allowing($t2)->isStarted() -> returns(false) -> when($con2->is('off'))
            -> allowing($t2)->isStarted() -> returns(true) -> when($con2->is('on'))
            -> allowing($t2)->start() -> when($con2->is('off')) -> then($con2->is('on'))
            -> one($t2)->send($message2, optional()) -> returns(1) -> when($con2->is('on'))
            -> never($t2)->send($message1, optional())
            -> ignoring($t2)
            );

        $transport = $this->_getTransport(array('t1' => $t1, 't2' => $t2), 't1');
        $transport->setMappings('t2', array(array('getSubject' => 'foobar')));
        $transport->start();
        $this->assertEqual(1, $transport->send($message1));
        $this->assertEqual(1, $transport->send($message2));

        $context->assertIsSatisfied();
    }

    public function testGetHeaderMapping()
    {
        $context = new Mockery();
        $message1 = $context->mock('Swift_Mime_Message');
        $headers1 = $context->mock('Swift_Mime_SimpleHeaderSet');
        $headerValue1 = $context->mock('Swift_Mime_Headers_UnstructuredHeader');
        $message2 = $context->mock('Swift_Mime_Message');
        $headers2 = $context->mock('Swift_Mime_SimpleHeaderSet');
        $headerValue2 = $context->mock('Swift_Mime_Headers_UnstructuredHeader');
        $t1 = $context->mock('Swift_Transport');
        $t2 = $context->mock('Swift_Transport');
        $con1 = $context->states('Connection 1')->startsAs('off');
        $con2 = $context->states('Connection 2')->startsAs('off');
        $context->checking(Expectations::create()
            -> one($headerValue1)->getValue()->returns('notfoobar')
            -> one($headers1)->getAll()->returns(array($headerValue1))
            -> one($message1)->getHeaders()->returns($headers1)
            -> allowing($t1)->isStarted() -> returns(false) -> when($con1->is('off'))
            -> allowing($t1)->isStarted() -> returns(true) -> when($con1->is('on'))
            -> allowing($t1)->start() -> when($con1->is('off')) -> then($con1->is('on'))
            -> one($t1)->send($message1, optional()) -> returns(1) -> when($con1->is('on'))
            -> never($t1)->send($message2, optional())
            -> ignoring($t1)
            -> one($headerValue2)->getValue()->returns('foobar')
            -> one($headers2)->getAll()->returns(array($headerValue2))
            -> one($message2)->getHeaders()->returns($headers2)
            -> allowing($t2)->isStarted() -> returns(false) -> when($con2->is('off'))
            -> allowing($t2)->isStarted() -> returns(true) -> when($con2->is('on'))
            -> allowing($t2)->start() -> when($con2->is('off')) -> then($con2->is('on'))
            -> one($t2)->send($message2, optional()) -> returns(1) -> when($con2->is('on'))
            -> never($t2)->send($message1, optional())
            -> ignoring($t2)
            );

        $transport = $this->_getTransport(array('t1' => $t1, 't2' => $t2), 't1');
        $transport->setMappings('t2', array(array('getHeaders' => array('X-MappedTransport' => 'foobar'))));
        $transport->start();
        $this->assertEqual(1, $transport->send($message1));
        $this->assertEqual(1, $transport->send($message2));

        $context->assertIsSatisfied();
    }

    public function testComplexMapping()
    {
        $context = new Mockery();
        
        $message1 = $context->mock('Swift_Mime_Message');
        $headers1 = $context->mock('Swift_Mime_SimpleHeaderSet');
        $headerValue1 = $context->mock('Swift_Mime_Headers_UnstructuredHeader');
        
        $message2 = $context->mock('Swift_Mime_Message');
        $headers2 = $context->mock('Swift_Mime_SimpleHeaderSet');
        $headerValue2 = $context->mock('Swift_Mime_Headers_UnstructuredHeader');
        
        $message3 = $context->mock('Swift_Mime_Message');
        $headers3 = $context->mock('Swift_Mime_SimpleHeaderSet');
        $headerValue3 = $context->mock('Swift_Mime_Headers_UnstructuredHeader');
        
        $t1 = $context->mock('Swift_Transport');
        $t2 = $context->mock('Swift_Transport');
        $t3 = $context->mock('Swift_Transport');
        
        $con1 = $context->states('Connection 1')->startsAs('off');
        $con2 = $context->states('Connection 2')->startsAs('off');
        $con3 = $context->states('Connection 3')->startsAs('off');
        
        $context->checking(Expectations::create()
            -> allowing($headerValue1)->getValue()->returns('t1')
            -> allowing($headers1)->getAll()->returns(array($headerValue1))
            -> allowing($message1)->getHeaders()->returns($headers1)
            -> allowing($message1)->getFrom()->returns('a')
            -> allowing($t1)->isStarted() -> returns(false) -> when($con1->is('off'))
            -> allowing($t1)->isStarted() -> returns(true) -> when($con1->is('on'))
            -> allowing($t1)->start() -> when($con1->is('off')) -> then($con1->is('on'))
            -> allowing($t1)->send($message1, optional()) -> returns(1) -> when($con1->is('on'))
            -> never($t1)->send($message2, optional())
            -> ignoring($t1)
            -> allowing($headerValue2)->getValue()->returns('2')
            -> allowing($headers2)->getAll()->returns(array($headerValue2))
            -> allowing($message2)->getHeaders()->returns($headers2)
            -> allowing($message2)->getFrom()->returns('b')
            -> allowing($t2)->isStarted() -> returns(false) -> when($con2->is('off'))
            -> allowing($t2)->isStarted() -> returns(true) -> when($con2->is('on'))
            -> allowing($t2)->start() -> when($con2->is('off')) -> then($con2->is('on'))
            -> allowing($t2)->send($message2, optional()) -> returns(1) -> when($con2->is('on'))
            -> never($t2)->send($message1, optional())
            -> ignoring($t2)
            -> allowing($headerValue3)->getValue()->returns('4')
            -> allowing($headers3)->getAll()->returns(array($headerValue3))
            -> allowing($message3)->getHeaders()->returns($headers3)
            -> allowing($message3)->getFrom()->returns('b')
            -> allowing($t3)->isStarted() -> returns(false) -> when($con3->is('off'))
            -> allowing($t3)->isStarted() -> returns(true) -> when($con3->is('on'))
            -> allowing($t3)->start() -> when($con3->is('off')) -> then($con3->is('on'))
            -> allowing($t3)->send($message3, optional()) -> returns(1) -> when($con3->is('on'))
            -> never($t3)->send($message1, optional())
            -> never($t3)->send($message2, optional())
            -> ignoring($t3)
            );

        $transport = $this->_getTransport(array('t1' => $t1, 't2' => $t2, 't3' => $t3), 't3');
        $transport->setMappings('t1',
                                array(array('getHeaders' => array('X-SWIFT-MAPPED-SENDER' => 't1')),
                                      array('getHeaders' => array('X-SWIFT-MAPPED-DELIVERY' => '1')),
                                      array('getFrom' => 't1@foo'),
                                      array('getFrom' => '1@foo'),
                                ));
        $transport->setMappings('t2',
                                array(array('getHeaders' => array('X-SWIFT-MAPPED-SENDER' => 't2')),
                                      array('getHeaders' => array('X-SWIFT-MAPPED-DELIVERY' => '2')),
                                      array('getFrom' => 't2@foo'),
                                      array('getFrom' => '2@foo'),
                                ));
        $transport->setMappings('t3',
                                array(array('getHeaders' => array('X-SWIFT-MAPPED-SENDER' => 't3')),
                                      array('getHeaders' => array('X-SWIFT-MAPPED-DELIVERY' => '3')),
                                      array('getFrom' => 't3@foo'),
                                      array('getFrom' => '3@foo'),
                                ));
        $transport->start();
        for ($i = 0; $i < 50; $i++) {
          $this->assertEqual(1, $transport->send($message1));
          $this->assertEqual(1, $transport->send($message2));
          $this->assertEqual(1, $transport->send($message3));
          $this->assertEqual(1, $transport->send($message2));
          $this->assertEqual(1, $transport->send($message3));
          $this->assertEqual(1, $transport->send($message1));
        }
        
        $context->assertIsSatisfied();
    }
    
    public function testRegisterPluginDelegatesToLoadedTransports()
    {
        $context = new Mockery();

        $plugin = $this->_createPlugin($context);

        $t1 = $context->mock('Swift_Transport');
        $t2 = $context->mock('Swift_Transport');
        $context->checking(Expectations::create()
            -> one($t1)->registerPlugin($plugin)
            -> one($t2)->registerPlugin($plugin)
            -> ignoring($t1)
            -> ignoring($t2)
            );

        $transport = $this->_getTransport(array('t1' => $t1, 't2' => $t2), 't1');
        $transport->registerPlugin($plugin);

        $context->assertIsSatisfied();
    }
    // -- Private helpers

    private function _getTransport(array $transports, $defaultTransportName)
    {
        $transport = new Swift_Transport_MappedTransport();
        $transport->setTransports($transports);
        $transport->setDefaultTransportName($defaultTransportName);
        
        return $transport;
    }

    private function _createPlugin($context)
    {
        return $context->mock('Swift_Events_EventListener');
    }

    private function _createInnerTransport()
    {
        return $this->_mockery()->mock('Swift_Transport');
    }

}
