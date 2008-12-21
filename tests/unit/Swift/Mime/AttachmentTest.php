<?php

require_once 'Swift/Mime/MimeEntity.php';
require_once 'Swift/Mime/Attachment.php';
require_once 'Swift/Mime/AbstractMimeEntityTest.php';
require_once 'Swift/FileStream.php';

class Swift_Mime_AttachmentTest extends Swift_Mime_AbstractMimeEntityTest
{
  
  public function testNestingLevelIsAttachment()
  {
    $attachment = $this->_createAttachment($this->_createHeaderSet(),
      $this->_createEncoder(), $this->_createCache()
      );
    $this->assertEqual(
      Swift_Mime_MimeEntity::LEVEL_MIXED, $attachment->getNestingLevel()
      );
  }
  
  public function testDispositionIsReturnedFromHeader()
  {
    /* -- RFC 2183, 2.1, 2.2.
     */
    
    $disposition = $this->_createHeader('Content-Disposition', 'attachment');
    $attachment = $this->_createAttachment($this->_createHeaderSet(array(
      'Content-Disposition' => $disposition)),
      $this->_createEncoder(), $this->_createCache()
      );
    $this->assertEqual('attachment', $attachment->getDisposition());
  }
  
  public function testDispositionIsSetInHeader()
  {
    $disposition = $this->_createHeader('Content-Disposition', 'attachment',
      array(), false
      );
    $this->_checking(Expectations::create()
      -> one($disposition)->setFieldBodyModel('inline')
      -> ignoring($disposition)
      );
    $attachment = $this->_createAttachment($this->_createHeaderSet(array(
      'Content-Disposition' => $disposition)),
      $this->_createEncoder(), $this->_createCache()
      );
    $attachment->setDisposition('inline');
  }
  
  public function testDispositionIsAddedIfNonePresent()
  {
    $headers = $this->_createHeaderSet(array(), false);
    $this->_checking(Expectations::create()
      -> one($headers)->addParameterizedHeader('Content-Disposition', 'inline')
      -> ignoring($headers)
      );
    $attachment = $this->_createAttachment($headers, $this->_createEncoder(),
      $this->_createCache()
      );
    $attachment->setDisposition('inline');
  }
  
  public function testDispositionIsAutoDefaultedToAttachment()
  {
    $headers = $this->_createHeaderSet(array(), false);
    $this->_checking(Expectations::create()
      -> one($headers)->addParameterizedHeader('Content-Disposition', 'attachment')
      -> ignoring($headers)
      );
    $attachment = $this->_createAttachment($headers, $this->_createEncoder(),
      $this->_createCache()
      );
  }
  
  public function testDefaultContentTypeInitializedToOctetStream()
  {
    $cType = $this->_createHeader('Content-Type', '',
      array(), false
      );
    $this->_checking(Expectations::create()
      -> one($cType)->setFieldBodyModel('application/octet-stream')
      -> ignoring($cType)
      );
    $attachment = $this->_createAttachment($this->_createHeaderSet(array(
      'Content-Type' => $cType)),
      $this->_createEncoder(), $this->_createCache()
      );
  }
  
  public function testFilenameIsReturnedFromHeader()
  {
    /* -- RFC 2183, 2.3.
     */
    
    $disposition = $this->_createHeader('Content-Disposition', 'attachment',
      array('filename'=>'foo.txt')
      );
    $attachment = $this->_createAttachment($this->_createHeaderSet(array(
      'Content-Disposition' => $disposition)),
      $this->_createEncoder(), $this->_createCache()
      );
    $this->assertEqual('foo.txt', $attachment->getFilename());
  }
  
  public function testFilenameIsSetInHeader()
  {
    $disposition = $this->_createHeader('Content-Disposition', 'attachment',
      array('filename'=>'foo.txt'), false
      );
    $this->_checking(Expectations::create()
      -> one($disposition)->setParameter('filename', 'bar.txt')
      -> ignoring($disposition)
      );
    $attachment = $this->_createAttachment($this->_createHeaderSet(array(
      'Content-Disposition' => $disposition)),
      $this->_createEncoder(), $this->_createCache()
      );
    $attachment->setFilename('bar.txt');
  }
  
  public function testSettingFilenameSetsNameInContentType()
  {
    /*
     This is a legacy requirement which isn't covered by up-to-date RFCs.
     */
    
    $cType = $this->_createHeader('Content-Type', 'text/plain',
      array(), false
      );
    $this->_checking(Expectations::create()
      -> one($cType)->setParameter('name', 'bar.txt')
      -> ignoring($cType)
      );
    $attachment = $this->_createAttachment($this->_createHeaderSet(array(
      'Content-Type' => $cType)),
      $this->_createEncoder(), $this->_createCache()
      );
    $attachment->setFilename('bar.txt');
  }
  
  public function testSizeIsReturnedFromHeader()
  {
    /* -- RFC 2183, 2.7.
     */
    
    $disposition = $this->_createHeader('Content-Disposition', 'attachment',
      array('size'=>1234)
      );
    $attachment = $this->_createAttachment($this->_createHeaderSet(array(
      'Content-Disposition' => $disposition)),
      $this->_createEncoder(), $this->_createCache()
      );
    $this->assertEqual(1234, $attachment->getSize());
  }
  
  public function testSizeIsSetInHeader()
  {
    $disposition = $this->_createHeader('Content-Disposition', 'attachment',
      array(), false
      );
    $this->_checking(Expectations::create()
      -> one($disposition)->setParameter('size', 12345)
      -> ignoring($disposition)
      );
    $attachment = $this->_createAttachment($this->_createHeaderSet(array(
      'Content-Disposition' => $disposition)),
      $this->_createEncoder(), $this->_createCache()
      );
    $attachment->setSize(12345);
  }
  
  public function testFilnameCanBeReadFromFileStream()
  {
    $file = $this->_createFileStream('/bar/file.ext', '');
    $disposition = $this->_createHeader('Content-Disposition', 'attachment',
      array('filename'=>'foo.txt'), false
      );
    $this->_checking(Expectations::create()
      -> one($disposition)->setParameter('filename', 'file.ext')
      -> ignoring($disposition)
      );
    $attachment = $this->_createAttachment($this->_createHeaderSet(array(
      'Content-Disposition' => $disposition)),
      $this->_createEncoder(), $this->_createCache()
      );
    $attachment->setFile($file);
  }
  
  public function testContentTypeCanBeSetViaSetFile()
  {
    $file = $this->_createFileStream('/bar/file.ext', '');
    $disposition = $this->_createHeader('Content-Disposition', 'attachment',
      array('filename'=>'foo.txt'), false
      );
    $ctype = $this->_createHeader('Content-Type', 'text/plain', array(), false);
    $headers = $this->_createHeaderSet(array(
      'Content-Disposition' => $disposition,
      'Content-Type' => $ctype
      ));
    $this->_checking(Expectations::create()
      -> one($disposition)->setParameter('filename', 'file.ext')
      -> one($ctype)->setFieldBodyModel('text/html')
      -> ignoring($disposition)
      -> ignoring($ctype)
      );
    $attachment = $this->_createAttachment($headers, $this->_createEncoder(),
      $this->_createCache()
      );
    $attachment->setFile($file, 'text/html');
  }
  
  public function XtestContentTypeCanBeLookedUpFromCommonListIfNotProvided()
  {
    $file = $this->_createFileStream('/bar/file.zip', '');
    $disposition = $this->_createHeader('Content-Disposition', 'attachment',
      array('filename'=>'foo.zip'), false
      );
    $ctype = $this->_createHeader('Content-Type', 'text/plain', array(), false);
    $headers = $this->_createHeaderSet(array(
      'Content-Disposition' => $disposition,
      'Content-Type' => $ctype
      ));
    $this->_checking(Expectations::create()
      -> one($disposition)->setParameter('filename', 'file.zip')
      -> one($ctype)->setFieldBodyModel('application/zip')
      -> ignoring($disposition)
      -> ignoring($ctype)
      );
    $attachment = $this->_createAttachment($headers, $this->_createEncoder(),
      $this->_createCache(), array('zip'=>'application/zip', 'txt'=>'text/plain')
      );
    $attachment->setFile($file);
  }
  
  public function testDataCanBeReadFromFile()
  {
    $file = $this->_createFileStream('/foo/file.ext', '<some data>');
    $attachment = $this->_createAttachment($this->_createHeaderSet(),
      $this->_createEncoder(), $this->_createCache()
      );
    $attachment->setFile($file);
    $this->assertEqual('<some data>', $attachment->getBody());
  }
  
  public function testFluidInterface()
  {
    $attachment = $this->_createAttachment($this->_createHeaderSet(),
      $this->_createEncoder(), $this->_createCache()
      );
    $this->assertSame($attachment,
      $attachment
      ->setContentType('application/pdf')
      ->setEncoder($this->_createEncoder())
      ->setId('foo@bar')
      ->setDescription('my pdf')
      ->setMaxLineLength(998)
      ->setBody('xx')
      ->setBoundary('xyz')
      ->setChildren(array())
      ->setDisposition('inline')
      ->setFilename('afile.txt')
      ->setSize(123)
      ->setFile($this->_createFileStream('foo.txt', ''))
      );
  }
  
  // -- Private helpers
  
  protected function _createEntity($headers, $encoder, $cache)
  {
    return $this->_createAttachment($headers, $encoder, $cache);
  }
  
  protected function _createAttachment($headers, $encoder, $cache,
    $mimeTypes = array())
  {
    return new Swift_Mime_Attachment($headers, $encoder, $cache, $mimeTypes);
  }
  
  protected function _createFileStream($path, $data, $stub = true)
  {
    $file = $this->_mock('Swift_FileStream');
    $pos = $this->_mockery()->states('position')->startsAs('at start');
    $this->_checking(Expectations::create()
      -> ignoring($file)->getPath() -> returns($path)
      -> ignoring($file)->read(optional()) -> returns($data)
        -> when($pos->isNot('at end')) -> then($pos->is('at end'))
      -> ignoring($file)->read(optional()) -> returns(false)
      );
    if ($stub)
    {
      $this->_checking(Expectations::create()
        -> ignoring($file)
        );
    }
    return $file;
  }
  
}
