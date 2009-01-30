<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/StreamBuffer.php';
require_once 'Swift/ReplacementFilterFactory.php';
require_once 'Swift/InputByteStream.php';

abstract class Swift_Transport_StreamBuffer_AbstractStreamBufferAcceptanceTest
  extends Swift_Tests_SwiftUnitTestCase
{

  protected $_buffer;
  
  abstract protected function _initializeBuffer();
  
  public function setUp()
  {
    $this->_buffer = new Swift_Transport_StreamBuffer(
      $this->_stub('Swift_ReplacementFilterFactory')
      );
  }
  
  public function testReadLine()
  {
    $this->_initializeBuffer();
    
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
    $this->_initializeBuffer();
    
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
  
  public function testBindingOtherStreamsMirrorsWriteOperations()
  {
    $this->_initializeBuffer();
    
    $is1 = $this->_createMockInputStream();
    $is2 = $this->_createMockInputStream();
    
    $this->_checking(Expectations::create()
      -> one($is1)->write('x')
      -> one($is2)->write('x')
      -> one($is1)->write('y')
      -> one($is2)->write('y')
    );
    
    $this->_buffer->bind($is1);
    $this->_buffer->bind($is2);
    
    $this->_buffer->write('x');
    $this->_buffer->write('y');
  }
  
  public function testBindingOtherStreamsMirrorsFlushOperations()
  {
    $this->_initializeBuffer();
    
    $is1 = $this->_createMockInputStream();
    $is2 = $this->_createMockInputStream();
    
    $this->_checking(Expectations::create()
      -> one($is1)->flushBuffers()
      -> one($is2)->flushBuffers()
    );
    
    $this->_buffer->bind($is1);
    $this->_buffer->bind($is2);
    
    $this->_buffer->flushBuffers();
  }
  
  public function testUnbindingStreamPreventsFurtherWrites()
  {
    $this->_initializeBuffer();
    
    $is1 = $this->_createMockInputStream();
    $is2 = $this->_createMockInputStream();
    
    $this->_checking(Expectations::create()
      -> one($is1)->write('x')
      -> one($is2)->write('x')
      -> one($is1)->write('y')
    );
    
    $this->_buffer->bind($is1);
    $this->_buffer->bind($is2);
    
    $this->_buffer->write('x');
    
    $this->_buffer->unbind($is2);
    
    $this->_buffer->write('y');
  }
  
  // -- Creation Methods
  
  private function _createMockInputStream()
  {
    return $this->_mock('Swift_InputByteStream');
  }
  
}
