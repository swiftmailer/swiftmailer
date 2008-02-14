<?php

require_once 'Swift/ByteStream/FileByteStream.php';

class Swift_ByteStream_FileByteStreamAcceptanceTest extends UnitTestCase
{
  
  private $_tmpDir;
  private $_testFile;
  
  public function skip()
  {
    $this->skipUnless(
      SWIFT_TMP_DIR, '%s: SWIFT_TMP_DIR needs to be set in tests/config.php first'
      );
  }
  
  public function setUp()
  {
    $this->_tmpDir = SWIFT_TMP_DIR;
    $this->_testFile = $this->_tmpDir . '/swift-test-file' . __CLASS__;
    file_put_contents($this->_testFile, 'abcdefghijklm');
  }
  
  public function tearDown()
  {
    unlink($this->_testFile);
  }
  
  public function testFileDataCanBeRead()
  {
    $file = new Swift_ByteStream_FileByteStream($this->_testFile);
    $str = '';
    while (false !== $bytes = $file->read(8192))
    {
      $str .= $bytes;
    }
    $this->assertEqual('abcdefghijklm', $str);
  }
  
  public function testFileDataCanBeReadSequentially()
  {
    $file = new Swift_ByteStream_FileByteStream($this->_testFile);
    $this->assertEqual('abcde', $file->read(5));
    $this->assertEqual('fghijklm', $file->read(8));
    $this->assertFalse($file->read(1));
  }
  
  public function testFilenameIsReturned()
  {
    $file = new Swift_ByteStream_FileByteStream($this->_testFile);
    $this->assertEqual($this->_testFile, $file->getPath());
  }
  
  public function testFileCanBeWrittenTo()
  {
    $file = new Swift_ByteStream_FileByteStream(
      $this->_testFile, true
      );
    $file->write('foobar');
    $this->assertEqual('foobar', $file->read(8192));
  }
  
  public function testReadingFromThenWritingToFile()
  {
    $file = new Swift_ByteStream_FileByteStream(
      $this->_testFile, true
      );
    $file->write('foobar');
    $this->assertEqual('foobar', $file->read(8192));
    $file->write('zipbutton');
    $this->assertEqual('zipbutton', $file->read(8192));
  }
  
  public function testFlushContents()
  {
    $file = new Swift_ByteStream_FileByteStream(
      $this->_testFile, true
      );
    $file->write('foobar');
    $file->flushContents();
    $this->assertFalse($file->read(8192));
  }
  
}
