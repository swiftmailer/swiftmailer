<?php

require_once 'Swift/Transport/StreamBuffer/AbstractStreamBufferAcceptanceTest.php';

class Swift_Transport_StreamBuffer_SslSocketAcceptanceTest
  extends Swift_Transport_StreamBuffer_AbstractStreamBufferAcceptanceTest
{

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
  
  protected function _initializeBuffer()
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
  }
  
}
