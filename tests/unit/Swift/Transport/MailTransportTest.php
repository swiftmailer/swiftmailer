<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';

class Swift_Transport_MailTransportTest extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testTransportInvokesMailOncePerMessage()
  {
    $invoker = $this->_createInvoker();
    $dispatcher = $this->_createEventDispatcher();
    $transport = $this->_createTransport($invoker, $dispatcher);
    
    $headers = $this->_createHeaders();
    $message = $this->_createMessage($headers);
    
    $this->_checking(Expectations::create()
      -> one($invoker)->mail(any(), any(), any(), any(), optional())
      -> ignoring($dispatcher)
      -> ignoring($headers)
      -> ignoring($message)
    );
    
    $transport->send($message);
  }
  
  public function testTransportUsesToFieldBodyInSending()
  {
    $invoker = $this->_createInvoker();
    $dispatcher = $this->_createEventDispatcher();
    $transport = $this->_createTransport($invoker, $dispatcher);
    
    $to = $this->_createHeader();
    $headers = $this->_createHeaders(array(
      'To' => $to
    ));
    $message = $this->_createMessage($headers);
    
    $this->_checking(Expectations::create()
      -> allowing($to)->getFieldBody() -> returns("Foo <foo@bar>")
      -> one($invoker)->mail("Foo <foo@bar>", any(), any(), any(), optional())
      -> ignoring($dispatcher)
      -> ignoring($headers)
      -> ignoring($message)
      -> ignoring($to)
    );
    
    $transport->send($message);
  }
  
  public function testTransportUsesSubjectFieldBodyInSending()
  {
    $invoker = $this->_createInvoker();
    $dispatcher = $this->_createEventDispatcher();
    $transport = $this->_createTransport($invoker, $dispatcher);
    
    $subj = $this->_createHeader();
    $headers = $this->_createHeaders(array(
      'Subject' => $subj
    ));
    $message = $this->_createMessage($headers);
    
    $this->_checking(Expectations::create()
      -> allowing($subj)->getFieldBody() -> returns("Thing")
      -> one($invoker)->mail(any(), "Thing", any(), any(), optional())
      -> ignoring($dispatcher)
      -> ignoring($headers)
      -> ignoring($message)
      -> ignoring($subj)
    );
    
    $transport->send($message);
  }
  
  public function testTransportUsesBodyOfMessage()
  {
    $invoker = $this->_createInvoker();
    $dispatcher = $this->_createEventDispatcher();
    $transport = $this->_createTransport($invoker, $dispatcher);
    
    $headers = $this->_createHeaders();
    $message = $this->_createMessage($headers);
    
    $this->_checking(Expectations::create()
      -> allowing($message)->toString() -> returns(
        "To: Foo <foo@bar>\r\n" .
        "\r\n" .
        "This body"
        )
      -> one($invoker)->mail(any(), any(), "This body", any(), optional())
      -> ignoring($dispatcher)
      -> ignoring($headers)
      -> ignoring($message)
    );
    
    $transport->send($message);
  }
  
  public function testTransportUsesHeadersFromMessage()
  {
    $invoker = $this->_createInvoker();
    $dispatcher = $this->_createEventDispatcher();
    $transport = $this->_createTransport($invoker, $dispatcher);
    
    $headers = $this->_createHeaders();
    $message = $this->_createMessage($headers);
    
    $this->_checking(Expectations::create()
      -> allowing($message)->toString() -> returns(
        "Subject: Stuff\r\n" .
        "\r\n" .
        "This body"
        )
      -> one($invoker)->mail(any(), any(), any(), "Subject: Stuff" . PHP_EOL, optional())
      -> ignoring($dispatcher)
      -> ignoring($headers)
      -> ignoring($message)
    );
    
    $transport->send($message);
  }
  
  public function testTransportReturnsCountOfAllRecipientsIfInvokerReturnsTrue()
  {
    $invoker = $this->_createInvoker();
    $dispatcher = $this->_createEventDispatcher();
    $transport = $this->_createTransport($invoker, $dispatcher);
    
    $headers = $this->_createHeaders();
    $message = $this->_createMessage($headers);
    
    $this->_checking(Expectations::create()
      -> allowing($message)->getTo() -> returns(array('foo@bar'=>null, 'zip@button'=>null))
      -> allowing($message)->getCc() -> returns(array('test@test'=>null))
      -> one($invoker)->mail(any(), any(), any(), any(), optional()) -> returns(true)
      -> ignoring($dispatcher)
      -> ignoring($headers)
      -> ignoring($message)
    );
    
    $this->assertEqual(3, $transport->send($message));
  }
  
  public function testTransportReturnsZeroIfInvokerReturnsFalse()
  {
    $invoker = $this->_createInvoker();
    $dispatcher = $this->_createEventDispatcher();
    $transport = $this->_createTransport($invoker, $dispatcher);
    
    $headers = $this->_createHeaders();
    $message = $this->_createMessage($headers);
    
    $this->_checking(Expectations::create()
      -> allowing($message)->getTo() -> returns(array('foo@bar'=>null, 'zip@button'=>null))
      -> allowing($message)->getCc() -> returns(array('test@test'=>null))
      -> one($invoker)->mail(any(), any(), any(), any(), optional()) -> returns(false)
      -> ignoring($dispatcher)
      -> ignoring($headers)
      -> ignoring($message)
    );
    
    $this->assertEqual(0, $transport->send($message));
  }
  
  public function testToHeaderIsRemovedFromHeaderSetDuringSending()
  {
    $invoker = $this->_createInvoker();
    $dispatcher = $this->_createEventDispatcher();
    $transport = $this->_createTransport($invoker, $dispatcher);
    
    $to = $this->_createHeader();
    $headers = $this->_createHeaders(array(
      'To' => $to
    ));
    $message = $this->_createMessage($headers);
    
    $this->_checking(Expectations::create()
      -> one($headers)->remove('To')
      -> one($invoker)->mail(any(), any(), any(), any(), optional())
      -> ignoring($dispatcher)
      -> ignoring($headers)
      -> ignoring($message)
      -> ignoring($to)
    );
    
    $transport->send($message);
  }
  
  public function testSubjectHeaderIsRemovedFromHeaderSetDuringSending()
  {
    $invoker = $this->_createInvoker();
    $dispatcher = $this->_createEventDispatcher();
    $transport = $this->_createTransport($invoker, $dispatcher);
    
    $subject = $this->_createHeader();
    $headers = $this->_createHeaders(array(
      'Subject' => $subject
    ));
    $message = $this->_createMessage($headers);
    
    $this->_checking(Expectations::create()
      -> one($headers)->remove('Subject')
      -> one($invoker)->mail(any(), any(), any(), any(), optional())
      -> ignoring($dispatcher)
      -> ignoring($headers)
      -> ignoring($message)
      -> ignoring($subject)
    );
    
    $transport->send($message);
  }
  
  public function testToHeaderIsPutBackAfterSending()
  {
    $invoker = $this->_createInvoker();
    $dispatcher = $this->_createEventDispatcher();
    $transport = $this->_createTransport($invoker, $dispatcher);
    
    $to = $this->_createHeader();
    $headers = $this->_createHeaders(array(
      'To' => $to
    ));
    $message = $this->_createMessage($headers);
    
    $this->_checking(Expectations::create()
      -> one($headers)->set($to, optional())
      -> one($invoker)->mail(any(), any(), any(), any(), optional())
      -> ignoring($dispatcher)
      -> ignoring($headers)
      -> ignoring($message)
      -> ignoring($to)
    );
    
    $transport->send($message);
  }
  
  public function testSubjectHeaderIsPutBackAfterSending()
  {
    $invoker = $this->_createInvoker();
    $dispatcher = $this->_createEventDispatcher();
    $transport = $this->_createTransport($invoker, $dispatcher);
    
    $subject = $this->_createHeader();
    $headers = $this->_createHeaders(array(
      'Subject' => $subject
    ));
    $message = $this->_createMessage($headers);
    
    $this->_checking(Expectations::create()
      -> one($headers)->set($subject, optional())
      -> one($invoker)->mail(any(), any(), any(), any(), optional())
      -> ignoring($dispatcher)
      -> ignoring($headers)
      -> ignoring($message)
      -> ignoring($subject)
    );
    
    $transport->send($message);
  }
  
  // -- Creation Methods
  
  private function _createTransport($invoker, $dispatcher)
  {
    return new Swift_Transport_MailTransport($invoker, $dispatcher);
  }
  
  private function _createEventDispatcher()
  {
    return $this->_mock('Swift_Events_EventDispatcher');
  }
  
  private function _createInvoker()
  {
    return $this->_mock('Swift_Transport_MailInvoker');
  }
  
  private function _createMessage($headers)
  {
    $message = $this->_mock('Swift_Mime_Message');
    
    $this->_checking(Expectations::create()
      -> allowing($message)->getHeaders() -> returns($headers)
      );
    
    return $message;
  }
  
  private function _createHeaders($headers = array())
  {
    $set = $this->_mock('Swift_Mime_HeaderSet');
    
    if (count($headers) > 0)
    {
      foreach ($headers as $name => $header)
      {
        $this->_checking(Expectations::create()
          -> allowing($set)->get($name) -> returns($header)
          -> allowing($set)->has($name) -> returns(true)
        );
      }
    }
    
    $header = $this->_createHeader();
    $this->_checking(Expectations::create()
      -> allowing($set)->get(any()) -> returns($header)
      -> allowing($set)->has(any()) -> returns(true)
      -> ignoring($header)
    );
    
    return $set;
  }
  
  private function _createHeader()
  {
    return $this->_mock('Swift_Mime_Header');
  }
  
}
