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
  
  public function testDateHeaderCreationUsesFactory()
  {
    $factory = $this->_mockery()->mock('Swift_Mime_HeaderFactory');
    $this->_mockery()->checking(Expectations::create()
      -> one($factory)->createDateHeader('X-Date', 123) -> returns($this->_stubHeader())
      );
    $this->_fillInHeaders($factory);
    $entity = $this->_createEntity($factory, $this->_createEncoder(),
      $this->_createCache()
      );
    $entity->addDateHeader('X-Date', 123);
  }
  
  public function testMailboxHeaderCreationUsesFactory()
  {
    $factory = $this->_mockery()->mock('Swift_Mime_HeaderFactory');
    $this->_mockery()->checking(Expectations::create()
      -> one($factory)->createMailboxHeader('X-Foo', 'z@y') -> returns($this->_stubHeader())
      );
    $this->_fillInHeaders($factory);
    $entity = $this->_createEntity($factory, $this->_createEncoder(),
      $this->_createCache()
      );
    $entity->addMailboxHeader('X-Foo', 'z@y');
  }
  
  public function testTextHeaderCreationUsesFactory()
  {
    $factory = $this->_mockery()->mock('Swift_Mime_HeaderFactory');
    $this->_mockery()->checking(Expectations::create()
      -> one($factory)->createTextHeader('X-Foo', 'x') -> returns($this->_stubHeader())
      );
    $this->_fillInHeaders($factory);
    $entity = $this->_createEntity($factory, $this->_createEncoder(),
      $this->_createCache()
      );
    $entity->addTextHeader('X-Foo', 'x');
  }
  
  public function testIdHeaderCreationUsesFactory()
  {
    $factory = $this->_mockery()->mock('Swift_Mime_HeaderFactory');
    $this->_mockery()->checking(Expectations::create()
      -> one($factory)->createIdHeader('X-ID', 'x@y') -> returns($this->_stubHeader())
      );
    $this->_fillInHeaders($factory);
    $entity = $this->_createEntity($factory, $this->_createEncoder(),
      $this->_createCache()
      );
    $entity->addIdHeader('X-ID', 'x@y');
  }
  
  public function testPathHeaderCreationUsesFactory()
  {
    $factory = $this->_mockery()->mock('Swift_Mime_HeaderFactory');
    $this->_mockery()->checking(Expectations::create()
      -> one($factory)->createPathHeader('X-Path', 'x@y') -> returns($this->_stubHeader())
      );
    $this->_fillInHeaders($factory);
    $entity = $this->_createEntity($factory, $this->_createEncoder(),
      $this->_createCache()
      );
    $entity->addPathHeader('X-Path', 'x@y');
  }
  
  public function testParamHeaderCreationUsesFactory()
  {
    $factory = $this->_mockery()->mock('Swift_Mime_HeaderFactory');
    $this->_mockery()->checking(Expectations::create()
      -> one($factory)->createParameterizedHeader('X-Foo', 'x', array('a'=>'b')) -> returns($this->_stubHeader())
      );
    $this->_fillInHeaders($factory);
    $entity = $this->_createEntity($factory, $this->_createEncoder(),
      $this->_createCache()
      );
    $entity->addParameterizedHeader('X-Foo', 'x', array('a'=>'b'));
  }
  
  public function testHeadersAreReturned()
  {
    $factory = $this->_mockery()->mock('Swift_Mime_HeaderFactory');
    $this->_mockery()->checking(Expectations::create()
      -> ignoring($factory)->createTextHeader('X-Foo', 'x') -> returns($this->_stubHeader())
      );
    $this->_fillInHeaders($factory);
    $entity = $this->_createEntity($factory, $this->_createEncoder(),
      $this->_createCache()
      );
    $entity->addTextHeader('X-Foo', 'x');
    $this->assertTrue(count($entity->getHeaders()) > 0,
      '%s: Headers should be returned'
      );
  }
  
  public function testHeadersAreInstancesOfHeader()
  {
    $factory = $this->_mockery()->mock('Swift_Mime_HeaderFactory');
    $this->_mockery()->checking(Expectations::create()
      -> ignoring($factory)->createTextHeader('X-Foo', 'x') -> returns($this->_stubHeader())
      );
    $this->_fillInHeaders($factory);
    $entity = $this->_createEntity($factory, $this->_createEncoder(),
      $this->_createCache()
      );
    $entity->addTextHeader('X-Foo', 'x');
    foreach ($entity->getHeaders() as $header)
    {
      $this->assertIsA($header, 'Swift_Mime_Header');
    }
  }
  
  public function testHeaderObjectsCanBeFetched()
  {
    $h = $this->_mockery()->mock('Swift_Mime_Header');
    $factory = $this->_mockery()->mock('Swift_Mime_HeaderFactory');
    $this->_mockery()->checking(Expectations::create()
      -> ignoring($h)->getFieldName() -> returns('X-Foo')
      -> ignoring($factory)->createTextHeader('X-Foo', 'x') -> returns($h)
      );
    $this->_fillInHeaders($factory);
    $entity = $this->_createEntity($factory, $this->_createEncoder(),
      $this->_createCache()
      );
    $entity->addTextHeader('X-Foo', 'x');
    $this->assertEqual($h, $entity->getHeader('x-foo'));
  }
  
  public function testMultipleHeaderObjectsCanBeFetched()
  {
    $h1 = $this->_mockery()->mock('Swift_Mime_Header');
    $h2 = $this->_mockery()->mock('Swift_Mime_Header');
    $factory = $this->_mockery()->mock('Swift_Mime_HeaderFactory');
    $this->_mockery()->checking(Expectations::create()
      -> ignoring($h1)->getFieldName() -> returns('X-Foo')
      -> ignoring($h2)->getFieldName() -> returns('X-Foo')
      -> ignoring($factory)->createTextHeader('X-Foo', 'x') -> returns($h1)
      -> ignoring($factory)->createTextHeader('X-Foo', 'y') -> returns($h2)
      );
    $this->_fillInHeaders($factory);
    $entity = $this->_createEntity($factory, $this->_createEncoder(),
      $this->_createCache()
      );
    $entity->addTextHeader('X-Foo', 'x');
    $entity->addTextHeader('X-Foo', 'y');
    $this->assertEqual(array($h1, $h2), $entity->getHeaderCollection('x-foo'));
  }
  
  public function testHeadersCanBeRemoved()
  {
    $h = $this->_mockery()->mock('Swift_Mime_Header');
    $factory = $this->_mockery()->mock('Swift_Mime_HeaderFactory');
    $this->_mockery()->checking(Expectations::create()
      -> ignoring($h)->getFieldName() -> returns('X-Foo')
      -> ignoring($factory)->createTextHeader('X-Foo', 'x') -> returns($h)
      );
    $this->_fillInHeaders($factory);
    $entity = $this->_createEntity($factory, $this->_createEncoder(),
      $this->_createCache()
      );
    $entity->addTextHeader('X-Foo', 'x');
    $beforeCount = count($entity->getHeaderCollection('x-foo'));
    $entity->removeHeader('x-foo');
    $this->assertEqual($beforeCount - 1,
      count($entity->getHeaderCollection('x-foo'))
      );
  }
  
  public function testContentTypeIsSetInHeader()
  {
    $factory = $this->_mockery()->mock('Swift_Mime_HeaderFactory');
    $this->_mockery()->checking(Expectations::create()
      -> one($factory)->createParameterizedHeader('Content-Type', 'image/jpeg', optional())
      );
    $this->_fillInHeaders($factory);
    $entity = $this->_createEntity($factory, $this->_createEncoder(),
      $this->_createCache()
      );
    $entity->setContentType('image/jpeg');
  }
  
  public function testContentTypeIsReadFromHeader()
  {
    $h = $this->_mockery()->mock('Swift_Mime_ParameterizedHeader');
    $factory = $this->_mockery()->mock('Swift_Mime_HeaderFactory');
    $this->_mockery()->checking(Expectations::create()
      -> one($h)->getFieldBodyModel() -> returns('image/gif')
      -> ignoring($h)->getFieldName() -> returns('Content-Type')
      -> ignoring($h)
      -> ignoring($factory)->createParameterizedHeader(any(), optional()) -> returns($h)
      );
    $this->_fillInHeaders($factory);
    $entity = $this->_createEntity($factory, $this->_createEncoder(),
      $this->_createCache()
      );
    $entity->setContentType('image/gif');
    $this->assertEqual('image/gif', $entity->getContentType());
  }
  
  public function testHeadersAppearInString()
  {
    $h1 = $this->_mockery()->mock('Swift_Mime_ParameterizedHeader');
    $h2 = $this->_mockery()->mock('Swift_Mime_ParameterizedHeader');
    $factory = $this->_mockery()->mock('Swift_Mime_HeaderFactory');
    $cache = $this->_createCache(false);
    $this->_mockery()->checking(Expectations::create()
      -> ignoring($h1)->getFieldName() -> returns('Content-Type')
      -> ignoring($h1)->getFieldBody() -> returns('text/html')
      -> ignoring($h1)->toString() -> returns('Content-Type: text/html' . "\r\n")
      -> ignoring($h1)
      -> ignoring($h2)->getFieldName() -> returns('X-Header')
      -> ignoring($h2)->getFieldBody() -> returns('foo')
      -> ignoring($h2)->toString() -> returns('X-Header: foo' . "\r\n")
      -> ignoring($h2)
      -> ignoring($factory)->createParameterizedHeader('Content-Type', optional()) -> returns($h1)
      -> ignoring($factory)->createParameterizedHeader('X-Header', optional()) -> returns($h2)
      -> ignoring($cache)->getString(any(), 'headers') -> returns(
        'Content-Type: text/html' . "\r\n" .
       'X-Header: foo' . "\r\n"
       )
      -> ignoring($cache)
      );
    $this->_fillInHeaders($factory);
    $entity = $this->_createEntity($factory, $this->_createEncoder(), $cache);
    $entity->setContentType('text/html');
    $entity->addParameterizedHeader('X-Header', 'foo');
    $this->assertEqual(
      'Content-Type: text/html' . "\r\n" .
      'X-Header: foo' . "\r\n",
      $entity->toString()
      );
  }
  
  public function XtestBodyIsAppended()
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
  
  public function XtestByteStreamBodyIsAppended()
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
  
  public function XtestSettingEncoderUpdatesTransferEncoding()
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
  
  public function XtestAddingChildrenGeneratesBoundary()
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
  
  public function XtestChildrenOfLevelAttachmentOrLessGeneratesMultipartMixed()
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
  
  public function XtestChildrenOfLevelEmbeddedOrLessGeneratesMultipartRelated()
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
  
  public function XtestChildrenOfLevelSubpartOrLessGeneratesMultipartAlternative()
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
  
  public function XtestHighestLevelChildDeterminesContentType()
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
  
  public function XtestBoundaryCanBeRetrieved()
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
  
  public function XtestBoundaryNeverChanges()
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
  
  public function XtestBoundaryCanBeManuallySet()
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
  
  public function XtestChildrenAppearInString()
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
  
  public function XtestMixingLevelsIsHierarchical()
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
  
  public function XtestSettingEncoderNotifiesChildren()
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
  
  public function XtestEncoderChangeIsCascadedToChildren()
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
  
  public function XtestCharsetChangeIsCascadedToChildren()
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
  
  public function XtestIdIsSetInHeader()
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
  
  public function XtestIdIsReadFromHeader()
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
  
  public function XtestIdIsAutoGenerated()
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
  
  public function XtestDescriptionCanBeSet()
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
  
  public function XtestEncoderIsUsedForStringGeneration()
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
  
  public function XtestMaxLineLengthIsProvidedForEncoding()
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
  
  public function XtestEntityCanBeWrittenToByteStream()
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
  
  public function XtestOrderingOfAlternativePartsCanBeSpecified_1()
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
  
  public function XtestOrderingOfAlternativePartsCanBeSpecified_2()
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
  
  public function XtestFluidInterface()
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
  
  abstract protected function _createBaseEntity($headers, $encoder, $cache);
  
  protected function _createEntity($headerFactory, $encoder, $cache)
  {
    $entity = $this->_createBaseEntity($headerFactory, $encoder, $cache);
    $entity->setHeaders(array());
    return $entity;
  }
  
  protected function _stubHeader()
  {
    return $this->_stub('Swift_Mime_Header');
  }
  
  protected function _createEncoder($stub = true)
  {
    $encoder = $this->_mockery()->mock('Swift_Mime_ContentEncoder');
    $this->_mockery()->checking(Expectations::create()
      -> ignoring($encoder)->getName() -> returns('quoted-printable')
      );
      
    if ($stub)
    {
      $this->_mockery()->checking(Expectations::create()
        -> ignoring($encoder)
        );
    }
    return $encoder;
  }
  
  protected function _createCache($stub = true)
  {
    $cache = $this->_mockery()->mock('Swift_KeyCache');
      
    if ($stub)
    {
      $this->_mockery()->checking(Expectations::create()
        -> ignoring($cache)
        );
    }
    return $cache;
  }
  
  protected function _fillInHeaders($factory)
  {
    $this->_mockery()->checking(Expectations::create()
      -> ignoring($factory) -> returns($this->_stubHeader())
      );
  }
  
}
