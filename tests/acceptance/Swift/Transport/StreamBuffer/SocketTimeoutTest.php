<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/StreamBuffer.php';
require_once 'Swift/ReplacementFilterFactory.php';
require_once 'Swift/InputByteStream.php';
require_once 'Swift/IoException.php';

class Swift_Transport_StreamBuffer_SocketTimeoutTest
  extends Swift_Tests_SwiftUnitTestCase
{
  protected $_buffer;
  
  protected $_server;
  
  public function setUp()
  {
    $this->_buffer = new Swift_Transport_StreamBuffer(
      $this->_stub('Swift_ReplacementFilterFactory')
      );
  }
  
  public function skip()
  {
    $serverStarted=false;
    for ($i=0; $i<5; ++$i) 
    {
      $this->_randomHighPort=rand(50000,65000);
      $this->_server = stream_socket_server('tcp://127.0.0.1:' . $this->_randomHighPort);
      if ($this->_server)
      {
        $serverStarted=true;
      } 
    }
    $this->skipUnless(SWIFT_SMTP_HOST,
      'Cannot run test without an SMTP host to connect to (define ' .
      'SWIFT_SMTP_HOST in tests/acceptance.conf.php if you wish to run this test)'
      );
  }
  
  protected function _initializeBuffer()
  {
    $host = '127.0.0.1';
    $port = $this->_randomHighPort;
    
    $this->_buffer->initialize(array(
      'type' => Swift_Transport_IoBuffer::TYPE_SOCKET,
      'host' => $host,
      'port' => $port,
      'protocol' => 'tcp',
      'blocking' => 1,
      'timeout' => 1
      ));
  }
  
  public function testTimeoutException()
  {
    $this->_initializeBuffer();
    $e=null;
    try {
      $line = $this->_buffer->readLine(0);
    } catch (Exception $e) {
    }
    $this->assertIsA($e, 'Swift_IoException', 'IO Exception Not Thrown On Connection Timeout');
    $this->assertPattern('/Connection to .* Timed Out/', $e->getMessage());
  }

  public function tearDown()
  {
    if ($this->_server)
    {
      stream_socket_shutdown($this->_server, STREAM_SHUT_RDWR);
    }
  }
}
