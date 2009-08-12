<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';

class Swift_Bug51Test extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_attachmentFile;
  private $_outputFile;
  
  public function skip()
  {
    $this->skipUnless(
      is_writable(SWIFT_TMP_DIR),
      '%s: This test requires tests/acceptance.conf.php to specify a ' .
      'writable SWIFT_TMP_DIR'
    );
  }
  
  public function setUp()
  {
    $this->_attachmentFile = SWIFT_TMP_DIR . '/attach.rand.bin';
    file_put_contents($this->_attachmentFile, '');
    
    $this->_outputFile = SWIFT_TMP_DIR . '/attach.out.bin';
    file_put_contents($this->_outputFile, '');
  }
  
  public function tearDown()
  {
    unlink($this->_attachmentFile);
    unlink($this->_outputFile);
  }
  
  public function testAttachmentsDoNotGetTruncatedUsingToByteStream()
  {
    //Run 100 times with 10KB attachments
    for ($i = 0; $i < 10; ++$i)
    {
      $message = $this->_createMessageWithRandomAttachment(
        10000, $this->_attachmentFile
      );
      
      file_put_contents($this->_outputFile, '');
      $message->toByteStream(
        new Swift_ByteStream_FileByteStream($this->_outputFile, true)
      );
      
      $emailSource = file_get_contents($this->_outputFile);
      
      $this->assertAttachmentFromSourceMatches(
        file_get_contents($this->_attachmentFile),
        $emailSource
      );
    }
  }
  
  public function testAttachmentsDoNotGetTruncatedUsingToString()
  {
    //Run 100 times with 10KB attachments
    for ($i = 0; $i < 10; ++$i)
    {
      $message = $this->_createMessageWithRandomAttachment(
        10000, $this->_attachmentFile
      );
      
      $emailSource = $message->toString();
      
      $this->assertAttachmentFromSourceMatches(
        file_get_contents($this->_attachmentFile),
        $emailSource
      );
    }
  }
  
  // -- Custom Assertions
  
  public function assertAttachmentFromSourceMatches($attachmentData, $source)
  {
    $encHeader = 'Content-Transfer-Encoding: base64';
    $base64declaration = strpos($source, $encHeader);
    
    $attachmentDataStart = strpos($source, "\r\n\r\n", $base64declaration);
    $attachmentDataEnd = strpos($source, "\r\n--", $attachmentDataStart);
    
    if (false === $attachmentDataEnd)
    {
      $attachmentBase64 = trim(substr($source, $attachmentDataStart));
    }
    else
    {
      $attachmentBase64 = trim(substr(
        $source, $attachmentDataStart,
        $attachmentDataEnd - $attachmentDataStart
      ));
    }
    
    $this->assertIdenticalBinary($attachmentData, base64_decode($attachmentBase64));
  }
  
  // -- Creation Methods
  
  private function _fillFileWithRandomBytes($byteCount, $file)
  {
    // I was going to use dd with if=/dev/random but this way seems more
    // cross platform even if a hella expensive!!
    
    file_put_contents($file, '');
    $fp = fopen($file, 'wb');
    for ($i = 0; $i < $byteCount; ++$i)
    {
      $byteVal = rand(0, 255);
      fwrite($fp, pack('i', $byteVal));
    }
    fclose($fp);
  }
  
  private function _createMessageWithRandomAttachment($size, $attachmentPath)
  {
    $this->_fillFileWithRandomBytes($size, $attachmentPath);
    
    $message = Swift_Message::newInstance()
      ->setSubject('test')
      ->setBody('test')
      ->setFrom('a@b.c')
      ->setTo('d@e.f')
      ->attach(Swift_Attachment::fromPath($attachmentPath))
      ;
    
    return $message;
  }
  
}
