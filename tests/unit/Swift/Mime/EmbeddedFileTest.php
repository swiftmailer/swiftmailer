<?php

require_once 'Swift/Mime/EmbeddedFile.php';
require_once 'Swift/Mime/AttachmentTest.php';

class Swift_Mime_EmbeddedFileTest extends Swift_Mime_AttachmentTest
{
  
  public function testNestingLevelIsAttachment()
  { //Overridden
  }
  
  public function testNestingLevelIsEmbedded()
  {
    $context = new Mockery();
    $file = $this->_createEmbeddedFile(
      array(), $this->_getEncoder($context), $this->_getCache($context)
      );
    $this->assertEqual(
      Swift_Mime_MimeEntity::LEVEL_EMBEDDED, $file->getNestingLevel()
      );
  }
  
  public function testDefaultDispositionIsAttachment()
  { //Overridden
  }
  
  public function testDefaultDispositionIsInline()
  {
    $context = new Mockery();
    $file = $this->_createEmbeddedFile(
      array(), $this->_getEncoder($context), $this->_getCache($context)
      );
    $this->assertEqual('inline', $file->getDisposition());
  }
  
  // -- Private helpers
  
  protected function _createAttachment($headers, $encoder, $cache)
  {
    return $this->_createEmbeddedFile($headers, $encoder, $cache);
  }
  
  private function _createEmbeddedFile($headers, $encoder, $cache)
  {
    return new Swift_Mime_EmbeddedFile($headers, $encoder, $cache);
  }
  
}
