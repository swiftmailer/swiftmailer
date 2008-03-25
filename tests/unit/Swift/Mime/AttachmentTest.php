<?php

require_once 'Swift/Mime/MimeEntity.php';
require_once 'Swift/Mime/Attachment.php';
require_once 'Swift/Mime/AbstractMimeEntityTest.php';
require_once 'Swift/Mime/Header.php';
require_once 'Swift/FileStream.php';

class Swift_Mime_AttachmentTest extends Swift_Mime_AbstractMimeEntityTest
{
  
  public function testNestingLevelIsAttachment()
  {
    $context = new Mockery();
    $attachment = $this->_createAttachment(
      array(), $this->_getEncoder($context), $this->_getCache($context)
      );
    $this->assertEqual(
      Swift_Mime_MimeEntity::LEVEL_ATTACHMENT, $attachment->getNestingLevel()
      );
  }
  
  public function testDispositionIsSetInHeader()
  {
    /* -- RFC 2183, 2.1, 2.2.
     */
    
    $context = new Mockery();
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->setFieldBodyModel('inline')
      -> allowing($h)->getFieldName() -> returns('Content-Disposition')
      -> ignoring($h)
      );
    
    $attachment = $this->_createAttachment(
      array($h), $this->_getEncoder($context), $this->_getCache($context)
      );
    $attachment->setDisposition('inline');
    
    $context->assertIsSatisfied();
  }
  
  public function testDispositionIsReadFromHeader()
  {
    $context = new Mockery();
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->getFieldBodyModel() -> returns('attachment')
      -> allowing($h)->getFieldName() -> returns('Content-Disposition')
      -> ignoring($h)
      );
    
    $attachment = $this->_createAttachment(
      array($h), $this->_getEncoder($context), $this->_getCache($context)
      );
    $this->assertEqual('attachment', $attachment->getDisposition());
    
    $context->assertIsSatisfied();
  }
  
  public function testDefaultDispositionIsAttachment()
  {
    $context = new Mockery();
    $attachment = $this->_createAttachment(
      array(), $this->_getEncoder($context), $this->_getCache($context)
      );
    $this->assertEqual('attachment', $attachment->getDisposition());
  }
  
  public function testFilenameIsSetInHeader()
  {
    /* -- RFC 2183, 2.3.
     */
    
    $context = new Mockery();
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->setParameter('filename', 'some-file.pdf')
      -> allowing($h)->getFieldName() -> returns('Content-Disposition')
      -> ignoring($h)
      );
    
    $attachment = $this->_createAttachment(
      array($h), $this->_getEncoder($context), $this->_getCache($context)
      );
    $attachment->setFilename('some-file.pdf');
    
    $context->assertIsSatisfied();
  }
  
  public function testFilenameIsReadFromHeader()
  {
    $context = new Mockery();
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->getParameter('filename') -> returns('a-file.txt')
      -> allowing($h)->getFieldName() -> returns('Content-Disposition')
      -> ignoring($h)
      );
    
    $attachment = $this->_createAttachment(
      array($h), $this->_getEncoder($context), $this->_getCache($context)
      );
    $this->assertEqual('a-file.txt', $attachment->getFilename());
    
    $context->assertIsSatisfied();
  }
  
  public function testCreationDateIsSetInHeader()
  {
    /* -- RFC 2183, 2.4.
     */
    
    $context = new Mockery();
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->setParameter('creation-date', date('r', 1234))
      -> allowing($h)->getFieldName() -> returns('Content-Disposition')
      -> ignoring($h)
      );
    
    $attachment = $this->_createAttachment(
      array($h), $this->_getEncoder($context), $this->_getCache($context)
      );
    $attachment->setCreationDate(1234);
    
    $context->assertIsSatisfied();
  }
  
  public function testCreationDateIsReadFromHeader()
  {
    $context = new Mockery();
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->getParameter('creation-date') -> returns(date('r', 1234))
      -> allowing($h)->getFieldName() -> returns('Content-Disposition')
      -> ignoring($h)
      );
    
    $attachment = $this->_createAttachment(
      array($h), $this->_getEncoder($context), $this->_getCache($context)
      );
    $this->assertEqual(1234, $attachment->getCreationDate());
    
    $context->assertIsSatisfied();
  }
  
  public function testModificationDateIsSetInHeader()
  {
    /* -- RFC 2183, 2.5.
     */
    
    $context = new Mockery();
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->setParameter('modification-date', date('r', 12345))
      -> allowing($h)->getFieldName() -> returns('Content-Disposition')
      -> ignoring($h)
      );
    
    $attachment = $this->_createAttachment(
      array($h), $this->_getEncoder($context), $this->_getCache($context)
      );
    $attachment->setModificationDate(12345);
    
    $context->assertIsSatisfied();
  }
  
  public function testModificationDateIsReadFromHeader()
  {
    $context = new Mockery();
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->getParameter('modification-date') -> returns(date('r', 1234))
      -> allowing($h)->getFieldName() -> returns('Content-Disposition')
      -> ignoring($h)
      );
    
    $attachment = $this->_createAttachment(
      array($h), $this->_getEncoder($context), $this->_getCache($context)
      );
    $this->assertEqual(1234, $attachment->getModificationDate());
    
    $context->assertIsSatisfied();
  }
  
  public function testReadDateIsSetInHeader()
  {
    /* -- RFC 2183, 2.6.
     */
    
    $context = new Mockery();
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->setParameter('read-date', date('r', 12345))
      -> allowing($h)->getFieldName() -> returns('Content-Disposition')
      -> ignoring($h)
      );
    
    $attachment = $this->_createAttachment(
      array($h), $this->_getEncoder($context), $this->_getCache($context)
      );
    $attachment->setReadDate(12345);
    
    $context->assertIsSatisfied();
  }
  
  public function testReadDateIsReadFromHeader()
  {
    $context = new Mockery();
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->getParameter('read-date') -> returns(date('r', 1234))
      -> allowing($h)->getFieldName() -> returns('Content-Disposition')
      -> ignoring($h)
      );
    
    $attachment = $this->_createAttachment(
      array($h), $this->_getEncoder($context), $this->_getCache($context)
      );
    $this->assertEqual(1234, $attachment->getReadDate());
    
    $context->assertIsSatisfied();
  }
  
  public function testSizeIsSetInHeader()
  {
    /* -- RFC 2183, 2.7.
     */
    
    $context = new Mockery();
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->setParameter('size', 1234)
      -> allowing($h)->getFieldName() -> returns('Content-Disposition')
      -> ignoring($h)
      );
    
    $attachment = $this->_createAttachment(
      array($h), $this->_getEncoder($context), $this->_getCache($context)
      );
    $attachment->setSize(1234);
    
    $context->assertIsSatisfied();
  }
  
  public function testSizeIsReadFromHeader()
  {
    $context = new Mockery();
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->getParameter('size') -> returns(1234)
      -> allowing($h)->getFieldName() -> returns('Content-Disposition')
      -> ignoring($h)
      );
    
    $attachment = $this->_createAttachment(
      array($h), $this->_getEncoder($context), $this->_getCache($context)
      );
    $this->assertEqual(1234, $attachment->getSize());
    
    $context->assertIsSatisfied();
  }
  
  public function testFilnameCanBeReadFromFileStream()
  {
    $context = new Mockery();
    $file = $context->mock('Swift_FileStream');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($file)->getPath() -> returns('/path/to/some-image.jpg')
      -> one($file)->read(optional()) -> returns('<image data>')
      -> one($file)->read(optional()) -> returns(false)
      -> ignoring($file)
      );
    
    $entity = $this->_createAttachment(
      array(), $this->_getEncoder($context), $this->_getCache($context)
      );
    $entity->setFile($file);
    $this->assertEqual('some-image.jpg', $entity->getFilename());
    $this->assertEqual('<image data>', $entity->getBodyAsString());
    
    $context->assertIsSatisfied();
  }
  
  public function testFluidInterface()
  {
    $context = new Mockery();
    $child = $context->mock('Swift_Mime_MimeEntity');
    $encoder = $this->_getEncoder($context);
    $file = $context->mock('Swift_FileStream');
    $context->checking(Expectations::create()
      -> allowing($child)->getNestingLevel() -> returns(Swift_Mime_MimeEntity::LEVEL_SUBPART)
      -> ignoring($child)
      -> ignoring($file)
      );
    $attachment = $this->_createAttachment(
      array(), $encoder, $this->_getCache($context)
      );
    $ref = $attachment
      ->setContentType('application/pdf')
      ->setEncoder($encoder)
      ->setId('foo@bar')
      ->setDescription('my pdf')
      ->setMaxLineLength(998)
      ->setBodyAsString('xx')
      ->setNestingLevel(10)
      ->setBoundary('xyz')
      ->setChildren(array($child))
      ->setHeaders(array())
      ->setDisposition('inline')
      ->setFilename('afile.txt')
      ->setCreationDate(time())
      ->setModificationDate(time() + 10)
      ->setReadDate(time() + 20)
      ->setSize(123)
      ->setFile($file)
      ;
    
    $this->assertReference($attachment, $ref);
    
    $context->assertIsSatisfied();
  }
  
  // -- Private helpers
  
  protected function _createEntity($headers, $encoder, $cache)
  {
    return $this->_createAttachment($headers, $encoder, $cache);
  }
  
  protected function _createAttachment($headers, $encoder, $cache)
  {
    return new Swift_Mime_Attachment($headers, $encoder, $cache);
  }
  
}
