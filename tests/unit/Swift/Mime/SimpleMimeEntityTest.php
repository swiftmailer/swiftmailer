<?php

require_once 'Swift/AbstractSwiftUnitTestCase.php';
require_once 'Swift/Mime/MimeEntity.php';
require_once 'Swift/Mime/SimpleMimeEntity.php';
require_once 'Swift/Mime/Header.php';
require_once 'Swift/Mime/ContentEncoder.php';
require_once 'Swift/Mime/FieldChangeObserver.php';

Mock::generate('Swift_Mime_Header', 'Swift_Mime_MockHeader');
Mock::generate('Swift_Mime_ContentEncoder', 'Swift_Mime_MockContentEncoder');
Mock::generate(
  'Swift_Mime_FieldChangeObserver',
  'Swift_Mime_MockFieldChangeObserver'
  );
Mock::generate('Swift_Mime_MimeEntity', 'Swift_Mime_MockMimeEntity');

class Swift_Mime_SimpleMimeEntityTest extends Swift_AbstractSwiftUnitTestCase
{
  
  private $_encoder;
  
  public function setUp()
  {
    $this->_encoder = new Swift_Mime_MockContentEncoder();
  }
  
  public function testHeadersAreReturned()
  {
    $h = new Swift_Mime_MockHeader();
    $h->setReturnValue('getFieldName', 'Content-Type');
    $headers = array($h);
    $entity = $this->_getEntity($headers, $this->_encoder);
    $this->assertEqual($headers, $entity->getHeaders());
  }
  
  public function testHeadersAppearInString()
  {
    $h1 = new Swift_Mime_MockHeader();
    $h1->setReturnValue('getFieldName', 'Content-Type');
    $h1->setReturnValue('toString', 'Content-Type: text/html' . "\r\n");
    $h2 = new Swift_Mime_MockHeader();
    $h2->setReturnValue('getFieldName', 'X-Header');
    $h2->setReturnValue('toString', 'X-Header: foo' . "\r\n");
    $headers = array($h1, $h2);
    $entity = $this->_getEntity($headers, $this->_encoder);
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
    $h1->setReturnValue('toString', 'Content-Type: text/html' . "\r\n");
    $h2 = new Swift_Mime_MockHeader();
    $h2->setReturnValue('getFieldName', 'X-Header');
    $h2->setReturnValue('toString', 'X-Header: foo' . "\r\n");
    $headers = array($h1, $h2);
    $this->_encoder->setReturnValue('encodeString', 'my body');
    $entity = $this->_getEntity($headers, $this->_encoder);
    $entity->setBodyAsString('my body');
    $this->assertEqual(
      'Content-Type: text/html' . "\r\n" .
      'X-Header: foo' . "\r\n" .
      "\r\n" .
      'my body',
      $entity->toString()
      );
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
    
    $entity = $this->_getEntity($headers, $this->_encoder);
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
    
    $entity = $this->_getEntity($headers, $this->_encoder);
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
    
    $entity1 = $this->_getEntity($headers1, $this->_encoder);
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
      $entity = $this->_getEntity($headers1, $this->_encoder);
      
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
      $entity = $this->_getEntity($headers1, $this->_encoder);
      
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
      $entity = $this->_getEntity($headers1, $this->_encoder);
      
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
      
      $entity = $this->_getEntity($headers, $this->_encoder);
      
      $observer = new Swift_Mime_MockFieldChangeObserver();
      $observer->expectAt(0, 'fieldChanged',
        array('contenttype', $combination['type'])
        );
      $observer->expectAt(1, 'fieldChanged', array('boundary', '*'));
      
      $entity->registerFieldChangeObserver($observer);
      
      $entity->setChildren($children);
    }
  }
  
  // -- Private helpers
  
  private function _getEntity($headers, $encoder)
  {
    return new Swift_Mime_SimpleMimeEntity($headers, $encoder);
  }
  
}
