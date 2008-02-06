<?php

require_once 'Swift/Mime/SimpleMimeEntity.php';
require_once 'Swift/Mime/Header/UnstructuredHeader.php';
require_once 'Swift/Mime/Header/ParameterizedHeader.php';
require_once 'Swift/Mime/Header/IdentificationHeader.php';
require_once 'Swift/Encoder/Rfc2231Encoder.php';
require_once 'Swift/Mime/ContentEncoder/QpContentEncoder.php';
require_once 'Swift/Mime/ContentEncoder/Base64ContentEncoder.php';
require_once 'Swift/Mime/ContentEncoder/PlainContentEncoder.php';
require_once 'Swift/Mime/HeaderEncoder/QpHeaderEncoder.php';
require_once 'Swift/CharacterStream/ArrayCharacterStream.php';
require_once 'Swift/CharacterReaderFactory/SimpleCharacterReaderFactory.php';

class Swift_Mime_SimpleMimeEntityAcceptanceTest extends UnitTestCase
{

  private $_contentEncoder;
  private $_headerEncoder;
  private $_paramEncoder;
  
  public function setUp()
  {
    $factory = new Swift_CharacterReaderFactory_SimpleCharacterReaderFactory();
    $this->_contentEncoder = new Swift_Mime_ContentEncoder_QpContentEncoder(
      new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8')
      );
    $this->_headerEncoder = new Swift_Mime_HeaderEncoder_QpHeaderEncoder(
      new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8')
      );
    $this->_paramEncoder = new Swift_Encoder_Rfc2231Encoder(
      new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8')
      );
  }
  
  public function testContentTypeIsSetInHeader()
  {
    $entity = $this->_createEntity();
    
    foreach (array('text/html', 'text/plain', 'audio/midi') as $type)
    {
      $entity->setContentType($type);
      $this->assertEqual($type, $entity->getContentType());
      $this->assertEqual(
        'Content-Type: ' . $type . "\r\n" .
        'Content-Transfer-Encoding: quoted-printable' . "\r\n",
        $entity->toString()
        );
    }
  }
  
  public function testEncodingIsSetInHeader()
  {
    $entity = $this->_createEntity();
    
    $entity->setEncoder(new Swift_Mime_ContentEncoder_Base64ContentEncoder());
    $entity->setContentType('text/plain');
    $this->assertEqual(
      'Content-Type: text/plain' . "\r\n" .
      'Content-Transfer-Encoding: base64' . "\r\n",
      $entity->toString()
      );
  }
  
  public function testBodyIsEncoded()
  {
    $entity = $this->_createEntity();
    $entity->setContentType('text/plain');
    $entity->setBodyAsString(
      'Just s' . pack('C*', 0xC2, 0x01, 0x01) . 'me multi-' . "\r\n" .
      'line message!'
      );
    
    $this->assertEqual(
      'Content-Type: text/plain' . "\r\n" .
      'Content-Transfer-Encoding: quoted-printable' . "\r\n" .
      "\r\n" .
      'Just s=C2=01=01me multi-' . "\r\n" .
      'line message!',
      $entity->toString()
      );
  }
  
  public function testNestingEntityCreatesNestedContent()
  {
    /* -- RFC 2045, 6.4.
    Certain Content-Transfer-Encoding values may only be used on certain
    media types.  In particular, it is EXPRESSLY FORBIDDEN to use any
    encodings other than "7bit", "8bit", or "binary" with any composite
    media type, i.e. one that recursively includes other Content-Type
    fields.  Currently the only composite media types are "multipart" and
    "message".
    */
    
    $entity1 = $this->_createEntity();
    
    $entity2 = $this->_createEntity();
    $entity2->setContentType('text/html');
    $entity2->setBodyAsString('just testing');
    $entity2->setNestingLevel(Swift_Mime_MimeEntity::LEVEL_SUBPART);
    
    $entity1->setChildren(array($entity2));
    
    $boundary = $entity1->getBoundary();
    
    $this->assertTrue($boundary);
    
    $this->assertEqual(
      'Content-Type: multipart/alternative;' . "\r\n" .
      ' boundary="' . $boundary . '"' . "\r\n" .
      "\r\n" .
      '--' . $boundary . "\r\n" .
      'Content-Type: text/html' . "\r\n" .
      'Content-Transfer-Encoding: quoted-printable' . "\r\n" .
      "\r\n" .
      'just testing' .
      "\r\n" .
      '--' . $boundary . '--' . "\r\n",
      $entity1->toString()
      );
  }
  
  public function testNestingTwoEntities()
  {
    $entity1 = $this->_createEntity();
    
    $entity2 = $this->_createEntity();
    $entity2->setContentType('text/html');
    $entity2->setBodyAsString('just testing');
    $entity2->setNestingLevel(Swift_Mime_MimeEntity::LEVEL_SUBPART);
    
    $entity3 = $this->_createEntity();
    $entity3->setContentType('text/plain');
    $entity3->setBodyAsString('just testing again');
    $entity3->setNestingLevel(Swift_Mime_MimeEntity::LEVEL_SUBPART);
    
    $entity1->setChildren(array($entity2, $entity3));
    
    $boundary = $entity1->getBoundary();
    
    $this->assertTrue($boundary);
    
    $this->assertEqual(
      'Content-Type: multipart/alternative;' . "\r\n" .
      ' boundary="' . $boundary . '"' . "\r\n" .
      "\r\n" .
      '--' . $boundary . "\r\n" .
      'Content-Type: text/html' . "\r\n" .
      'Content-Transfer-Encoding: quoted-printable' . "\r\n" .
      "\r\n" .
      'just testing' .
      "\r\n" .
      '--' . $boundary . "\r\n" .
      'Content-Type: text/plain' . "\r\n" .
      'Content-Transfer-Encoding: quoted-printable' . "\r\n" .
      "\r\n" .
      'just testing again' .
      "\r\n" .
      '--' . $boundary . '--' . "\r\n",
      $entity1->toString()
      );
  }
  
  public function testNestingMixedEntitiesProducesHierarchicalNesting()
  {
    $entity1 = $this->_createEntity();
    
    $entity2 = $this->_createEntity();
    $entity2->setContentType('text/html');
    $entity2->setBodyAsString('just testing');
    $entity2->setNestingLevel(Swift_Mime_MimeEntity::LEVEL_SUBPART);
    
    $entity3 = $this->_createEntity();
    $entity3->setContentType('text/plain');
    $entity3->setBodyAsString('just testing again');
    $entity3->setNestingLevel(Swift_Mime_MimeEntity::LEVEL_ATTACHMENT);
    
    $entity1->setChildren(array($entity2, $entity3));
    
    $boundary = $entity1->getBoundary();
    $this->assertTrue($boundary);
    
    $boundary = preg_quote($boundary, '~');
    
    $this->assertPattern(
      '~^Content-Type: multipart/mixed;' . "\r\n" .
      ' boundary="' . $boundary . '"' . "\r\n" .
      "\r\n" .
      '--' . $boundary . "\r\n" .
      'Content-Type: multipart/alternative;' . "\r\n" .
      ' boundary="(.*?)"' . "\r\n" .
      "\r\n" .
      '--\\1' . "\r\n" .
      'Content-Type: text/html' . "\r\n" .
      'Content-Transfer-Encoding: quoted-printable' . "\r\n" .
      "\r\n" .
      'just testing' .
      "\r\n" .
      '--\\1--' . "\r\n" .
      "\r\n" .
      '--' . $boundary . "\r\n" .
      'Content-Type: text/plain' . "\r\n" .
      'Content-Transfer-Encoding: quoted-printable' . "\r\n" .
      "\r\n" .
      'just testing again' .
      "\r\n" .
      '--' . $boundary . '--' . "\r\n" .
      '$~D',
      $entity1->toString()
      );
  }
  
  public function testMixingAllThreeLevels()
  {
    $entity1 = $this->_createEntity();
    
    $entity2 = $this->_createEntity();
    $entity2->setContentType('text/html');
    $entity2->setBodyAsString('just testing');
    $entity2->setNestingLevel(Swift_Mime_MimeEntity::LEVEL_SUBPART);
    
    $entity3 = $this->_createEntity();
    $entity3->setContentType('text/plain');
    $entity3->setBodyAsString('just testing again');
    $entity3->setNestingLevel(Swift_Mime_MimeEntity::LEVEL_ATTACHMENT);
    
    $entity4 = $this->_createEntity();
    $entity4->setContentType('image/jpeg');
    $entity4->setBodyAsString('abcdef123456');
    $entity4->setNestingLevel(Swift_Mime_MimeEntity::LEVEL_EMBEDDED);
    
    $entity1->setChildren(array($entity2, $entity3, $entity4));
    
    $boundary = $entity1->getBoundary();
    $this->assertTrue($boundary);
    
    $boundary = preg_quote($boundary, '~');
    
    $this->assertPattern(
      '~^Content-Type: multipart/mixed;' . "\r\n" .
      ' boundary="' . $boundary . '"' . "\r\n" .
      "\r\n" .
      '--' . $boundary . "\r\n" .
      'Content-Type: multipart/related;' . "\r\n" .
      ' boundary="(.*?)"' . "\r\n" .
      "\r\n" .
      '--\\1' . "\r\n" .
      'Content-Type: multipart/alternative;' . "\r\n" .
      ' boundary="(.*?)"' . "\r\n" .
      "\r\n" .
      '--\\2' . "\r\n" .
      'Content-Type: text/html' . "\r\n" .
      'Content-Transfer-Encoding: quoted-printable' . "\r\n" .
      "\r\n" .
      'just testing' .
      "\r\n" .
      '--\\2--' . "\r\n" .
      "\r\n" .
      '--\\1' . "\r\n" .
      'Content-Type: image/jpeg' . "\r\n" .
      'Content-Transfer-Encoding: quoted-printable' . "\r\n" .
      "\r\n" .
      'abcdef123456' .
      "\r\n" .
      '--\\1--' . "\r\n" .
      "\r\n" .
      '--' . $boundary . "\r\n" .
      'Content-Type: text/plain' . "\r\n" .
      'Content-Transfer-Encoding: quoted-printable' . "\r\n" .
      "\r\n" .
      'just testing again' .
      "\r\n" .
      '--' . $boundary . '--' . "\r\n" .
      '$~D',
      $entity1->toString()
      );
  }
  
  public function test7bitEncodingIsDisplayedForNestedContent()
  {
    /* -- RFC 2045, 6.4.
     */
    
    $entity1 = $this->_createEntity();
    $entity1->setEncoder(
      new Swift_Mime_ContentEncoder_PlainContentEncoder('7bit')
      );
    
    $entity2 = $this->_createEntity();
    $entity2->setContentType('text/html');
    $entity2->setBodyAsString('just testing');
    $entity2->setNestingLevel(Swift_Mime_MimeEntity::LEVEL_SUBPART);
    
    $entity1->setChildren(array($entity2));
    
    $boundary = $entity1->getBoundary();
    
    $this->assertTrue($boundary);
    
    $this->assertEqual(
      'Content-Type: multipart/alternative;' . "\r\n" .
      ' boundary="' . $boundary . '"' . "\r\n" .
      'Content-Transfer-Encoding: 7bit' . "\r\n" .
      "\r\n" .
      '--' . $boundary . "\r\n" .
      'Content-Type: text/html' . "\r\n" .
      'Content-Transfer-Encoding: quoted-printable' . "\r\n" .
      "\r\n" .
      'just testing' .
      "\r\n" .
      '--' . $boundary . '--' . "\r\n",
      $entity1->toString()
      );
  }
  
  public function test8bitEncodingIsDisplayedForNestedContent()
  {
    $entity1 = $this->_createEntity();
    $entity1->setEncoder(
      new Swift_Mime_ContentEncoder_PlainContentEncoder('8bit')
      );
    
    $entity2 = $this->_createEntity();
    $entity2->setContentType('text/html');
    $entity2->setBodyAsString('just testing');
    $entity2->setNestingLevel(Swift_Mime_MimeEntity::LEVEL_SUBPART);
    
    $entity1->setChildren(array($entity2));
    
    $boundary = $entity1->getBoundary();
    
    $this->assertTrue($boundary);
    
    $this->assertEqual(
      'Content-Type: multipart/alternative;' . "\r\n" .
      ' boundary="' . $boundary . '"' . "\r\n" .
      'Content-Transfer-Encoding: 8bit' . "\r\n" .
      "\r\n" .
      '--' . $boundary . "\r\n" .
      'Content-Type: text/html' . "\r\n" .
      'Content-Transfer-Encoding: quoted-printable' . "\r\n" .
      "\r\n" .
      'just testing' .
      "\r\n" .
      '--' . $boundary . '--' . "\r\n",
      $entity1->toString()
      );
  }
  
  public function testBinaryEncodingIsDisplayedForNestedContent()
  {
    $entity1 = $this->_createEntity();
    $entity1->setEncoder(
      new Swift_Mime_ContentEncoder_PlainContentEncoder('binary')
      );
    
    $entity2 = $this->_createEntity();
    $entity2->setContentType('text/html');
    $entity2->setBodyAsString('just testing');
    $entity2->setNestingLevel(Swift_Mime_MimeEntity::LEVEL_SUBPART);
    
    $entity1->setChildren(array($entity2));
    
    $boundary = $entity1->getBoundary();
    
    $this->assertTrue($boundary);
    
    $this->assertEqual(
      'Content-Type: multipart/alternative;' . "\r\n" .
      ' boundary="' . $boundary . '"' . "\r\n" .
      'Content-Transfer-Encoding: binary' . "\r\n" .
      "\r\n" .
      '--' . $boundary . "\r\n" .
      'Content-Type: text/html' . "\r\n" .
      'Content-Transfer-Encoding: quoted-printable' . "\r\n" .
      "\r\n" .
      'just testing' .
      "\r\n" .
      '--' . $boundary . '--' . "\r\n",
      $entity1->toString()
      );
  }
  
  public function testIdAppearsIfContentIdHeaderPresent()
  {
    $entity = $this->_createEntity();
    $headers = $entity->getHeaders();
    $headers[] = new Swift_Mime_Header_IdentificationHeader('Content-ID');
    $entity->setHeaders($headers);
    
    $entity->setContentType('text/plain');
    $entity->setId('foo@bar');
    
    $this->assertEqual(
      'Content-Type: text/plain' . "\r\n" .
      'Content-Transfer-Encoding: quoted-printable' . "\r\n" .
      'Content-ID: <foo@bar>' . "\r\n",
      $entity->toString()
      );
  }
  
  public function testDescriptionAppearsIfContentDescriptionHeaderIsPresent()
  {
    $entity = $this->_createEntity();
    $headers = $entity->getHeaders();
    $headers[] = new Swift_Mime_Header_UnstructuredHeader(
      'Content-Description', $this->_headerEncoder
      );
    $entity->setHeaders($headers);
    
    $entity->setContentType('text/plain');
    $entity->setDescription('some description');
    
    $this->assertEqual(
      'Content-Type: text/plain' . "\r\n" .
      'Content-Transfer-Encoding: quoted-printable' . "\r\n" .
      'Content-Description: some description' . "\r\n",
      $entity->toString()
      );
  }
  
  // -- Private helpers
  
  private function _createEntity()
  {
    $entity = new Swift_Mime_SimpleMimeEntity(
      array(
        new Swift_Mime_Header_ParameterizedHeader(
          'Content-Type', $this->_headerEncoder, $this->_paramEncoder
          ),
        new Swift_Mime_Header_UnstructuredHeader(
          'Content-Transfer-Encoding', $this->_headerEncoder
          )
        ),
      $this->_contentEncoder
      );
    return $entity;
  }
  
}
