<?php

require_once 'Swift/Transport/StreamBuffer/AbstractStreamBufferAcceptanceTest.php';

class Swift_Transport_StreamBuffer_ProcessAcceptanceTest
  extends Swift_Transport_StreamBuffer_AbstractStreamBufferAcceptanceTest
{
  
  public function skip()
  {
    $this->skipIf(!SWIFT_SENDMAIL_PATH,
      'Cannot run test without a path to sendmail (define ' .
      'SWIFT_SENDMAIL_PATH in tests/acceptance.conf.php if you wish to run this test)'
      );
  }
  
  protected function _initializeBuffer()
  {
    $this->_buffer->initialize(array(
      'type' => Swift_Transport_IoBuffer::TYPE_PROCESS,
      'command' => SWIFT_SENDMAIL_PATH . ' -bs'
      ));
  }
  
}
