<?php

require_once 'Swift/Mime/MimeEntity.php';
require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Mime/ContentEncoder.php';
require_once 'Swift/Mime/Header.php';
require_once 'Swift/Mime/ParameterizedHeader.php';
require_once 'Swift/KeyCache.php';

abstract class Swift_Mime_AbstractMimeEntityTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testHeadersAreReturned()
  {
    $context = new Mockery();
    
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> allowing($h)->getFieldName() -> returns('Content-Type')
      -> ignoring($h)
      );
    $headers = array($h);
    $mime = $this->_createEntity(
      $headers, $this->_getEncoder($context), $this->_getCache($context)
      );
    $this->assertEqual($headers, $mime->getHeaders());
    
    $context->assertIsSatisfied();
  }
  
  public function testHeaderObjectsCanBeFetched()
  {
    $context = new Mockery();
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> allowing($h)->getFieldName() -> returns('Content-Type')
      -> allowing($h)->getFieldBody() -> returns('text/plain')
      -> ignoring($h)
      );
    
    $headers = array($h);
    $entity = $this->_createEntity(
      $headers, $this->_getEncoder($context), $this->_getCache($context)
      );
    $this->assertEqual($h, $entity->getHeader('content-type'));
    
    $context->assertIsSatisfied();
  }
  
  public function testMultipleHeaderObjectsCanBeFetched()
  {
    $context = new Mockery();
    $h1 = $context->mock('Swift_Mime_Header');
    $h2 = $context->mock('Swift_Mime_ParameterizedHeader');
    $h3 = $context->mock('Swift_Mime_Header');
    $context->checking(Expectations::create()
      -> allowing($h1)->getFieldName() -> returns('Received')
      -> allowing($h1)->getFieldBody() -> returns('xxx')
      -> ignoring($h1)
      -> allowing($h2)->getFieldName() -> returns('Content-Type')
      -> allowing($h2)->getFieldBody() -> returns('text/plain')
      -> ignoring($h2)
      -> allowing($h3)->getFieldName() -> returns('Received')
      -> allowing($h3)->getFieldBody() -> returns('yyy')
      -> ignoring($h3)
      );
    
    $headers = array($h1, $h2, $h3);
    $entity = $this->_createEntity(
      $headers, $this->_getEncoder($context), $this->_getCache($context)
      );
    $this->assertEqual(array($h1, $h3), $entity->getHeaderCollection('Received'));
    
    $context->assertIsSatisfied();
  }
  
  public function testHeadersCanBeAdded()
  {
    $context = new Mockery();
    
    $h1 = $context->mock('Swift_Mime_ParameterizedHeader');
    $h2 = $context->mock('Swift_Mime_Header');
    $h3 = $context->mock('Swift_Mime_Header');
    $context->checking(Expectations::create()
      -> allowing($h1)->getFieldName() -> returns('Content-Type')
      -> allowing($h1)->getFieldBody() -> returns('text/html')
      -> allowing($h1)->toString() -> returns('Content-Type: text/html' . "\r\n")
      -> ignoring($h1)
      -> allowing($h2)->getFieldName() -> returns('X-Header')
      -> allowing($h2)->getFieldBody() -> returns('foo')
      -> allowing($h2)->toString() -> returns('X-Header: foo' . "\r\n")
      -> ignoring($h2)
      -> allowing($h3)->getFieldName() -> returns('X-Custom')
      -> allowing($h3)->getFieldBody() -> returns('test')
      -> allowing($h3)->toString() -> returns('X-Custom: test' . "\r\n")
      -> ignoring($h3)
      );
    
    $headers = array($h1, $h2);
    
    $entity = $this->_createEntity(
      $headers, $this->_getEncoder($context), $this->_getCache($context)
      );
    
    $entity->addHeader($h3);
    
    $this->assertEqual(array($h1, $h2, $h3), $entity->getHeaders());
    
    $context->assertIsSatisfied();
  }
  
  public function testHeadersCanBeRemoved()
  {
    $context = new Mockery();
    
    $h1 = $context->mock('Swift_Mime_ParameterizedHeader');
    $h2 = $context->mock('Swift_Mime_Header');
    $context->checking(Expectations::create()
      -> allowing($h1)->getFieldName() -> returns('Content-Type')
      -> allowing($h1)->getFieldBody() -> returns('text/html')
      -> allowing($h1)->toString() -> returns('Content-Type: text/html' . "\r\n")
      -> ignoring($h1)
      -> allowing($h2)->getFieldName() -> returns('X-Header')
      -> allowing($h2)->getFieldBody() -> returns('foo')
      -> allowing($h2)->toString() -> returns('X-Header: foo' . "\r\n")
      -> ignoring($h2)
      );
    $headers = array($h1, $h2);
    
    $entity = $this->_createEntity(
      $headers, $this->_getEncoder($context), $this->_getCache($context)
      );
    
    $entity->removeHeader('X-Header');
    
    $this->assertEqual(array($h1), $entity->getHeaders());
    
    $context->assertIsSatisfied();
  }
  
  public function testHeadersAppearInString()
  {
    $context = new Mockery();
    
    $h1 = $context->mock('Swift_Mime_ParameterizedHeader');
    $h2 = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> allowing($h1)->getFieldName() -> returns('Content-Type')
      -> allowing($h1)->getFieldBody() -> returns('text/html')
      -> allowing($h1)->toString() -> returns('Content-Type: text/html' . "\r\n")
      -> ignoring($h1)
      -> allowing($h2)->getFieldName() -> returns('X-Header')
      -> allowing($h2)->getFieldBody() -> returns('foo')
      -> allowing($h2)->toString() -> returns('X-Header: foo' . "\r\n")
      -> ignoring($h2)
      );
    
    $headers = array($h1, $h2);
    
    $cache = $this->_getCache($context, false);
    $context->checking(Expectations::create()
      -> allowing($cache)->getString(any(), 'headers') -> returns(
        'Content-Type: text/html' . "\r\n" .
       'X-Header: foo' . "\r\n"
       )
      -> ignoring($cache)
      );
    
    $mime = $this->_createEntity(
      $headers, $this->_getEncoder($context), $cache
      );
    $this->assertEqual(
      'Content-Type: text/html' . "\r\n" .
      'X-Header: foo' . "\r\n",
      $mime->toString()
      );
    
    $context->assertIsSatisfied();
  }
  
  public function testBodyIsAppended()
  {
    $context = new Mockery();
    
    $h1 = $context->mock('Swift_Mime_ParameterizedHeader');
    $h2 = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> allowing($h1)->getFieldName() -> returns('Content-Type')
      -> allowing($h1)->getFieldBody() -> returns('text/html')
      -> allowing($h1)->toString() -> returns('Content-Type: text/html' . "\r\n")
      -> ignoring($h1)
      -> allowing($h2)->getFieldName() -> returns('X-Header')
      -> allowing($h2)->getFieldBody() -> returns('foo')
      -> allowing($h2)->toString() -> returns('X-Header: foo' . "\r\n")
      -> ignoring($h2)
      );
    
    $headers = array($h1, $h2);
    
    $encoder = $this->_getEncoder($context, false);
    $cache = $this->_getCache($context, false);
    $context->checking(Expectations::create()
      -> allowing($encoder)->encodeString('my body') -> returns('my body')
      -> ignoring($encoder)
      -> allowing($cache)->getString(any(), 'headers') -> returns(
        'Content-Type: text/html' . "\r\n" .
        'X-Header: foo' . "\r\n"
        )
      -> allowing($cache)->getString(any(), 'body') -> returns("\r\n" . 'my body')
      -> ignoring($cache)
      );
    
    $mime = $this->_createEntity($headers, $encoder, $cache);
    $mime->setBodyAsString('my body');
    $this->assertEqual(
      'Content-Type: text/html' . "\r\n" .
      'X-Header: foo' . "\r\n" .
      "\r\n" .
      'my body',
      $mime->toString()
      );
      
    $context->assertIsSatisfied();
  }
  
  public function testByteStreamBodyIsAppended()
  {
    $context = new Mockery();
    
    $h1 = $context->mock('Swift_Mime_ParameterizedHeader');
    $h2 = $context->mock('Swift_Mime_ParameterizedHeader');
    $encoder = $this->_getEncoder($context, false);
    $cache = $this->_getCache($context, false);
    $context->checking(Expectations::create()
      -> allowing($h1)->getFieldName() -> returns('Content-Type')
      -> allowing($h1)->getFieldBody() -> returns('text/html')
      -> allowing($h1)->toString() -> returns('Content-Type: text/html' . "\r\n")
      -> ignoring($h1)
      -> allowing($h2)->getFieldName() -> returns('X-Header')
      -> allowing($h2)->getFieldBody() -> returns('foo')
      -> allowing($h2)->toString() -> returns('X-Header: foo' . "\r\n")
      -> ignoring($h2)
      -> allowing($encoder)->encodeString('my body') -> returns('my body')
      -> ignoring($encoder)
      -> allowing($cache)->getString(any(), 'headers') -> returns(
        'Content-Type: text/html' . "\r\n" .
        'X-Header: foo' . "\r\n"
        )
      -> allowing($cache)->getString(any(), 'body') -> returns(
        "\r\n" .
        'my body'
        )
      -> ignoring($cache)
      );
    $headers = array($h1, $h2);
    
    $entity = $this->_createEntity($headers, $encoder, $cache);
    
    $os = $context->mock('Swift_OutputByteStream');
    $context->checking(Expectations::create()
      -> one($os)->read(optional()) -> returns('my body')
      -> allowing($os)->read(optional()) -> returns(false)
      -> ignoring($os)
      );
    
    $entity->setBodyAsByteStream($os);
    
    $this->assertEqual(
      'Content-Type: text/html' . "\r\n" .
      'X-Header: foo' . "\r\n" .
      "\r\n" .
      'my body',
      $entity->toString()
      );
      
    $context->assertIsSatisfied();
  }
  
  public function testContentTypeIsSetInHeader()
  {
    $context = new Mockery();
    
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->setFieldBodyModel('text/html')
      -> allowing($h)->getFieldName() -> returns('Content-Type')
      -> ignoring($h)
      );
    $headers = array($h);
    
    $mime = $this->_createEntity(
      $headers, $this->_getEncoder($context), $this->_getCache($context)
      );
    $mime->setContentType('text/html');
    
    $context->assertIsSatisfied();
  }
  
  public function testContentTypeIsReadFromHeader()
  {
    $context = new Mockery();
    
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->getFieldBodyModel() -> returns('application/xhtml+xml')
      -> allowing($h)->getFieldName() -> returns('Content-Type')
      -> ignoring($h)
      );
    $headers = array($h);
    
    $mime = $this->_createEntity(
      $headers, $this->_getEncoder($context), $this->_getCache($context)
      );
    $this->assertEqual('application/xhtml+xml', $mime->getContentType());
    
    $context->assertIsSatisfied();
  }
  
  public function testSettingEncoderUpdatesTransferEncoding()
  {
    $context = new Mockery();
    $h1 = $context->mock('Swift_Mime_ParameterizedHeader');
    $h2 = $context->mock('Swift_Mime_Header');
    $encoder = $context->mock('Swift_Mime_ContentEncoder');
    $context->checking(Expectations::create()
      -> allowing($h1)->getFieldName() -> returns('Content-Type')
      -> ignoring($h1)
      -> allowing($h2)->getFieldName() -> returns('Content-Transfer-Encoding')
      -> one($h2)->setFieldBodyModel('quoted-printable')
      -> one($h2)->setFieldBodyModel('base64')
      -> ignoring($h2)
      -> allowing($encoder)->getName() -> returns('base64')
      -> ignoring($encoder)
      );
    
    $headers = array($h1, $h2);
    
    $entity = $this->_createEntity(
      $headers, $this->_getEncoder($context), $this->_getCache($context)
      );
    
    $entity->setEncoder($encoder);
    
    $context->assertIsSatisfied();
  }
  
  public function testAddingChildrenGeneratesBoundary()
  {
    $context = new Mockery();
    
    $h1 = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h1)->setParameter('boundary', any())
      -> allowing($h1)->getFieldName() -> returns('Content-Type')
      -> ignoring($h1)
      );
    $headers1 = array($h1);
    
    $entity1 = $this->_createEntity(
      $headers1, $this->_getEncoder($context), $this->_getCache($context)
      );
    
    $h2 = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> allowing($h2)->getFieldName() -> returns('Content-Type')
      -> ignoring($h2)
      );
    $headers2 = array($h2);
    
    $entity2 = $context->mock('Swift_Mime_MimeEntity');
    $context->checking(Expectations::create()
      -> allowing($entity2)->getHeaders() -> returns($headers2)
      -> allowing($entity2)->getNestingLevel() -> returns(Swift_Mime_MimeEntity::LEVEL_ATTACHMENT)
      -> ignoring($entity2)
      );
    
    $entity1->setChildren(array($entity2));
    
    $context->assertIsSatisfied();
  }
  
  public function testChildrenOfLevelAttachmentOrLessGeneratesMultipartMixed()
  {
    $context = new Mockery();
    
    for ($level = Swift_Mime_MimeEntity::LEVEL_ATTACHMENT;
      $level > Swift_Mime_MimeEntity::LEVEL_TOP; $level--)
    {
      $h1 = $context->mock('Swift_Mime_ParameterizedHeader');
      $context->checking(Expectations::create()
        -> one($h1)->setFieldBodyModel('multipart/mixed')
        -> ignoring($h1)->setFieldBodyModel(any())
        -> allowing($h1)->getFieldName() -> returns('Content-Type')
        -> atLeast(1)->of($h1)->setParameter('boundary', any())
        -> ignoring($h1)
        );
    
      $headers1 = array($h1);
    
      $entity = $this->_createEntity(
        $headers1, $this->_getEncoder($context), $this->_getCache($context)
        );
      
      $h2 = $context->mock('Swift_Mime_ParameterizedHeader');
      $context->checking(Expectations::create()
        -> allowing($h2)->getFieldName() -> returns('Content-Type')
        -> ignoring($h2)
        );
      $headers2 = array($h2);
      
      $entity2 = $context->mock('Swift_Mime_MimeEntity');
      $context->checking(Expectations::create()
        -> allowing($entity2)->getHeaders() -> returns($headers2)
        -> allowing($entity2)->getNestingLevel() -> returns($level)
        -> ignoring($entity2)
        );
    
      $entity->setChildren(array($entity2));
    }
    
    $context->assertIsSatisfied();
  }
  
  public function testChildrenOfLevelEmbeddedOrLessGeneratesMultipartRelated()
  {
    $context = new Mockery();
    
    for ($level = Swift_Mime_MimeEntity::LEVEL_EMBEDDED;
      $level > Swift_Mime_MimeEntity::LEVEL_ATTACHMENT; $level--)
    {
      $h1 = $context->mock('Swift_Mime_ParameterizedHeader');
      $context->checking(Expectations::create()
        -> one($h1)->setFieldBodyModel('multipart/related')
        -> allowing($h1)->getFieldName() -> returns('Content-Type')
        -> atLeast(1)->of($h1)->setParameter('boundary', any())
        -> ignoring($h1)
        );
    
      $headers1 = array($h1);
      
      $entity = $this->_createEntity(
        $headers1, $this->_getEncoder($context), $this->_getCache($context)
        );
      
      $h2 = $context->mock('Swift_Mime_ParameterizedHeader');
      $context->checking(Expectations::create()
        -> allowing($h2)->getFieldName() -> returns('Content-Type')
        -> ignoring($h2)
        );
      
      $headers2 = array($h2);
    
      $entity2 = $context->mock('Swift_Mime_MimeEntity');
      $context->checking(Expectations::create()
        -> allowing($entity2)->getHeaders() -> returns($headers2)
        -> allowing($entity2)->getNestingLevel() -> returns($level)
        -> ignoring($entity2)
        );
    
      $entity->setChildren(array($entity2));
    }
    
    $context->assertIsSatisfied();
  }
  
  public function testChildrenOfLevelSubpartOrLessGeneratesMultipartAlternative()
  {
    $context = new Mockery();
    
    for ($level = Swift_Mime_MimeEntity::LEVEL_SUBPART;
      $level > Swift_Mime_MimeEntity::LEVEL_EMBEDDED; $level--)
    {
      $h1 = $context->mock('Swift_Mime_ParameterizedHeader');
      $context->checking(Expectations::create()
        -> one($h1)->setFieldBodyModel('multipart/alternative')
        -> allowing($h1)->getFieldName() -> returns('Content-Type')
        -> atLeast(1)->of($h1)->setParameter('boundary', any())
        -> ignoring($h1)
        );
      
      $headers1 = array($h1);
      
      $entity = $this->_createEntity(
        $headers1, $this->_getEncoder($context), $this->_getCache($context)
        );
      
      $h2 = $context->mock('Swift_Mime_ParameterizedHeader');
      $context->checking(Expectations::create()
        -> allowing($h2)->getFieldName() -> returns('Content-Type')
        -> ignoring($h2)
        );
      
      $headers2 = array($h2);
    
      $entity2 = $context->mock('Swift_Mime_MimeEntity');
      $context->checking(Expectations::create()
        -> allowing($entity2)->getHeaders() -> returns($headers2)
        -> allowing($entity2)->getNestingLevel() -> returns($level)
        -> ignoring($entity2)
        );
    
      $entity->setChildren(array($entity2));
    }
    
    $context->assertIsSatisfied();
  }
  
  public function testHighestLevelChildDeterminesContentType()
  {
    $context = new Mockery();
    
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
        $subentity = $context->mock('Swift_Mime_MimeEntity');
        $context->checking(Expectations::create()
          -> allowing($subentity)->getNestingLevel() -> returns($level)
          -> ignoring($subentity)
          );
        
        $children[] = $subentity;
      }
      
      $h1 = $context->mock('Swift_Mime_ParameterizedHeader');
      $context->checking(Expectations::create()
        -> one($h1)->setFieldBodyModel($combination['type'])
        -> allowing($h1)->getFieldName() -> returns('Content-Type')
        -> atLeast(1)->of($h1)->setParameter('boundary', any())
        -> ignoring($h1)
        );
      
      $headers = array($h1);
      
      $entity = $this->_createEntity(
        $headers, $this->_getEncoder($context), $this->_getCache($context)
        );
      
      $entity->setChildren($children);
    }
    
    $context->assertIsSatisfied();
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
    
    $context = new Mockery();
    
    $h1 = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> allowing($h1)->getFieldName() -> returns('Content-Type')
      -> ignoring($h1)
      );
    $headers1 = array($h1);
    
    $entity1 = $this->_createEntity(
      $headers1, $this->_getEncoder($context), $this->_getCache($context)
      );
    
    $h2 = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> allowing($h2)->getFieldName() -> returns('Content-Type')
      -> ignoring($h2)
      );
    $headers2 = array($h2);
    
    $entity2 = $context->mock('Swift_Mime_MimeEntity');
    $context->checking(Expectations::create()
      -> allowing($entity2)->getHeaders() -> returns($headers2)
      -> allowing($entity2)->getNestingLevel() -> returns(Swift_Mime_MimeEntity::LEVEL_ATTACHMENT)
      -> ignoring($entity2)
      );
    
    $entity1->setChildren(array($entity2));
    
    $this->assertPattern(
      '/^[a-zA-Z0-9\'\(\)\+_\-,\.\/:=\?\ ]{0,69}[a-zA-Z0-9\'\(\)\+_\-,\.\/:=\?]$/D',
      $entity1->getBoundary()
      );
    
    $context->assertIsSatisfied();
  }
  
  public function testBoundaryNeverChanges()
  {
    $context = new Mockery();
    $h1 = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> allowing($h1)->getFieldName() -> returns('Content-Type')
      -> ignoring($h1)
      );
    $headers1 = array($h1);
    
    $entity1 = $this->_createEntity(
      $headers1, $this->_getEncoder($context), $this->_getCache($context)
      );
    
    $h2 = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> allowing($h2)->getFieldName() -> returns('Content-Type')
      -> ignoring($h2)
      );
    $headers2 = array($h2);
    
    $entity2 = $context->mock('Swift_Mime_MimeEntity');
    $context->checking(Expectations::create()
      -> allowing($entity2)->getHeaders() -> returns($headers2)
      -> allowing($entity2)->getNestingLevel() -> returns(Swift_Mime_MimeEntity::LEVEL_ATTACHMENT)
      -> ignoring($entity2)
      );
    
    $entity1->setChildren(array($entity2));
    
    $boundary = $entity1->getBoundary();
    for ($i = 0; $i < 10; $i++)
    {
      $this->assertEqual($boundary, $entity1->getBoundary());
    }
    
    $context->assertIsSatisfied();
  }
  
  public function testBoundaryCanBeManuallySet()
  {
    $context = new Mockery();
    $h1 = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> allowing($h1)->getFieldName() -> returns('Content-Type')
      -> ignoring($h1)
      );
    $headers1 = array($h1);
    
    $entity1 = $this->_createEntity(
      $headers1, $this->_getEncoder($context), $this->_getCache($context)
      );
    
    $h2 = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> allowing($h2)->getFieldName() -> returns('Content-Type')
      -> ignoring($h2)
      );
    $headers2 = array($h2);
    
    $entity2 = $context->mock('Swift_Mime_MimeEntity');
    $context->checking(Expectations::create()
      -> allowing($entity2)->getHeaders() -> returns($headers2)
      -> allowing($entity2)->getNestingLevel() -> returns(Swift_Mime_MimeEntity::LEVEL_ATTACHMENT)
      -> ignoring($entity2)
      );
      
    $entity1->setBoundary('my_boundary');
    
    $entity1->setChildren(array($entity2));
    
    $this->assertEqual('my_boundary', $entity1->getBoundary());
    
    $context->assertIsSatisfied();
  }
  
  public function testChildrenAppearInString()
  {
    /* -- RFC 2046, 5.1.1.
     (excerpt too verbose to paste here)
     */
    
    $context = new Mockery();
    $h1 = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> allowing($h1)->getFieldName() -> returns('Content-Type')
      -> allowing($h1)->getFieldBody() -> returns(
        'multipart/alternative;' . "\r\n" .
        ' boundary="_=_foo_=_"'
        )
      -> allowing($h1)->toString() -> returns(
        'Content-Type: multipart/alternative;' . "\r\n" .
        ' boundary="_=_foo_=_"' . "\r\n"
        )
      -> ignoring($h1)
      );
    
    $headers1 = array($h1);
    
    $cache = $this->_getCache($context, false);
    $context->checking(Expectations::create()
      -> allowing($cache)->getString(any(), 'headers') -> returns(
        'Content-Type: multipart/alternative;' . "\r\n" .
        ' boundary="_=_foo_=_"' . "\r\n"
        )
      -> ignoring($cache)
      );
    
    $entity1 = $this->_createEntity($headers1, $this->_getEncoder($context), $cache);
    $entity1->setBoundary('_=_foo_=_');
    
    $entity2 = $context->mock('Swift_Mime_MimeEntity');
    $context->checking(Expectations::create()
      -> allowing($entity2)->getNestingLevel() -> returns(Swift_Mime_MimeEntity::LEVEL_SUBPART)
      -> allowing($entity2)->toString() -> returns(
        'Content-Type: text/plain' . "\r\n" .
        "\r\n" .
        'foobar test'
        )
      -> ignoring($entity2)
      );
      
    $entity3 = $context->mock('Swift_Mime_MimeEntity');
    $context->checking(Expectations::create()
      -> allowing($entity3)->getNestingLevel() -> returns(Swift_Mime_MimeEntity::LEVEL_SUBPART)
      -> allowing($entity3)->toString() -> returns(
        'Content-Type: text/html' . "\r\n" .
        "\r\n" .
        'foobar <strong>test</strong>'
        )
      -> ignoring($entity3)
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
    
    $context->assertIsSatisfied();
  }
  
  public function testMixingLevelsIsHierarchical()
  {
    $context = new Mockery();
    $h1 = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> allowing($h1)->getFieldName() -> returns('Content-Type')
      -> allowing($h1)->getFieldBody() -> returns(
        'multipart/mixed;' . "\r\n" .
        ' boundary="_=_foo_=_"'
        )
      -> allowing($h1)->toString() -> returns(
        'Content-Type: multipart/mixed;' . "\r\n" .
        ' boundary="_=_foo_=_"' . "\r\n"
        )
      -> ignoring($h1)
      );
    
    $headers = array($h1);
    
    $cache = $this->_getCache($context, false);
    $context->checking(Expectations::create()
      //Parent generated content
      -> one($cache)->getString(any(), 'headers') -> returns(
        'Content-Type: multipart/mixed;' . "\r\n" .
        ' boundary="_=_foo_=_"' . "\r\n"
        )
      //Child generated content
      -> one($cache)->getString(any(), 'headers') -> returns(
        'Content-Type: multipart/alternative;' . "\r\n" .
        ' boundary="_=_bar_=_"' . "\r\n"
        )
      -> ignoring($cache)
      );
    
    $entity1 = $this->_createEntity($headers, $this->_getEncoder($context), $cache);
    $entity1->setBoundary('_=_foo_=_');
    
    //Create some entities which nest differently
    $entity2 = $context->mock('Swift_Mime_MimeEntity');
    $context->checking(Expectations::create()
      -> allowing($entity2)->getNestingLevel() -> returns(Swift_Mime_MimeEntity::LEVEL_ATTACHMENT)
      -> allowing($entity2)->toString() -> returns(
        'Content-Type: application/octet-stream' . "\r\n" .
        "\r\n" .
        'foo'
        )
      -> ignoring($entity2)
      );
      
    $entity3 = $context->mock('Swift_Mime_MimeEntity');
    $context->checking(Expectations::create()
      -> allowing($entity3)->getNestingLevel() -> returns(Swift_Mime_MimeEntity::LEVEL_SUBPART)
      -> allowing($entity3)->toString() -> returns(
        'Content-Type: text/plain' . "\r\n" .
        "\r\n" .
        'xyz'
        )
      -> ignoring($entity3)
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
      
    $context->assertIsSatisfied();
  }
  
  public function testSettingEncoderNotifiesChildren()
  {
    $context = new Mockery();
    
    $encoder1 = $this->_getEncoder($context);
    $encoder2 = $this->_getEncoder($context);
    $child1 = $context->mock('Swift_Mime_MimeEntity');
    $child2 = $context->mock('Swift_Mime_MimeEntity');
    $context->checking(Expectations::create()
      -> one($child1)->encoderChanged($encoder2)
      -> ignoring($child1)
      -> one($child2)->encoderChanged($encoder2)
      -> ignoring($child2)
      );
    $mime = $this->_createEntity(array(), $encoder1, $this->_getCache($context));
    $mime->setChildren(array($child1, $child2));
    $mime->setEncoder($encoder2);
    
    $context->assertIsSatisfied();
  }
  
  public function testEncoderChangeIsCascadedToChildren()
  {
    $context = new Mockery();
    
    $encoder1 = $this->_getEncoder($context);
    $encoder2 = $this->_getEncoder($context);
    $child1 = $context->mock('Swift_Mime_MimeEntity');
    $child2 = $context->mock('Swift_Mime_MimeEntity');
    $context->checking(Expectations::create()
      -> one($child1)->encoderChanged($encoder2)
      -> ignoring($child1)
      -> one($child2)->encoderChanged($encoder2)
      -> ignoring($child2)
      );
    $mime = $this->_createEntity(array(), $encoder1, $this->_getCache($context));
    $mime->setChildren(array($child1, $child2));
    $mime->encoderChanged($encoder2);
    
    $context->assertIsSatisfied();
  }
  
  public function testCharsetChangeIsCascadedToChildren()
  {
    $context = new Mockery();
    
    $child1 = $context->mock('Swift_Mime_MimeEntity');
    $child2 = $context->mock('Swift_Mime_MimeEntity');
    $context->checking(Expectations::create()
      -> one($child1)->charsetChanged('windows-874')
      -> ignoring($child1)
      -> one($child2)->charsetChanged('windows-874')
      -> ignoring($child2)
      );
    $mime = $this->_createEntity(
      array(), $this->_getEncoder($context), $this->_getCache($context)
      );
    $mime->setChildren(array($child1, $child2));
    $mime->charsetChanged('windows-874');
    
    $context->assertIsSatisfied();
  }
  
  public function testIdIsSetInHeader()
  {
    /* -- RFC 2045, 7.
    In constructing a high-level user agent, it may be desirable to allow
    one body to make reference to another.  Accordingly, bodies may be
    labelled using the "Content-ID" header field, which is syntactically
    identical to the "Message-ID" header field
    */
    
    $context = new Mockery();
    $h = $context->mock('Swift_Mime_Header');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->setFieldBodyModel('foo@bar')
      -> allowing($h)->getFieldName() -> returns('Content-ID')
      -> ignoring($h)
      );
    $mime = $this->_createEntity(
      array($h), $this->_getEncoder($context), $this->_getCache($context)
      );
    $mime->setId('foo@bar');
    $context->assertIsSatisfied();
  }
  
  public function testIdIsReadFromHeader()
  {
    $context = new Mockery();
    $h = $context->mock('Swift_Mime_Header');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->getFieldBodyModel() -> returns('xyz@somewhere.tld')
      -> allowing($h)->getFieldName() -> returns('Content-ID')
      -> ignoring($h)
      );
    $mime = $this->_createEntity(
      array($h), $this->_getEncoder($context), $this->_getCache($context)
      );
    $this->assertEqual('xyz@somewhere.tld', $mime->getId());
    $context->assertIsSatisfied();
  }
  
  public function testIdIsAutoGenerated()
  {
    $context = new Mockery();
    $h = $context->mock('Swift_Mime_Header');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->setFieldBodyModel(pattern('/^.*?@.*?$/D'))
      -> allowing($h)->getFieldName() -> returns('Content-ID')
      -> ignoring($h)
      );
    $mime = $this->_createEntity(
      array($h), $this->_getEncoder($context), $this->_getCache($context)
      );
    $context->assertIsSatisfied();
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
    
    $context = new Mockery();
    $mime = $this->_createEntity(
      array(), $this->_getEncoder($context), $this->_getCache($context)
      );
    $mime->setDescription('my mime entity');
    $this->assertEqual('my mime entity', $mime->getDescription());
  }
  
  public function testEncoderIsUsedForStringGeneration()
  {
    $context = new Mockery();
    
    $h1 = $context->mock('Swift_Mime_ParameterizedHeader');
    $h2 = $context->mock('Swift_Mime_Header');
    $encoder = $this->_getEncoder($context, false);
    
    $context->checking(Expectations::create()
      -> allowing($h1)->getFieldName() -> returns('Content-Type')
      -> allowing($h1)->getFieldBody() -> returns('text/html')
      -> allowing($h1)->toString() -> returns('Content-Type: text/html' . "\r\n")
      -> ignoring($h1)
      -> allowing($h2)->getFieldName() -> returns('X-Header')
      -> allowing($h2)->getFieldBody() -> returns('foo')
      -> allowing($h2)->toString() -> returns('X-Header: foo' . "\r\n")
      -> ignoring($h2)
      -> one($encoder)->encodeString(
        'my body', optional(), optional()
        ) ->  returns('my body')
      -> ignoring($encoder)
      );
    
    $headers = array($h1, $h2);
    
    $mime = $this->_createEntity($headers, $encoder, $this->_getCache($context));
    $mime->setBodyAsString('my body');
    $mime->toString();
    
    $context->assertIsSatisfied();
  }
  
  public function testMaxLineLengthIsProvidedForEncoding()
  {
    $context = new Mockery();
    
    $h1 = $context->mock('Swift_Mime_ParameterizedHeader');
    $h2 = $context->mock('Swift_Mime_Header');
    $encoder = $this->_getEncoder($context, false);
    
    $context->checking(Expectations::create()
      -> allowing($h1)->getFieldName() -> returns('Content-Type')
      -> allowing($h1)->getFieldBody() -> returns('text/html')
      -> allowing($h1)->toString() -> returns('Content-Type: text/html' . "\r\n")
      -> ignoring($h1)
      -> allowing($h2)->getFieldName() -> returns('X-Header')
      -> allowing($h2)->getFieldBody() -> returns('foo')
      -> allowing($h2)->toString() -> returns('X-Header: foo' . "\r\n")
      -> ignoring($h2)
      -> one($encoder)->encodeString('my body', 0, 78) ->  returns('my body')
      -> ignoring($encoder)
      );
    
    $headers = array($h1, $h2);
    
    $entity = $this->_createEntity($headers, $encoder, $this->_getCache($context));
    $entity->setMaxLineLength(78);
    $entity->setBodyAsString('my body');
    
    $entity->toString();
    
    $context->assertIsSatisfied();
  }
  
  public function testEntityCanBeWrittenToByteStream()
  {
    $context = new Mockery();
    $cache = $this->_getCache($context, false);
    $is = $context->mock('Swift_InputByteStream');
    $context->checking(Expectations::create()
      -> ignoring($is)
      -> atLeast(1)->of($cache)->exportToByteStream(any(), any(), $is)
      -> ignoring($cache)
      );
    
    $entity = $this->_createEntity(array(), $this->_getEncoder($context), $cache);
    $entity->setBodyAsString('test');
    $entity->toByteStream($is);
    
    $context->assertIsSatisfied();
  }
  
  public function testOrderingOfAlternativePartsCanBeSpecified_1()
  {
    $context = new Mockery();
    
    $h1 = $context->mock('Swift_Mime_ParameterizedHeader');
    $cache = $this->_getCache($context, false);
    $context->checking(Expectations::create()
      -> allowing($h1)->getFieldName() -> returns('Content-Type')
      -> allowing($h1)->getFieldBody() -> returns(
        'multipart/alternative;' . "\r\n" .
        ' boundary="_=_foo_=_"'
        )
      -> allowing($h1)->toString() -> returns(
        'Content-Type: multipart/alternative;' . "\r\n" .
        ' boundary="_=_foo_=_"' . "\r\n"
        )
      -> ignoring($h1)
      -> allowing($cache)->getString(any(), 'headers') -> returns(
        'Content-Type: multipart/alternative;' . "\r\n" .
        ' boundary="_=_foo_=_"' . "\r\n"
        )
      -> ignoring($cache)
      );
    $headers1 = array($h1);
    
    $entity1 = $this->_createEntity($headers1, $this->_getEncoder($context), $cache);
    $entity1->setBoundary('_=_foo_=_');
    
    $entity2 = $context->mock('Swift_Mime_MimeEntity');
    $entity3 = $context->mock('Swift_Mime_MimeEntity');
    $context->checking(Expectations::create()
      -> allowing($entity2)->getNestingLevel() -> returns(Swift_Mime_MimeEntity::LEVEL_SUBPART)
      -> allowing($entity2)->getContentType() -> returns('text/plain')
      -> allowing($entity2)->toString() -> returns(
        'Content-Type: text/plain' . "\r\n" .
        "\r\n" .
        'foobar test'
        )
      -> ignoring($entity2)
      
      -> allowing($entity3)->getNestingLevel() -> returns(Swift_Mime_MimeEntity::LEVEL_SUBPART)
      -> allowing($entity3)->getContentType() -> returns('text/html')
      -> allowing($entity3)->toString() -> returns(
        'Content-Type: text/html' . "\r\n" .
        "\r\n" .
        'foobar <strong>test</strong>'
        )
      -> ignoring($entity3)
      );
    
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
    
    $context->assertIsSatisfied();
  }
  
  public function testOrderingOfAlternativePartsCanBeSpecified_2()
  {
    $context = new Mockery();
    
    $h1 = $context->mock('Swift_Mime_ParameterizedHeader');
    $cache = $this->_getCache($context, false);
    $context->checking(Expectations::create()
      -> allowing($h1)->getFieldName() -> returns('Content-Type')
      -> allowing($h1)->getFieldBody() -> returns(
        'multipart/alternative;' . "\r\n" .
        ' boundary="_=_foo_=_"'
        )
      -> allowing($h1)->toString() -> returns(
        'Content-Type: multipart/alternative;' . "\r\n" .
        ' boundary="_=_foo_=_"' . "\r\n"
        )
      -> ignoring($h1)
      -> allowing($cache)->getString(any(), 'headers') -> returns(
        'Content-Type: multipart/alternative;' . "\r\n" .
        ' boundary="_=_foo_=_"' . "\r\n"
        )
      -> ignoring($cache)
      );
    $headers1 = array($h1);
    
    $entity1 = $this->_createEntity($headers1, $this->_getEncoder($context), $cache);
    $entity1->setBoundary('_=_foo_=_');
    
    $entity2 = $context->mock('Swift_Mime_MimeEntity');
    $entity3 = $context->mock('Swift_Mime_MimeEntity');
    $context->checking(Expectations::create()
      -> allowing($entity2)->getNestingLevel() -> returns(Swift_Mime_MimeEntity::LEVEL_SUBPART)
      -> allowing($entity2)->getContentType() -> returns('text/plain')
      -> allowing($entity2)->toString() -> returns(
        'Content-Type: text/plain' . "\r\n" .
        "\r\n" .
        'foobar test'
        )
      -> ignoring($entity2)
      
      -> allowing($entity3)->getNestingLevel() -> returns(Swift_Mime_MimeEntity::LEVEL_SUBPART)
      -> allowing($entity3)->getContentType() -> returns('text/html')
      -> allowing($entity3)->toString() -> returns(
        'Content-Type: text/html' . "\r\n" .
        "\r\n" .
        'foobar <strong>test</strong>'
        )
      -> ignoring($entity3)
      );
    
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
    
    $context->assertIsSatisfied();
  }
  
  public function testFluidInterface()
  {
    $context = new Mockery();
    $child = $context->mock('Swift_Mime_MimeEntity');
    $context->checking(Expectations::create()
      -> allowing($child)->getNestingLevel() -> returns(Swift_Mime_MimeEntity::LEVEL_SUBPART)
      -> ignoring($child)
      );
    $mime = $this->_createEntity(
      array(), $this->_getEncoder($context), $this->_getCache($context)
      );
    $ref = $mime
      ->setContentType('text/plain')
      ->setEncoder($this->_getEncoder($context))
      ->setId('foo@bar')
      ->setDescription('my description')
      ->setMaxLineLength(998)
      ->setBodyAsString('xx')
      ->setNestingLevel(10)
      ->setBoundary('xyz')
      ->setChildren(array())
      ->setHeaders(array())
      ;
    
    $this->assertReference($mime, $ref);
    
    $context->assertIsSatisfied();
  }
  
  // -- Private helpers
  
  abstract protected function _createEntity($headers, $encoder, $cache);
  
  protected function _getEncoder($mockery, $ignore = true)
  {
    $encoder = $mockery->mock('Swift_Mime_ContentEncoder');
    $mockery->checking(Expectations::create()
      -> allowing($encoder)->getName() -> returns('quoted-printable')
      );
    if ($ignore)
    {
      $mockery->checking(Expectations::create()
        -> ignoring($encoder)
        );
    }
    return $encoder;
  }
  
  protected function _getCache($mockery, $ignore = true)
  {
    $cache = $mockery->mock('Swift_KeyCache');
    if ($ignore)
    {
      $mockery->checking(Expectations::create()
        -> ignoring($cache)
        );
    }
    return $cache;
  }
  
}
