<?php

require_once 'Swift/Mime/MimeEntity.php';
require_once 'Swift/Mime/Attachment.php';
require_once 'Swift/AbstractSwiftUnitTestCase.php';
require_once 'Swift/Mime/ContentEncoder.php';
require_once 'Swift/Mime/Header.php';
require_once 'Swift/Mime/FieldChangeObserver.php';

Mock::generate('Swift_Mime_ContentEncoder', 'Swift_Mime_MockContentEncoder');
Mock::generate('Swift_Mime_Header', 'Swift_Mime_MockHeader');
Mock::generate('Swift_Mime_FieldChangeObserver',
  'Swift_Mime_MockFieldChangeObserver'
  );

class Swift_Mime_AttachmentTest extends Swift_AbstractSwiftUnitTestCase
{
  private $_encoder;
  
  public function setUp()
  {
    $this->_encoder = new Swift_Mime_MockContentEncoder();
    $this->_encoder->setReturnValue('getName', 'base64');
  }
  
  public function testNestingLevelIsAttachment()
  {
    $attachment = $this->_createAttachment(array(), $this->_encoder);
    $this->assertEqual(
      Swift_Mime_MimeEntity::LEVEL_ATTACHMENT, $attachment->getNestingLevel()
      );
  }
  
  public function testFluidInterface()
  {
    $attachment = $this->_createAttachment(array(), $this->_encoder);
    $ref = $attachment
      ->setContentType('application/pdf')
      ->setEncoder($this->_encoder)
      ->setId('foo@bar')
      ->setDescription('my pdf')
      ->setMaxLineLength(998)
      ->setBodyAsString('xx')
      ->setNestingLevel(10)
      ->setBoundary('xyz')
      ->setChildren(array())
      ->setHeaders(array())
      ;
    
    $this->assertReference($attachment, $ref);
  }
  
  // -- Private helpers
  
  private function _createAttachment($headers, $encoder)
  {
    return new Swift_Mime_Attachment($headers, $encoder);
  }
  
}
