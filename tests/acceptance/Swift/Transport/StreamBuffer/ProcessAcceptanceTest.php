<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/StreamBuffer.php';
require_once 'Swift/ReplacementFilterFactory.php';

class Swift_Transport_StreamBuffer_ProcessAcceptanceTest
  extends Swift_Tests_SwiftUnitTestCase
{

  private $_buffer;
  
  public function skip()
  {
    $this->skipIf(!SWIFT_SENDMAIL_PATH,
      'Cannot run test without a path to sendmail (define ' .
      'SWIFT_SENDMAIL_PATH in tests/acceptance.conf.php if you wish to run this test)'
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
    $this->_buffer->initialize(array(
      'type' => Swift_Transport_IoBuffer::TYPE_PROCESS,
      'command' => SWIFT_SENDMAIL_PATH . ' -bs'
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
    $this->_buffer->initialize(array(
      'type' => Swift_Transport_IoBuffer::TYPE_PROCESS,
      'command' => SWIFT_SENDMAIL_PATH . ' -bs'
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
