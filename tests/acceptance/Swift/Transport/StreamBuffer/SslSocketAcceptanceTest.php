<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/StreamBuffer.php';
require_once 'Swift/ReplacementFilterFactory.php';

class Swift_Transport_StreamBuffer_SslSocketAcceptanceTest
  extends Swift_Tests_SwiftUnitTestCase
{

  private $_buffer;
  
  public function skip()
  {
    $streams = stream_get_transports();
    $this->skipIf(!in_array('ssl', $streams),
      'SSL is not configured for your system.  It is not possible to run this test'
      );
    $this->skipIf(!SWIFT_SSL_HOST,
      'Cannot run test without an SSL enabled SMTP host to connect to (define ' .
      'SWIFT_SSL_HOST in tests/acceptance.conf.php if you wish to run this test)'
      );
  }
  
  public function setUp()
  {
    $this->_buffer = new Swift_Transport_StreamBuffer(
      $this->_stub('Swift_ReplacementFilterFactory')
      );
  }
  
  public function testReadLine()
  {
    $parts = explode(':', SWIFT_SSL_HOST);
    $host = $parts[0];
    $port = isset($parts[1]) ? $parts[1] : 25;
    
    $this->_buffer->initialize(array(
      'type' => Swift_Transport_IoBuffer::TYPE_SOCKET,
      'host' => $host,
      'port' => $port,
      'protocol' => 'ssl',
      'blocking' => 1,
      'timeout' => 15
      ));
    
    $line = $this->_buffer->readLine(0);
    $this->assertPattern('/^[0-9]{3}.*?\r\n$/D', $line);
    $seq = $this->_buffer->write("QUIT\r\n");
    $this->assertTrue($seq);
    $line = $this->_buffer->readLine($seq);
    $this->assertPattern('/^[0-9]{3}.*?\r\n$/D', $line);
    $this->_buffer->terminate();
  }
  
  public function testWrite()
  {
    $parts = explode(':', SWIFT_SSL_HOST);
    $host = $parts[0];
    $port = isset($parts[1]) ? $parts[1] : 25;
    
    $this->_buffer->initialize(array(
      'type' => Swift_Transport_IoBuffer::TYPE_SOCKET,
      'host' => $host,
      'port' => $port,
      'protocol' => 'ssl',
      'blocking' => 1,
      'timeout' => 15
      ));
    
    $line = $this->_buffer->readLine(0);
    $this->assertPattern('/^[0-9]{3}.*?\r\n$/D', $line);
    
    $seq = $this->_buffer->write("HELO foo\r\n");
    $this->assertTrue($seq);
    $line = $this->_buffer->readLine($seq);
    $this->assertPattern('/^[0-9]{3}.*?\r\n$/D', $line);
    
    $seq = $this->_buffer->write("QUIT\r\n");
    $this->assertTrue($seq);
    $line = $this->_buffer->readLine($seq);
    $this->assertPattern('/^[0-9]{3}.*?\r\n$/D', $line);
    $this->_buffer->terminate();
  }
  
}
