<?php

require_once 'Swift/Mime/Attachment.php';
require_once 'Swift/Mime/Header/UnstructuredHeader.php';
require_once 'Swift/Mime/Header/ParameterizedHeader.php';
require_once 'Swift/Encoder/Rfc2231Encoder.php';
require_once 'Swift/Mime/ContentEncoder/Base64ContentEncoder.php';
require_once 'Swift/Mime/HeaderEncoder/QpHeaderEncoder.php';
require_once 'Swift/CharacterStream/ArrayCharacterStream.php';
require_once 'Swift/CharacterReaderFactory/SimpleCharacterReaderFactory.php';
require_once 'Swift/KeyCache/ArrayKeyCache.php';
require_once 'Swift/KeyCache/SimpleKeyCacheInputStream.php';

class Swift_Mime_AttachmentAcceptanceTest extends UnitTestCase
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
  
  public function testDispositionIsSetInHeader()
  {
    $attachment = $this->_createAttachment();
    $attachment->setContentType('application/pdf');
    $attachment->setDisposition('inline');
    $this->assertEqual(
      'Content-Type: application/pdf' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n" .
      'Content-Disposition: inline' . "\r\n",
      $attachment->toString()
      );
  }
  
  public function testDispositionIsAttachmentByDefault()
  {
    $attachment = $this->_createAttachment();
    $attachment->setContentType('application/pdf');
    $this->assertEqual(
      'Content-Type: application/pdf' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n" .
      'Content-Disposition: attachment' . "\r\n",
      $attachment->toString()
      );
  }
  
  public function testFilenameIsSetInHeader()
  {
    $attachment = $this->_createAttachment();
    $attachment->setContentType('application/pdf');
    $attachment->setFilename('foo.pdf');
    $this->assertEqual(
      'Content-Type: application/pdf' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n" .
      'Content-Disposition: attachment; filename=foo.pdf' . "\r\n",
      $attachment->toString()
      );
  }
  
  public function testCreationDateIsSetInHeader()
  {
    $date = time();
    $attachment = $this->_createAttachment();
    $attachment->setContentType('application/pdf');
    $attachment->setCreationDate($date);
    $this->assertEqual(
      'Content-Type: application/pdf' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n" .
      'Content-Disposition: attachment;' . "\r\n" .
      ' creation-date="' . date('r', $date) . '"' . "\r\n",
      $attachment->toString()
      );
  }
  
  public function testModificationDateIsSetInHeader()
  {
    $date = time();
    $attachment = $this->_createAttachment();
    $attachment->setContentType('application/pdf');
    $attachment->setModificationDate($date);
    $this->assertEqual(
      'Content-Type: application/pdf' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n" .
      'Content-Disposition: attachment;' . "\r\n" .
      ' modification-date="' . date('r', $date) . '"' . "\r\n",
      $attachment->toString()
      );
  }
  
  public function testReadDateIsSetInHeader()
  {
    $date = time();
    $attachment = $this->_createAttachment();
    $attachment->setContentType('application/pdf');
    $attachment->setReadDate($date);
    $this->assertEqual(
      'Content-Type: application/pdf' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n" .
      'Content-Disposition: attachment; read-date="' . date('r', $date) . '"' . "\r\n",
      $attachment->toString()
      );
  }
  
  public function testSizeIsSetInHeader()
  {
    $attachment = $this->_createAttachment();
    $attachment->setContentType('application/pdf');
    $attachment->setSize(12340);
    $this->assertEqual(
      'Content-Type: application/pdf' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n" .
      'Content-Disposition: attachment; size=12340' . "\r\n",
      $attachment->toString()
      );
  }
  
  public function testMultipleParametersInHeader()
  {
    $attachment = $this->_createAttachment();
    $attachment->setContentType('application/pdf');
    $attachment->setFilename('foo.pdf');
    $attachment->setSize(12340);
    $attachment->setCreationDate(123);
    $attachment->setModificationDate(1234);
    $attachment->setReadDate(12345);
    $this->assertEqual(
      'Content-Type: application/pdf' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n" .
      'Content-Disposition: attachment; filename=foo.pdf; size=12340;' . "\r\n" .
      ' creation-date="' . date('r', 123) . '";' . "\r\n" .
      ' modification-date="' . date('r', 1234) . '";' . "\r\n" .
      ' read-date="' . date('r', 12345) . '"' . "\r\n",
      $attachment->toString()
      );
  }
  
  public function testEndToEnd()
  {
    $attachment = $this->_createAttachment();
    $attachment->setContentType('application/pdf');
    $attachment->setFilename('foo.pdf');
    $attachment->setSize(12340);
    $attachment->setCreationDate(123);
    $attachment->setModificationDate(1234);
    $attachment->setReadDate(12345);
    $attachment->setBodyAsString('abcd');
    $this->assertEqual(
      'Content-Type: application/pdf' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n" .
      'Content-Disposition: attachment; filename=foo.pdf; size=12340;' . "\r\n" .
      ' creation-date="' . date('r', 123) . '";' . "\r\n" .
      ' modification-date="' . date('r', 1234) . '";' . "\r\n" .
      ' read-date="' . date('r', 12345) . '"' . "\r\n" .
      "\r\n" .
      base64_encode('abcd'),
      $attachment->toString()
      );
  }
  
  // -- Private helpers
  
  private function _createAttachment()
  {
    $entity = new Swift_Mime_Attachment(
      array(
        new Swift_Mime_Header_ParameterizedHeader(
          'Content-Type', $this->_headerEncoder, $this->_paramEncoder
          ),
        new Swift_Mime_Header_UnstructuredHeader(
          'Content-Transfer-Encoding', $this->_headerEncoder
          ),
        new Swift_Mime_Header_ParameterizedHeader(
          'Content-Disposition', $this->_headerEncoder, $this->_paramEncoder
          )
        ),
      $this->_contentEncoder,
      $this->_cache
      );
    return $entity;
  }
  
}
