<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';

class Swift_Transport_PostmarkTransportTest
  extends Swift_Tests_SwiftUnitTestCase
{
  public function testTransportStartedAlwaysReturnsFalse()
  {
    $dispatcher = $this->_createEventDispatcher();
    $transport = $this->_createTransport($dispatcher);
    
    $this->assertFalse($transport->isStarted());
    $transport->start();
    $this->assertFalse($transport->isStarted());
    $transport->stop();
    $this->assertFalse($transport->isStarted());
  }

  public function testRegisterPluginLoadsPluginInEventDispatcher()
  {
    $dispatcher = $this->_createEventDispatcher(false);
    $listener = $this->_mock('Swift_Events_EventListener');

    $transport = $this->_createTransport($dispatcher);
    $this->_checking(Expectations::create()
      -> one($dispatcher)->bindEventListener($listener)
      );
    $transport->registerPlugin($listener);
  }

  public function testMailWithoutFromFails()
  {
    $dispatcher = $this->_createEventDispatcher();
    $transport = $this->_createTransport($dispatcher);

    $to = $this->_createHeader('To', "Foo <foo@bar.com>");
    $headers = $this->_createHeaders(array(
      $to,
    ));
    $message = $this->_createMessage($headers);
    
    $this->_checking(Expectations::create()
      -> allowing($message)->getBody() -> returns("Text body")
      -> allowing($message)->getContentType() -> returns("text/plain")
      -> ignoring($dispatcher)
      -> ignoring($headers)
      -> ignoring($message)
    );

	try {
      $transport->send($message);
    } catch (Swift_PostmarkTransportException $expected) {
      return;
    }

    $this->fail('An expected Swift_Transport_PostmarkTransportException has not been raised.');
  }

  public function testMailWithoutToFails()
  {
    $dispatcher = $this->_createEventDispatcher();
    $transport = $this->_createTransport($dispatcher);

    $from = $this->_createHeader("From", "Bar <bar@foo.com>");
    $headers = $this->_createHeaders(array(
      $from,
    ));
    $message = $this->_createMessage($headers);
    
    $this->_checking(Expectations::create()
      -> allowing($message)->getBody() -> returns("Text body")
      -> allowing($message)->getContentType() -> returns("text/plain")
      -> ignoring($dispatcher)
      -> ignoring($headers)
      -> ignoring($message)
    );

	try {
      $transport->send($message);
    } catch (Swift_PostmarkTransportException $expected) {
      return;
    }

    $this->fail('An expected Swift_Transport_PostmarkTransportException has not been raised.');
  }

  public function testMailWithEmptyBodyFails()
  {
    $dispatcher = $this->_createEventDispatcher();
    $transport = $this->_createTransport($dispatcher);

    $to = $this->_createHeader('To', "Foo <foo@bar.com>");
    $from = $this->_createHeader("From", "Bar <bar@foo.com>");
    $headers = $this->_createHeaders(array(
      $to,
      $from,
    ));
    $message = $this->_createMessage($headers);
    
    $this->_checking(Expectations::create()
      -> allowing($message)->getBody() -> returns("")
      -> allowing($message)->getContentType() -> returns("text/plain")
      -> ignoring($dispatcher)
      -> ignoring($headers)
      -> ignoring($message)
    );

	try {
      $transport->send($message);
    } catch (Swift_PostmarkTransportException $expected) {
      return;
    }

    $this->fail('An expected Swift_Transport_PostmarkTransportException has not been raised.');
  }

  public function testSimpleMailIsSent()
  {
    $dispatcher = $this->_createEventDispatcher();
    $transport = $this->_createTransport($dispatcher);

    $to = $this->_createHeader('To', "Foo <foo@bar.com>");
    $from = $this->_createHeader("From", "Bar <bar@foo.com>");
    $headers = $this->_createHeaders(array(
      $to, $from,
    ));
    $message = $this->_createMessage($headers);
    
    $this->_checking(Expectations::create()
      -> allowing($message)->getBody() -> returns("Text body")
      -> allowing($message)->getContentType() -> returns("text/plain")
      -> ignoring($dispatcher)
      -> ignoring($headers)
      -> ignoring($message)
    );

    $transport->send($message);
  }

  public function testExtraHeadersAreParsed()
  {
    $dispatcher = $this->_createEventDispatcher();
    $transport = $this->_createTransport($dispatcher);

    $to = $this->_createHeader('To', "Foo <foo@bar.com>");
    $from = $this->_createHeader("From", "Bar <bar@foo.com>");
    $extra = $this->_createHeader("Extra", "Testing");
    $headers = $this->_createHeaders(array(
      $to, $from, $extra,
    ));

    $message = $this->_createMessage($headers);
    
    $this->_checking(Expectations::create()
      -> allowing($message)->getBody() -> returns("Text body")
      -> allowing($message)->getContentType() -> returns("text/plain")
      -> allowing($headers)->getAll() -> returns(array($extra))
      -> ignoring($dispatcher)
      -> ignoring($headers)
      -> ignoring($message)
    );

    $msg = $transport->buildPostmarkMessage($message);

    $this->assertEqual(1, sizeof($msg['Headers']));
    $this->assertEqual("Extra", $msg['Headers'][0]['Name']);
    $this->assertEqual("Testing", $msg['Headers'][0]['Value']);
  }

  // -- Creation Methods
  
  private function _createTransport($dispatcher)
  {
    $postmark = new Swift_Transport_PostmarkTransport($dispatcher);
    $postmark->setApiKey("POSTMARK_API_TEST");
    
    return $postmark;
  }
  
  private function _createEventDispatcher()
  {
    return $this->_mock('Swift_Events_EventDispatcher');
  }

  private function _createMessage($headers)
  {
    $message = $this->_mock('Swift_Mime_Message');
    $children = array();

    $this->_checking(Expectations::create()
      -> allowing($message)->getHeaders() -> returns($headers)
      -> allowing($message)->getChildren() -> returns($children)
    );

    return $message;
  }

  private function _createHeaders($headers = array())
  {
    $set = $this->_mock('Swift_Mime_HeaderSet');
    
    if (count($headers) > 0)
    {
      foreach ($headers as $header)
      {
        $this->_checking(Expectations::create()
          -> allowing($set)->get($header->getFieldName()) -> returns($header)
          -> allowing($set)->has($header->getFieldName()) -> returns(true)
        );
      }
    }
    
    $header = $this->_mock('Swift_Mime_Header');
    $this->_checking(Expectations::create()
      -> allowing($set)->get(any()) -> returns($header)
      -> allowing($set)->has(any()) -> returns(true)
      -> ignoring($header)
    );
    
    return $set;
  }

  private function _createHeader($name, $body = '')
  {
    $header = $this->_mock('Swift_Mime_Header');
    $this->_checking(Expectations::create()
      -> ignoring($header)->getFieldName() -> returns($name)
      -> ignoring($header)->toString() -> returns(sprintf("%s: %s\r\n", $name, $body))
      -> ignoring($header)->getFieldBody() -> returns($body)
    );

    return $header;
  }

}
