<?php

require_once 'Swift/Mime/EmbeddedFile.php';
require_once 'Swift/Mime/Header/UnstructuredHeader.php';
require_once 'Swift/Mime/Header/ParameterizedHeader.php';
require_once 'Swift/Mime/Header/IdentificationHeader.php';
require_once 'Swift/Encoder/Rfc2231Encoder.php';
require_once 'Swift/Mime/ContentEncoder/Base64ContentEncoder.php';
require_once 'Swift/Mime/HeaderEncoder/QpHeaderEncoder.php';
require_once 'Swift/CharacterStream/ArrayCharacterStream.php';
require_once 'Swift/CharacterReaderFactory/SimpleCharacterReaderFactory.php';
require_once 'Swift/KeyCache/ArrayKeyCache.php';
require_once 'Swift/KeyCache/SimpleKeyCacheInputStream.php';

class Swift_Mime_EmbeddedFileAcceptanceTest extends UnitTestCase
{

  private $_contentEncoder;
  private $_headerEncoder;
  private $_paramEncoder;
  private $_cache;
  
  public function setUp()
  {
    $this->_cache = new Swift_KeyCache_ArrayKeyCache(
      new Swift_KeyCache_SimpleKeyCacheInputStream()
      );
    $factory = new Swift_CharacterReaderFactory_SimpleCharacterReaderFactory();
    $this->_contentEncoder = new Swift_Mime_ContentEncoder_Base64ContentEncoder();
    $this->_headerEncoder = new Swift_Mime_HeaderEncoder_QpHeaderEncoder(
      new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8')
      );
    $this->_paramEncoder = new Swift_Encoder_Rfc2231Encoder(
      new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8')
      );
  }
  
  public function testContentIdIsSetInHeader()
  {
    $file = $this->_createEmbeddedFile();
    $file->setContentType('application/pdf');
    $file->setId('foo@bar');
    $this->assertEqual(
      'Content-Type: application/pdf' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n" .
      'Content-Disposition: inline' . "\r\n" .
      'Content-ID: <foo@bar>' . "\r\n",
      $file->toString()
      );
  }
  
  public function testDispositionIsSetInHeader()
  {
    $file = $this->_createEmbeddedFile();
    $id = $file->getId();
    $file->setContentType('application/pdf');
    $file->setDisposition('attachment');
    $this->assertEqual(
      'Content-Type: application/pdf' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n" .
      'Content-Disposition: attachment' . "\r\n" .
      'Content-ID: <'. $id . '>' . "\r\n",
      $file->toString()
      );
  }
  
  public function testFilenameIsSetInHeader()
  {
    $file = $this->_createEmbeddedFile();
    $id = $file->getId();
    $file->setContentType('application/pdf');
    $file->setFilename('foo.pdf');
    $this->assertEqual(
      'Content-Type: application/pdf; name=foo.pdf' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n" .
      'Content-Disposition: inline; filename=foo.pdf' . "\r\n" .
      'Content-ID: <'. $id . '>' . "\r\n",
      $file->toString()
      );
  }
  
  public function testCreationDateIsSetInHeader()
  {
    $date = time();
    $file = $this->_createEmbeddedFile();
    $id = $file->getId();
    $file->setContentType('application/pdf');
    $file->setCreationDate($date);
    $this->assertEqual(
      'Content-Type: application/pdf' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n" .
      'Content-Disposition: inline; creation-date="' . date('r', $date) . '"' . "\r\n" .
      'Content-ID: <'. $id . '>' . "\r\n",
      $file->toString()
      );
  }
  
  public function testModificationDateIsSetInHeader()
  {
    $date = time();
    $file = $this->_createEmbeddedFile();
    $id = $file->getId();
    $file->setContentType('application/pdf');
    $file->setModificationDate($date);
    $this->assertEqual(
      'Content-Type: application/pdf' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n" .
      'Content-Disposition: inline;' . "\r\n" .
      ' modification-date="' . date('r', $date) . '"' . "\r\n" .
      'Content-ID: <'. $id . '>' . "\r\n",
      $file->toString()
      );
  }
  
  public function testReadDateIsSetInHeader()
  {
    $date = time();
    $file = $this->_createEmbeddedFile();
    $id = $file->getId();
    $file->setContentType('application/pdf');
    $file->setReadDate($date);
    $this->assertEqual(
      'Content-Type: application/pdf' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n" .
      'Content-Disposition: inline; read-date="' . date('r', $date) . '"' . "\r\n" .
      'Content-ID: <'. $id . '>' . "\r\n",
      $file->toString()
      );
  }
  
  public function testSizeIsSetInHeader()
  {
    $file = $this->_createEmbeddedFile();
    $id = $file->getId();
    $file->setContentType('application/pdf');
    $file->setSize(12340);
    $this->assertEqual(
      'Content-Type: application/pdf' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n" .
      'Content-Disposition: inline; size=12340' . "\r\n" .
      'Content-ID: <'. $id . '>' . "\r\n",
      $file->toString()
      );
  }
  
  public function testMultipleParametersInHeader()
  {
    $file = $this->_createEmbeddedFile();
    $id = $file->getId();
    $file->setContentType('application/pdf');
    $file->setFilename('foo.pdf');
    $file->setSize(12340);
    $file->setCreationDate(123);
    $file->setModificationDate(1234);
    $file->setReadDate(12345);
    $this->assertEqual(
      'Content-Type: application/pdf; name=foo.pdf' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n" .
      'Content-Disposition: inline; filename=foo.pdf; size=12340;' . "\r\n" .
      ' creation-date="' . date('r', 123) . '";' . "\r\n" .
      ' modification-date="' . date('r', 1234) . '";' . "\r\n" .
      ' read-date="' . date('r', 12345) . '"' . "\r\n" .
      'Content-ID: <'. $id . '>' . "\r\n",
      $file->toString()
      );
  }
  
  public function testEndToEnd()
  {
    $file = $this->_createEmbeddedFile();
    $id = $file->getId();
    $file->setContentType('application/pdf');
    $file->setFilename('foo.pdf');
    $file->setSize(12340);
    $file->setCreationDate(123);
    $file->setModificationDate(1234);
    $file->setReadDate(12345);
    $file->setBodyAsString('abcd');
    $this->assertEqual(
      'Content-Type: application/pdf; name=foo.pdf' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n" .
      'Content-Disposition: inline; filename=foo.pdf; size=12340;' . "\r\n" .
      ' creation-date="' . date('r', 123) . '";' . "\r\n" .
      ' modification-date="' . date('r', 1234) . '";' . "\r\n" .
      ' read-date="' . date('r', 12345) . '"' . "\r\n" .
      'Content-ID: <'. $id . '>' . "\r\n" .
      "\r\n" .
      base64_encode('abcd'),
      $file->toString()
      );
  }
  
  // -- Private helpers
  
  private function _createEmbeddedFile()
  {
    $entity = new Swift_Mime_EmbeddedFile(
      array(
        new Swift_Mime_Header_ParameterizedHeader(
          'Content-Type', $this->_headerEncoder, null
          ),
        new Swift_Mime_Header_UnstructuredHeader(
          'Content-Transfer-Encoding', $this->_headerEncoder
          ),
        new Swift_Mime_Header_ParameterizedHeader(
          'Content-Disposition', $this->_headerEncoder, $this->_paramEncoder
          ),
        new Swift_Mime_Header_IdentificationHeader('Content-ID')
        ),
      $this->_contentEncoder,
      $this->_cache
      );
    return $entity;
  }
  
}
