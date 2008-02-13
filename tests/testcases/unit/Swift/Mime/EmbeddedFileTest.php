<?php

require_once 'Swift/Mime/MimeEntity.php';
require_once 'Swift/Mime/EmbeddedFile.php';
require_once 'Swift/AbstractSwiftUnitTestCase.php';
require_once 'Swift/Mime/ContentEncoder.php';
require_once 'Swift/Mime/Header.php';
require_once 'Swift/Mime/FieldChangeObserver.php';
require_once 'Swift/FileStream.php';
require_once 'Swift/KeyCache.php';

Mock::generate('Swift_Mime_ContentEncoder', 'Swift_Mime_MockContentEncoder');
Mock::generate('Swift_Mime_Header', 'Swift_Mime_MockHeader');
Mock::generate('Swift_Mime_FieldChangeObserver',
  'Swift_Mime_MockFieldChangeObserver'
  );
Mock::generate('Swift_FileStream', 'Swift_MockFileStream');
Mock::generate('Swift_KeyCache', 'Swift_MockKeyCache');

class Swift_Mime_EmbeddedFileTest extends Swift_AbstractSwiftUnitTestCase
{
  private $_encoder;
  private $_cache;
  
  public function setUp()
  {
    $this->_cache = new Swift_MockKeyCache();
    $this->_encoder = new Swift_Mime_MockContentEncoder();
    $this->_encoder->setReturnValue('getName', 'base64');
  }
  
  public function testNestingLevelIsEmbedded()
  {
    $file = $this->_createEmbeddedFile(array(), $this->_encoder, $this->_cache);
    $this->assertEqual(
      Swift_Mime_MimeEntity::LEVEL_EMBEDDED, $file->getNestingLevel()
      );
  }
  
  public function testDispositionCanBeSetAndFetched()
  {
    /* -- RFC 2183, 2.1, 2.2.
     */
    
    $file = $this->_createEmbeddedFile(array(), $this->_encoder, $this->_cache);
    $file->setDisposition('attachment');
    $this->assertEqual('attachment', $file->getDisposition());
  }
  
  public function testSettingDispositionNotifiesFieldChangeObserver()
  {
    $observer1 = new Swift_Mime_MockFieldChangeObserver();
    $observer1->expectOnce('fieldChanged', array('disposition', 'attachment'));
    $observer2 = new Swift_Mime_MockFieldChangeObserver();
    $observer2->expectOnce('fieldChanged', array('disposition', 'attachment'));

    $file = $this->_createEmbeddedFile(array(), $this->_encoder, $this->_cache);

    $file->registerFieldChangeObserver($observer1);
    $file->registerFieldChangeObserver($observer2);

    $file->setDisposition('attachment');
  }
  
  public function testDefaultDispositionIsInline()
  {
    $file = $this->_createEmbeddedFile(array(), $this->_encoder, $this->_cache);
    $this->assertEqual('inline', $file->getDisposition());
  }
  
  public function testFilenameCanBeSetAndFetched()
  {
    /* -- RFC 2183, 2.3.
     */
    
    $file = $this->_createEmbeddedFile(array(), $this->_encoder, $this->_cache);
    $file->setFilename('some-file.pdf');
    $this->assertEqual('some-file.pdf', $file->getFilename());
  }
  
  public function testSettingFilenameNotifiesFieldChangeObserver()
  {
    $observer1 = new Swift_Mime_MockFieldChangeObserver();
    $observer1->expectOnce('fieldChanged', array('filename', 'foo.bar'));
    $observer2 = new Swift_Mime_MockFieldChangeObserver();
    $observer2->expectOnce('fieldChanged', array('filename', 'foo.bar'));

    $file = $this->_createEmbeddedFile(array(), $this->_encoder, $this->_cache);

    $file->registerFieldChangeObserver($observer1);
    $file->registerFieldChangeObserver($observer2);

    $file->setFilename('foo.bar');
  }
  
  public function testCreationDateCanBeSetAndFetched()
  {
    /* -- RFC 2183, 2.4.
     */
    
    $date = time();
    $file = $this->_createEmbeddedFile(array(), $this->_encoder, $this->_cache);
    $file->setCreationDate($date);
    $this->assertEqual($date, $file->getCreationDate());
  }
  
  public function testSettingCreationDateNotifiesFieldChangeObserver()
  {
    $date = time();
    
    $observer1 = new Swift_Mime_MockFieldChangeObserver();
    $observer1->expectOnce('fieldChanged', array('creationdate', $date));
    $observer2 = new Swift_Mime_MockFieldChangeObserver();
    $observer2->expectOnce('fieldChanged', array('creationdate', $date));

    $file = $this->_createEmbeddedFile(array(), $this->_encoder, $this->_cache);

    $file->registerFieldChangeObserver($observer1);
    $file->registerFieldChangeObserver($observer2);

    $file->setCreationDate($date);
  }
  
  public function testModificationDateCanBeSetAndFetched()
  {
    /* -- RFC 2183, 2.5.
     */
    
    $date = time();
    $file = $this->_createEmbeddedFile(array(), $this->_encoder, $this->_cache);
    $file->setModificationDate($date);
    $this->assertEqual($date, $file->getModificationDate());
  }
  
  public function testSettingModificationDateNotifiesFieldChangeObserver()
  {
    $date = time();
    
    $observer1 = new Swift_Mime_MockFieldChangeObserver();
    $observer1->expectOnce('fieldChanged', array('modificationdate', $date));
    $observer2 = new Swift_Mime_MockFieldChangeObserver();
    $observer2->expectOnce('fieldChanged', array('modificationdate', $date));

    $file = $this->_createEmbeddedFile(array(), $this->_encoder, $this->_cache);

    $file->registerFieldChangeObserver($observer1);
    $file->registerFieldChangeObserver($observer2);

    $file->setModificationDate($date);
  }
  
  public function testReadDateCanBeSetAndFetched()
  {
    /* -- RFC 2183, 2.6.
     */
    
    $date = time();
    $file = $this->_createEmbeddedFile(array(), $this->_encoder, $this->_cache);
    $file->setReadDate($date);
    $this->assertEqual($date, $file->getReadDate());
  }
  
  public function testSettingReadDateNotifiesFieldChangeObserver()
  {
    $date = time();
    
    $observer1 = new Swift_Mime_MockFieldChangeObserver();
    $observer1->expectOnce('fieldChanged', array('readdate', $date));
    $observer2 = new Swift_Mime_MockFieldChangeObserver();
    $observer2->expectOnce('fieldChanged', array('readdate', $date));

    $file = $this->_createEmbeddedFile(array(), $this->_encoder, $this->_cache);

    $file->registerFieldChangeObserver($observer1);
    $file->registerFieldChangeObserver($observer2);

    $file->setReadDate($date);
  }
  
  public function testSizeCanBeSetAndFetched()
  {
    /* -- RFC 2183, 2.7.
     */
    
    $file = $this->_createEmbeddedFile(array(), $this->_encoder, $this->_cache);
    $file->setSize(123456);
    $this->assertEqual(123456, $file->getSize());
  }
  
  public function testSettingSizeNotifiesFieldChangeObserver()
  {
    $observer1 = new Swift_Mime_MockFieldChangeObserver();
    $observer1->expectOnce('fieldChanged', array('size', 123456));
    $observer2 = new Swift_Mime_MockFieldChangeObserver();
    $observer2->expectOnce('fieldChanged', array('size', 123456));

    $file = $this->_createEmbeddedFile(array(), $this->_encoder, $this->_cache);

    $file->registerFieldChangeObserver($observer1);
    $file->registerFieldChangeObserver($observer2);

    $file->setSize(123456);
  }
  
  public function testFluidInterface()
  {
    $file = $this->_createEmbeddedFile(array(), $this->_encoder, $this->_cache);
    $ref = $file
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
      ->setDisposition('inline')
      ->setFilename('afile.txt')
      ->setCreationDate(time())
      ->setModificationDate(time() + 10)
      ->setReadDate(time() + 20)
      ->setSize(123)
      ->setFile(new Swift_MockFileStream())
      ;
    
    $this->assertReference($file, $ref);
  }
  
  // -- Private helpers
  
  private function _createEmbeddedFile($headers, $encoder, $cache)
  {
    return new Swift_Mime_EmbeddedFile($headers, $encoder, $cache);
  }
  
}
