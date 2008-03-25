<?php

require_once 'Swift/Mime/MimePart.php';
require_once 'Swift/Mime/Headers/UnstructuredHeader.php';
require_once 'Swift/Mime/Headers/ParameterizedHeader.php';
require_once 'Swift/Encoder/Rfc2231Encoder.php';
require_once 'Swift/Mime/ContentEncoder/QpContentEncoder.php';
require_once 'Swift/Mime/HeaderEncoder/QpHeaderEncoder.php';
require_once 'Swift/CharacterStream/ArrayCharacterStream.php';
require_once 'Swift/CharacterReaderFactory/SimpleCharacterReaderFactory.php';
require_once 'Swift/KeyCache/ArrayKeyCache.php';
require_once 'Swift/KeyCache/SimpleKeyCacheInputStream.php';

class Swift_Mime_MimePartAcceptanceTest extends UnitTestCase
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
    $this->_contentEncoder = new Swift_Mime_ContentEncoder_QpContentEncoder(
      new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8'), true
      );
    $this->_headerEncoder = new Swift_Mime_HeaderEncoder_QpHeaderEncoder(
      new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8')
      );
    $this->_paramEncoder = new Swift_Encoder_Rfc2231Encoder(
      new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8')
      );
  }
  
  public function testCharsetIsSetInHeader()
  {
    $part = $this->_createMimePart();
    $part->setContentType('text/plain');
    $part->setCharset('utf-8');
    $part->setBodyAsString('foobar');
    $this->assertEqual(
      'Content-Type: text/plain; charset=utf-8' . "\r\n" .
      'Content-Transfer-Encoding: quoted-printable' . "\r\n" .
      "\r\n" .
      'foobar',
      $part->toString()
      );
  }
  
  public function testFormatIsSetInHeaders()
  {
    $part = $this->_createMimePart();
    $part->setContentType('text/plain');
    $part->setFormat('flowed');
    $part->setBodyAsString('> foobar');
    $this->assertEqual(
      'Content-Type: text/plain; format=flowed' . "\r\n" .
      'Content-Transfer-Encoding: quoted-printable' . "\r\n" .
      "\r\n" .
      '> foobar',
      $part->toString()
      );
  }
  
  public function testDelSpIsSetInHeaders()
  {
    $part = $this->_createMimePart();
    $part->setContentType('text/plain');
    $part->setDelSp(true);
    $part->setBodyAsString('foobar');
    $this->assertEqual(
      'Content-Type: text/plain; delsp=yes' . "\r\n" .
      'Content-Transfer-Encoding: quoted-printable' . "\r\n" .
      "\r\n" .
      'foobar',
      $part->toString()
      );
  }
  
  public function testAll3ParamsInHeaders()
  {
    $part = $this->_createMimePart();
    $part->setContentType('text/plain');
    $part->setCharset('utf-8');
    $part->setFormat('fixed');
    $part->setDelSp(true);
    $part->setBodyAsString('foobar');
    $this->assertEqual(
      'Content-Type: text/plain; charset=utf-8; format=fixed; delsp=yes' . "\r\n" .
      'Content-Transfer-Encoding: quoted-printable' . "\r\n" .
      "\r\n" .
      'foobar',
      $part->toString()
      );
  }
  
  public function testBodyIsCanonicalized()
  {
    $part = $this->_createMimePart();
    $part->setContentType('text/plain');
    $part->setCharset('utf-8');
    $part->setBodyAsString("foobar\r\rtest\ning\r");
    $this->assertEqual(
      'Content-Type: text/plain; charset=utf-8' . "\r\n" .
      'Content-Transfer-Encoding: quoted-printable' . "\r\n" .
      "\r\n" .
      "foobar\r\n" .
      "\r\n" .
      "test\r\n" .
      "ing\r\n",
      $part->toString()
      );
  }
  
  // -- Private helpers
  
  private function _createMimePart()
  {
    $entity = new Swift_Mime_MimePart(
      array(
        new Swift_Mime_Headers_ParameterizedHeader(
          'Content-Type', $this->_headerEncoder, $this->_paramEncoder
          ),
        new Swift_Mime_Headers_UnstructuredHeader(
          'Content-Transfer-Encoding', $this->_headerEncoder
          )
        ),
      $this->_contentEncoder,
      $this->_cache
      );
    return $entity;
  }
  
}
