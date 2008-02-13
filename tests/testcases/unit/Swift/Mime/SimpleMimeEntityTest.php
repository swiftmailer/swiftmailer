<?php

require_once 'Swift/AbstractSwiftUnitTestCase.php';
require_once 'Swift/Mime/MimeEntity.php';
require_once 'Swift/Mime/SimpleMimeEntity.php';
require_once 'Swift/Mime/Header.php';
require_once 'Swift/Mime/ContentEncoder.php';
require_once 'Swift/Mime/FieldChangeObserver.php';
require_once 'Swift/InputByteStream.php';
require_once 'Swift/OutputByteStream.php';
require_once 'Swift/KeyCache.php';

Mock::generate('Swift_Mime_Header', 'Swift_Mime_MockHeader');
Mock::generate('Swift_Mime_ContentEncoder', 'Swift_Mime_MockContentEncoder');
Mock::generate(
  'Swift_Mime_FieldChangeObserver',
  'Swift_Mime_MockFieldChangeObserver'
  );
Mock::generate('Swift_Mime_MimeEntity', 'Swift_Mime_MockMimeEntity');
Mock::generate('Swift_InputByteStream', 'Swift_MockInputByteStream');
Mock::generate('Swift_OutputByteStream', 'Swift_MockOutputByteStream');
Mock::generate('Swift_KeyCache', 'Swift_MockKeyCache');

class Swift_Mime_SimpleMimeEntityTest extends Swift_AbstractSwiftUnitTestCase
{
  
  private $_encoder;
  private $_cache;
  
  public function setUp()
  {
    $this->_encoder = new Swift_Mime_MockContentEncoder();
    $this->_cache = new Swift_MockKeyCache();
  }
  
  public function testHeadersAreReturned()
  {
    $h = new Swift_Mime_MockHeader();
    $h->setReturnValue('getFieldName', 'Content-Type');
    $h->setReturnValue('getFieldBody', 'text/plain');
    $headers = array($h);
    $entity = $this->_getEntity($headers, $this->_encoder, $this->_cache);
    $this->assertEqual($headers, $entity->getHeaders());
  }
  
  public function testHeadersAppearInString()
  {
    $h1 = new Swift_Mime_MockHeader();
    $h1->setReturnValue('getFieldName', 'Content-Type');
    $h1->setReturnValue('getFieldBody', 'text/html');
    $h1->setReturnValue('toString', 'Content-Type: text/html' . "\r\n");
    $h2 = new Swift_Mime_MockHeader();
    $h2->setReturnValue('getFieldName', 'X-Header');
    $h2->setReturnValue('getFieldBody', 'foo');
    $h2->setReturnValue('toString', 'X-Header: foo' . "\r\n");
    $headers = array($h1, $h2);
    $this->_cache->setReturnValue('getString',
      'Content-Type: text/html' . "\r\n" .
      'X-Header: foo' . "\r\n",
      array('*', 'headers')
      );
    $entity = $this->_getEntity($headers, $this->_encoder, $this->_cache);
    $this->assertEqual(
      'Content-Type: text/html' . "\r\n" .
      'X-Header: foo' . "\r\n",
      $entity->toString()
      );
  }
  
  public function testBodyIsAppended()
  {
    $h1 = new Swift_Mime_MockHeader();
    $h1->setReturnValue('getFieldName', 'Content-Type');
    $h1->setReturnValue('getFieldBody', 'text/html');
    $h1->setReturnValue('toString', 'Content-Type: text/html' . "\r\n");
    $h2 = new Swift_Mime_MockHeader();
    $h2->setReturnValue('getFieldName', 'X-Header');
    $h2->setReturnValue('getFieldBody', 'foo');
    $h2->setReturnValue('toString', 'X-Header: foo' . "\r\n");
    $headers = array($h1, $h2);
    $this->_encoder->setReturnValue('encodeString', 'my body');
    $this->_cache->setReturnValue('getString',
      'Content-Type: text/html' . "\r\n" .
      'X-Header: foo' . "\r\n",
      array('*', 'headers')
      );
    $this->_cache->setReturnValue('getString',
      "\r\n" .
      'my body',
      array('*', 'body')
      );
    $entity = $this->_getEntity($headers, $this->_encoder, $this->_cache);
    $entity->setBodyAsString('my body');
    $this->assertEqual(
      'Content-Type: text/html' . "\r\n" .
      'X-Header: foo' . "\r\n" .
      "\r\n" .
      'my body',
      $entity->toString()
      );
  }
  
  public function testByteStreamBodyIsAppended()
  {
    $h1 = new Swift_Mime_MockHeader();
    $h1->setReturnValue('getFieldName', 'Content-Type');
    $h1->setReturnValue('getFieldBody', 'text/html');
    $h1->setReturnValue('toString', 'Content-Type: text/html' . "\r\n");
    $h2 = new Swift_Mime_MockHeader();
    $h2->setReturnValue('getFieldName', 'X-Header');
    $h2->setReturnValue('getFieldBody', 'foo');
    $h2->setReturnValue('toString', 'X-Header: foo' . "\r\n");
    $headers = array($h1, $h2);
    $this->_encoder->setReturnValue('encodeString', 'my body');
    $this->_cache->setReturnValue('getString',
      'Content-Type: text/html' . "\r\n" .
      'X-Header: foo' . "\r\n",
      array('*', 'headers')
      );
    $this->_cache->setReturnValue('getString',
      "\r\n" .
      'my body',
      array('*', 'body')
      );
    $entity = $this->_getEntity($headers, $this->_encoder, $this->_cache);
    
    $os = new Swift_MockOutputByteStream();
    $os->setReturnValueAt(0, 'read', 'my body');
    $os->setReturnValueAt(1, 'read', false);
    
    $entity->setBodyAsByteStream($os);
    
    $this->assertEqual(
      'Content-Type: text/html' . "\r\n" .
      'X-Header: foo' . "\r\n" .
      "\r\n" .
      'my body',
      $entity->toString()
      );
  }
  
  public function testEntityCanBeWrittenToByteStream()
  {
    //see acceptance test for more detail
    $this->_cache->expectAtLeastOnce('exportToByteStream');
    $entity = $this->_getEntity(array(), $this->_encoder, $this->_cache);
    $entity->setBodyAsString('test');
    $entity->toByteStream(new Swift_MockInputByteStream());
  }
  
  public function testContentTypeCanBeSetAndFetched()
  {
    /* --
    This comes in very useful so Headers can observe the entity for things
    such as content-type or content-transfer-encoding changes.
    */
    
    $h = new Swift_Mime_MockHeader();
    $h->setReturnValue('getFieldName', 'Content-Type');
    $headers = array($h);
    
    $entity = $this->_getEntity($headers, $this->_encoder, $this->_cache);
    $entity->setContentType('text/html');
    
    $this->assertEqual('text/html', $entity->getContentType());
  }
  
  public function testMimeFieldObserversAreNotifiedOnChange()
  {
    /* --
    This comes in very useful so Headers can observe the entity for things
    such as content-type or content-transfer-encoding changes.
    */
    
    $h = new Swift_Mime_MockHeader();
    $h->setReturnValue('getFieldName', 'Content-Type');
    $headers = array($h);
    
    $observer1 = new Swift_Mime_MockFieldChangeObserver();
    $observer1->expectOnce('fieldChanged', array('contenttype', 'text/html'));
    $observer2 = new Swift_Mime_MockFieldChangeObserver();
    $observer2->expectOnce('fieldChanged', array('contenttype', 'text/html'));
    
    $entity = $this->_getEntity($headers, $this->_encoder, $this->_cache);
    $entity->registerFieldChangeObserver($observer1);
    $entity->registerFieldChangeObserver($observer2);
    
    $entity->setContentType('text/html');
  }
  
  public function testAddingChildrenGeneratesBoundary()
  {
    $h1 = new Swift_Mime_MockHeader();
    $h1->setReturnValue('getFieldName', 'Content-Type');
    $headers1 = array($h1);
    
    $observer1 = new Swift_Mime_MockFieldChangeObserver();
    //ack!
    $observer1->expectAt(1, 'fieldChanged', array('boundary', '*'));
    
    $entity1 = $this->_getEntity($headers1, $this->_encoder, $this->_cache);
    $entity1->registerFieldChangeObserver($observer1);
    
    $h2 = new Swift_Mime_MockHeader();
    $h2->setReturnValue('getFieldName', 'Content-Type');
    $headers2 = array($h2);
    
    $entity2 = new Swift_Mime_MockMimeEntity();
    $entity2->setReturnValue('getHeaders', $headers2);
    $entity2->setReturnValue('getNestingLevel',
      Swift_Mime_MimeEntity::LEVEL_ATTACHMENT
      );
    
    $entity1->setChildren(array($entity2));
  }
  
  public function testChildrenOfLevelAttachmentOrLessGeneratesMultipartMixed()
  {
    $h1 = new Swift_Mime_MockHeader();
    $h1->setReturnValue('getFieldName', 'Content-Type');
    $headers1 = array($h1);
    
    for ($level = Swift_Mime_MimeEntity::LEVEL_ATTACHMENT;
      $level > Swift_Mime_MimeEntity::LEVEL_TOP; $level--)
    {
      $entity = $this->_getEntity($headers1, $this->_encoder, $this->_cache);
      
      $observer = new Swift_Mime_MockFieldChangeObserver();
      $observer->expectAt(0, 'fieldChanged', array('contenttype', 'multipart/mixed'));
      $observer->expectAt(1, 'fieldChanged', array('boundary', '*'));
      $observer->expectMinimumCallCount('fieldChanged', 2);
      
      $entity->registerFieldChangeObserver($observer);
      
      $h2 = new Swift_Mime_MockHeader();
      $h2->setReturnValue('getFieldName', 'Content-Type');
      $headers2 = array($h2);
    
      $entity2 = new Swift_Mime_MockMimeEntity();
      $entity2->setReturnValue('getHeaders', $headers2);
      $entity2->setReturnValue('getNestingLevel', $level);
    
      $entity->setChildren(array($entity2));
    }
  }
  
  public function testChildrenOfLevelEmbeddedOrLessGeneratesMultipartRelated()
  {
    $h1 = new Swift_Mime_MockHeader();
    $h1->setReturnValue('getFieldName', 'Content-Type');
    $headers1 = array($h1);
    
    for ($level = Swift_Mime_MimeEntity::LEVEL_EMBEDDED;
      $level > Swift_Mime_MimeEntity::LEVEL_ATTACHMENT; $level--)
    {
      $entity = $this->_getEntity($headers1, $this->_encoder, $this->_cache);
      
      $observer = new Swift_Mime_MockFieldChangeObserver();
      $observer->expectAt(0, 'fieldChanged', array('contenttype', 'multipart/related'));
      $observer->expectAt(1, 'fieldChanged', array('boundary', '*'));
      $observer->expectMinimumCallCount('fieldChanged', 2);
      
      $entity->registerFieldChangeObserver($observer);
      
      $h2 = new Swift_Mime_MockHeader();
      $h2->setReturnValue('getFieldName', 'Content-Type');
      $headers2 = array($h2);
    
      $entity2 = new Swift_Mime_MockMimeEntity();
      $entity2->setReturnValue('getHeaders', $headers2);
      $entity2->setReturnValue('getNestingLevel', $level);
    
      $entity->setChildren(array($entity2));
    }
  }
  
  public function testChildrenOfLevelSubpartOrLessGeneratesMultipartAlternative()
  {
    $h1 = new Swift_Mime_MockHeader();
    $h1->setReturnValue('getFieldName', 'Content-Type');
    $headers1 = array($h1);
    
    for ($level = Swift_Mime_MimeEntity::LEVEL_SUBPART;
      $level > Swift_Mime_MimeEntity::LEVEL_EMBEDDED; $level--)
    {
      $entity = $this->_getEntity($headers1, $this->_encoder, $this->_cache);
      
      $observer = new Swift_Mime_MockFieldChangeObserver();
      $observer->expectAt(0, 'fieldChanged', array('contenttype', 'multipart/alternative'));
      $observer->expectAt(1, 'fieldChanged', array('boundary', '*'));
      $observer->expectMinimumCallCount('fieldChanged', 2);
      
      $entity->registerFieldChangeObserver($observer);
      
      $h2 = new Swift_Mime_MockHeader();
      $h2->setReturnValue('getFieldName', 'Content-Type');
      $headers2 = array($h2);
    
      $entity2 = new Swift_Mime_MockMimeEntity();
      $entity2->setReturnValue('getHeaders', $headers2);
      $entity2->setReturnValue('getNestingLevel', $level);
    
      $entity->setChildren(array($entity2));
    }
  }
  
  public function testHighestLevelChildDeterminesContentType()
  {
    $combinations  = array(
      array('levels' => array(Swift_Mime_MimeEntity::LEVEL_ATTACHMENT,
        Swift_Mime_MimeEntity::LEVEL_EMBEDDED,
        Swift_Mime_MimeEntity::LEVEL_SUBPART
        ),
        'type' => 'multipart/mixed'
        ),
      array('levels' => array(Swift_Mime_MimeEntity::LEVEL_ATTACHMENT,
        Swift_Mime_MimeEntity::LEVEL_EMBEDDED
        ),
        'type' => 'multipart/mixed'
        ),
      array('levels' => array(Swift_Mime_MimeEntity::LEVEL_ATTACHMENT,
        Swift_Mime_MimeEntity::LEVEL_SUBPART
        ),
        'type' => 'multipart/mixed'
        ),
      array('levels' => array(Swift_Mime_MimeEntity::LEVEL_EMBEDDED,
        Swift_Mime_MimeEntity::LEVEL_SUBPART
        ),
        'type' => 'multipart/related'
        )
      );
    
    foreach ($combinations as $combination)
    {
      $children = array();
      foreach ($combination['levels'] as $level)
      {
        $subentity = new Swift_Mime_MockMimeEntity();
        $subentity->setReturnValue('getNestingLevel', $level);
        $children[] = $subentity;
      }
      
      $headers = array();
      $h1 = new Swift_Mime_MockHeader();
      $h1->setReturnValue('getFieldName', 'Content-Type');
      
      $entity = $this->_getEntity($headers, $this->_encoder, $this->_cache);
      
      $observer = new Swift_Mime_MockFieldChangeObserver();
      $observer->expectAt(0, 'fieldChanged',
        array('contenttype', $combination['type'])
        );
      $observer->expectAt(1, 'fieldChanged', array('boundary', '*'));
      
      $entity->registerFieldChangeObserver($observer);
      
      $entity->setChildren($children);
    }
  }
  
  public function testBoundaryCanBeRetrieved()
  {
    /* -- RFC 2046, 5.1.1.
     boundary := 0*69<bchars> bcharsnospace

     bchars := bcharsnospace / " "

     bcharsnospace := DIGIT / ALPHA / "'" / "(" / ")" /
                      "+" / "_" / "," / "-" / "." /
                      "/" / ":" / "=" / "?"
    */
    
    $h1 = new Swift_Mime_MockHeader();
    $h1->setReturnValue('getFieldName', 'Content-Type');
    $headers1 = array($h1);
    
    $entity1 = $this->_getEntity($headers1, $this->_encoder, $this->_cache);
    
    $h2 = new Swift_Mime_MockHeader();
    $h2->setReturnValue('getFieldName', 'Content-Type');
    $headers2 = array($h2);
    
    $entity2 = new Swift_Mime_MockMimeEntity();
    $entity2->setReturnValue('getHeaders', $headers2);
    $entity2->setReturnValue('getNestingLevel',
      Swift_Mime_MimeEntity::LEVEL_ATTACHMENT
      );
    
    $entity1->setChildren(array($entity2));
    
    $this->assertPattern(
      '/^[a-zA-Z0-9\'\(\)\+_\-,\.\/:=\?\ ]{0,69}[a-zA-Z0-9\'\(\)\+_\-,\.\/:=\?]$/D',
      $entity1->getBoundary()
      );
  }
  
  public function testBoundaryNeverChanges()
  {
    $h1 = new Swift_Mime_MockHeader();
    $h1->setReturnValue('getFieldName', 'Content-Type');
    $headers1 = array($h1);
    
    $entity1 = $this->_getEntity($headers1, $this->_encoder, $this->_cache);
    
    $h2 = new Swift_Mime_MockHeader();
    $h2->setReturnValue('getFieldName', 'Content-Type');
    $headers2 = array($h2);
    
    $entity2 = new Swift_Mime_MockMimeEntity();
    $entity2->setReturnValue('getHeaders', $headers2);
    $entity2->setReturnValue('getNestingLevel',
      Swift_Mime_MimeEntity::LEVEL_ATTACHMENT
      );
    
    $entity1->setChildren(array($entity2));
    
    $boundary = $entity1->getBoundary();
    for ($i = 0; $i < 10; $i++)
    {
      $this->assertEqual($boundary, $entity1->getBoundary());
    }
  }
  
  public function testBoundaryCanBeManuallySet()
  {
    $h1 = new Swift_Mime_MockHeader();
    $h1->setReturnValue('getFieldName', 'Content-Type');
    $headers1 = array($h1);
    
    $entity1 = $this->_getEntity($headers1, $this->_encoder, $this->_cache);
    
    $h2 = new Swift_Mime_MockHeader();
    $h2->setReturnValue('getFieldName', 'Content-Type');
    $headers2 = array($h2);
    
    $entity2 = new Swift_Mime_MockMimeEntity();
    $entity2->setReturnValue('getHeaders', $headers2);
    $entity2->setReturnValue('getNestingLevel',
      Swift_Mime_MimeEntity::LEVEL_ATTACHMENT
      );
      
    $entity1->setBoundary('my_boundary');
    
    $entity1->setChildren(array($entity2));
    
    $this->assertEqual('my_boundary', $entity1->getBoundary());
  }
  
  public function testChildrenAppearInString()
  {
    /* -- RFC 2046, 5.1.1.
     (excerpt too verbose to paste here)
     */
    
    $h1 = new Swift_Mime_MockHeader();
    $h1->setReturnValue('getFieldName', 'Content-Type');
    $h1->setReturnValue('getFieldBody', 'multipart/alternative;' . "\r\n" .
      ' boundary="_=_foo_=_"'
      );
    $h1->setReturnValue('toString',
      'Content-Type: multipart/alternative;' . "\r\n" .
      ' boundary="_=_foo_=_"' . "\r\n"
      );
    $headers1 = array($h1);
    
    $this->_cache->setReturnValueAt(0, 'getString',
      'Content-Type: multipart/alternative;' . "\r\n" .
      ' boundary="_=_foo_=_"' . "\r\n",
      array('*', 'headers')
      );
    
    $entity1 = $this->_getEntity($headers1, $this->_encoder, $this->_cache);
    $entity1->setBoundary('_=_foo_=_');
    
    $entity2 = new Swift_Mime_MockMimeEntity();
    $entity2->setReturnValue('getNestingLevel',
      Swift_Mime_MimeEntity::LEVEL_SUBPART
      );
    $entity2->setReturnValue('toString',
      'Content-Type: text/plain' . "\r\n" .
      "\r\n" .
      'foobar test'
      );
    
    $entity3 = new Swift_Mime_MockMimeEntity();
    $entity3->setReturnValue('getNestingLevel',
      Swift_Mime_MimeEntity::LEVEL_SUBPART
      );
    $entity3->setReturnValue('toString',
      'Content-Type: text/html' . "\r\n" .
      "\r\n" .
      'foobar <strong>test</strong>'
      );
    
    $entity1->setChildren(array($entity2, $entity3));
    
    $this->assertEqual(
      'Content-Type: multipart/alternative;' . "\r\n" .
      ' boundary="_=_foo_=_"' . "\r\n" .
      "\r\n" .
      '--_=_foo_=_' . "\r\n" .
      'Content-Type: text/plain' . "\r\n" .
      "\r\n" .
      'foobar test' . "\r\n" .
      '--_=_foo_=_' . "\r\n" .
      'Content-Type: text/html' . "\r\n" .
      "\r\n" .
      'foobar <strong>test</strong>' . "\r\n" .
      '--_=_foo_=_--' . "\r\n"
      ,
      $entity1->toString()
      );
  }
  
  public function testMixingLevelsIsHierarchical()
  {
    $h1 = new Swift_Mime_MockHeader();
    $h1->setReturnValue('getFieldName', 'Content-Type');
    $h1->setReturnValue('getFieldBody', 'multipart/mixed;' . "\r\n" .
      ' boundary="_=_foo_=_"'
      );
    $h1->setReturnValue('toString',
      'Content-Type: multipart/mixed;' . "\r\n" .
      ' boundary="_=_foo_=_"' . "\r\n"
      );
    $headers = array($h1);
    
    $this->_cache->setReturnValueAt(0, 'getString',
      'Content-Type: multipart/mixed;' . "\r\n" .
      ' boundary="_=_foo_=_"' . "\r\n",
      array('*', 'headers')
      );
    $this->_cache->setReturnValueAt(1, 'getString',
      'Content-Type: multipart/alternative;' . "\r\n" .
      ' boundary="_=_bar_=_"' . "\r\n",
      array('*', 'headers')
      );
    
    $entity1 = $this->_getEntity($headers, $this->_encoder, $this->_cache);
    $entity1->setBoundary('_=_foo_=_');
    
    //Create some entities which nest differently
    $entity2 = new Swift_Mime_MockMimeEntity();
    $entity2->setReturnValue('getNestingLevel',
      Swift_Mime_MimeEntity::LEVEL_ATTACHMENT
      );
    $entity2->setReturnValue('toString',
      'Content-Type: application/octet-stream' . "\r\n" .
      "\r\n" .
      'foo'
      );
    
    $entity3 = new Swift_Mime_MockMimeEntity();
    $entity3->setReturnValue('getNestingLevel',
      Swift_Mime_MimeEntity::LEVEL_SUBPART
      );
    $entity3->setReturnValue('toString',
      'Content-Type: text/plain' . "\r\n" .
      "\r\n" .
      'xyz'
      );
    
    $entity1->setChildren(array($entity2, $entity3));
    
    $stringEntity = $entity1->toString();
    
    $this->assertPattern(
      '~^' .
      'Content-Type: multipart/mixed;' . "\r\n" .
      ' boundary="_=_foo_=_"' . "\r\n" .
      "\r\n" .
      '--_=_foo_=_' . "\r\n" .
      'Content-Type: multipart/alternative;' . "\r\n" .
      ' boundary="_=_bar_=_"' . "\r\n" .
      "\r\n" .
      '--(.*?)' . "\r\n" .
      'Content-Type: text/plain' . "\r\n" .
      "\r\n" .
      'xyz' . "\r\n" .
      '--\\1--' . "\r\n" .
      "\r\n" .
      '--_=_foo_=_' . "\r\n" .
      'Content-Type: application/octet-stream' . "\r\n" .
      "\r\n" .
      'foo' .
      "\r\n" .
      '--_=_foo_=_--' . "\r\n" .
      '$~D',
      $stringEntity
      );
  }
  
  public function testSettingEncoderNotifiesFieldChange()
  {
    $this->_encoder->setReturnValue('getName', 'quoted-printable');
    
    $h = new Swift_Mime_MockHeader();
    $h->setReturnValue('getFieldName', 'Content-Type');
    $headers = array($h);
    
    $encoder = new Swift_Mime_MockContentEncoder();
    $encoder->setReturnValue('getName', 'base64');
    
    $observer1 = new Swift_Mime_MockFieldChangeObserver();
    $observer1->expectOnce('fieldChanged',
      array('encoder', $encoder)
      );
    $observer2 = new Swift_Mime_MockFieldChangeObserver();
    $observer2->expectOnce('fieldChanged',
      array('encoder', $encoder)
      );
    
    $entity = $this->_getEntity($headers, $this->_encoder, $this->_cache);
    
    $entity->registerFieldChangeObserver($observer1);
    $entity->registerFieldChangeObserver($observer2);
    
    $entity->setEncoder($encoder);
  }
  
  public function testIdCanBeSet()
  {
    /* -- RFC 2045, 7.
    In constructing a high-level user agent, it may be desirable to allow
    one body to make reference to another.  Accordingly, bodies may be
    labelled using the "Content-ID" header field, which is syntactically
    identical to the "Message-ID" header field
    */
    
    $entity = $this->_getEntity(array(), $this->_encoder, $this->_cache);
    $entity->setId('foo@bar');
    $this->assertEqual('foo@bar', $entity->getId());
  }
  
  public function testIdIsAutoGenerated()
  {
    $entity = $this->_getEntity(array(), $this->_encoder, $this->_cache);
    $this->assertPattern('/^.*?@.*?$/D', $entity->getId());
  }
  
  public function testIdDoesntChangeForSameEntity()
  {
    $entity = $this->_getEntity(array(), $this->_encoder, $this->_cache);
    $id = $entity->getId();
    for ($i = 0; $i < 10; ++$i)
    {
      $this->assertEqual($id, $entity->getId());
    }
  }
  
  public function testIdsAreUniquePerEntity()
  {
    $lastid = null;
    for ($i = 0; $i < 10; ++$i)
    {
      $entity = $this->_getEntity(array(), $this->_encoder, $this->_cache);
      $this->assertNotEqual($lastid, $entity->getId());
      $lastid = $entity->getId();
    }
  }
  
  public function testFieldChangeObserverIsNotifiedOfIdChange()
  {
    $observer1 = new Swift_Mime_MockFieldChangeObserver();
    $observer1->expectOnce('fieldChanged',
      array('id', 'foo@bar')
      );
    $observer2 = new Swift_Mime_MockFieldChangeObserver();
    $observer2->expectOnce('fieldChanged',
      array('id', 'foo@bar')
      );
    
    $entity = $this->_getEntity(array(), $this->_encoder, $this->_cache);
    
    $entity->registerFieldChangeObserver($observer1);
    $entity->registerFieldChangeObserver($observer2);
    
    $entity->setId('foo@bar');
  }
  
  public function testDescriptionCanBeSet()
  {
    /* -- RFC 2045, 8.
    The ability to associate some descriptive information with a given
    body is often desirable.  For example, it may be useful to mark an
    "image" body as "a picture of the Space Shuttle Endeavor."  Such text
    may be placed in the Content-Description header field.  This header
    field is always optional.
    */
    
    $entity = $this->_getEntity(array(), $this->_encoder, $this->_cache);
    $entity->setDescription('my mime entity');
    $this->assertEqual('my mime entity', $entity->getDescription());
  }
  
  public function testDescriptionNotifiesFieldChangeObserver()
  {
    $observer1 = new Swift_Mime_MockFieldChangeObserver();
    $observer1->expectOnce('fieldChanged',
      array('description', 'my desc')
      );
    $observer2 = new Swift_Mime_MockFieldChangeObserver();
    $observer2->expectOnce('fieldChanged',
      array('description', 'my desc')
      );
    
    $entity = $this->_getEntity(array(), $this->_encoder, $this->_cache);
    
    $entity->registerFieldChangeObserver($observer1);
    $entity->registerFieldChangeObserver($observer2);
    
    $entity->setDescription('my desc');
  }
  
  public function testEncoderIsUsedForStringGeneration()
  {
    $h1 = new Swift_Mime_MockHeader();
    $h1->setReturnValue('getFieldName', 'Content-Type');
    $h1->setReturnValue('getFieldBody', 'text/html');
    $h1->setReturnValue('toString', 'Content-Type: text/html' . "\r\n");
    $h2 = new Swift_Mime_MockHeader();
    $h2->setReturnValue('getFieldName', 'X-Header');
    $h2->setReturnValue('getFieldBody', 'foo');
    $h2->setReturnValue('toString', 'X-Header: foo' . "\r\n");
    $headers = array($h1, $h2);
    
    $this->_encoder->expectOnce('encodeString', array('my body', '*', '*'));
    
    $entity = $this->_getEntity($headers, $this->_encoder, $this->_cache);
    $entity->setBodyAsString('my body');
    $entity->toString();
  }
  
  public function testMaxLineLengthIsProvidedForEncoding()
  {
    $h1 = new Swift_Mime_MockHeader();
    $h1->setReturnValue('getFieldName', 'Content-Type');
    $h1->setReturnValue('toString', 'Content-Type: text/html' . "\r\n");
    $h2 = new Swift_Mime_MockHeader();
    $h2->setReturnValue('getFieldName', 'X-Header');
    $h2->setReturnValue('toString', 'X-Header: foo' . "\r\n");
    $headers = array($h1, $h2);
    
    $this->_encoder->expectOnce('encodeString', array('my body', 0, 78));
    $this->_encoder->setReturnValue('encodeString', 'my body');
    
    $entity = $this->_getEntity($headers, $this->_encoder, $this->_cache);
    $entity->setMaxLineLength(78);
    $entity->setBodyAsString('my body');
    
    $entity->toString();
  }
  
  public function testOrderingOfAlternativePartsCanBeSpecified_1()
  {
    $h1 = new Swift_Mime_MockHeader();
    $h1->setReturnValue('getFieldName', 'Content-Type');
    $h1->setReturnValue('getFieldBody', 'multipart/alternative;' . "\r\n" .
      ' boundary="_=_foo_=_"'
      );
    $h1->setReturnValue('toString',
      'Content-Type: multipart/alternative;' . "\r\n" .
      ' boundary="_=_foo_=_"' . "\r\n"
      );
    $headers1 = array($h1);
    
    $this->_cache->setReturnValue('getString',
      'Content-Type: multipart/alternative;' . "\r\n" .
      ' boundary="_=_foo_=_"' . "\r\n",
      array('*', 'headers')
      );
    
    $entity1 = $this->_getEntity($headers1, $this->_encoder, $this->_cache);
    $entity1->setBoundary('_=_foo_=_');
    
    $entity2 = new Swift_Mime_MockMimeEntity();
    $entity2->setReturnValue('getNestingLevel',
      Swift_Mime_MimeEntity::LEVEL_SUBPART
      );
    $entity2->setReturnValue('getContentType', 'text/plain');
    $entity2->setReturnValue('toString',
      'Content-Type: text/plain' . "\r\n" .
      "\r\n" .
      'foobar test'
      );
    
    $entity3 = new Swift_Mime_MockMimeEntity();
    $entity3->setReturnValue('getNestingLevel',
      Swift_Mime_MimeEntity::LEVEL_SUBPART
      );
    $entity3->setReturnValue('toString',
      'Content-Type: text/html' . "\r\n" .
      "\r\n" .
      'foobar <strong>test</strong>'
      );
    $entity3->setReturnValue('getContentType', 'text/html');
    
    $entity1->setChildren(array($entity2, $entity3));
    
    $entity1->setTypeOrderPreference(array(
      'text/html' => 1,
      'text/plain' => 2
      ));
      
    $this->assertEqual(
      'Content-Type: multipart/alternative;' . "\r\n" .
      ' boundary="_=_foo_=_"' . "\r\n" .
      "\r\n" .
      '--_=_foo_=_' . "\r\n" .
      'Content-Type: text/html' . "\r\n" .
      "\r\n" .
      'foobar <strong>test</strong>' .
      "\r\n" .
      '--_=_foo_=_' . "\r\n" .
      'Content-Type: text/plain' . "\r\n" .
      "\r\n" .
      'foobar test' .
      "\r\n" .
      '--_=_foo_=_--' . "\r\n",
      $entity1->toString(),
      '%s: The type order preference should cause the html version to appear '. 
      'before the plain version'
      );
  }
  
  public function testOrderingOfAlternativePartsCanBeSpecified_2()
  {
    $h1 = new Swift_Mime_MockHeader();
    $h1->setReturnValue('getFieldName', 'Content-Type');
    $h1->setReturnValue('getFieldBody', 'multipart/alternative;' . "\r\n" .
      ' boundary="_=_foo_=_"'
      );
    $h1->setReturnValue('toString',
      'Content-Type: multipart/alternative;' . "\r\n" .
      ' boundary="_=_foo_=_"' . "\r\n"
      );
    $headers1 = array($h1);
    
    $this->_cache->setReturnValue('getString',
      'Content-Type: multipart/alternative;' . "\r\n" .
      ' boundary="_=_foo_=_"' . "\r\n",
      array('*', 'headers')
      );
    
    $entity1 = $this->_getEntity($headers1, $this->_encoder, $this->_cache);
    $entity1->setBoundary('_=_foo_=_');
    
    $entity2 = new Swift_Mime_MockMimeEntity();
    $entity2->setReturnValue('getNestingLevel',
      Swift_Mime_MimeEntity::LEVEL_SUBPART
      );
    $entity2->setReturnValue('getContentType', 'text/plain');
    $entity2->setReturnValue('toString',
      'Content-Type: text/plain' . "\r\n" .
      "\r\n" .
      'foobar test'
      );
    
    $entity3 = new Swift_Mime_MockMimeEntity();
    $entity3->setReturnValue('getNestingLevel',
      Swift_Mime_MimeEntity::LEVEL_SUBPART
      );
    $entity3->setReturnValue('toString',
      'Content-Type: text/html' . "\r\n" .
      "\r\n" .
      'foobar <strong>test</strong>'
      );
    $entity3->setReturnValue('getContentType', 'text/html');
    
    $entity1->setChildren(array($entity2, $entity3));
    
    $entity1->setTypeOrderPreference(array(
      'text/html' => 2,
      'text/plain' => 1
      ));
    
    $this->assertEqual(
      'Content-Type: multipart/alternative;' . "\r\n" .
      ' boundary="_=_foo_=_"' . "\r\n" .
      "\r\n" .
      '--_=_foo_=_' . "\r\n" .
      'Content-Type: text/plain' . "\r\n" .
      "\r\n" .
      'foobar test' .
      "\r\n" .
      '--_=_foo_=_' . "\r\n" .
      'Content-Type: text/html' . "\r\n" .
      "\r\n" .
      'foobar <strong>test</strong>' .
      "\r\n" .
      '--_=_foo_=_--' . "\r\n",
      $entity1->toString(),
      '%s: The type order preference should cause the plain version to appear '. 
      'before the html version'
      );
  }
  
  public function testFluidInterface()
  {
    $entity = $this->_getEntity(array(), $this->_encoder, $this->_cache);
    $ref = $entity
      ->setContentType('text/plain')
      ->setEncoder($this->_encoder)
      ->setId('foo@bar')
      ->setDescription('my description')
      ->setMaxLineLength(998)
      ->setBodyAsString('xx')
      ->setBodyAsByteStream(new Swift_MockOutputByteStream())
      ->setBody('')
      ->setNestingLevel(10)
      ->setBoundary('xyz')
      ->setChildren(array())
      ->setHeaders(array())
      ;
    $this->assertReference($ref, $entity,
      '%s: All setters should return $this for fluid interface allowance'
      );
  }
  
  // -- Private helpers
  
  private function _getEntity($headers, $encoder, $cache)
  {
    return new Swift_Mime_SimpleMimeEntity($headers, $encoder, $cache);
  }
  
}
