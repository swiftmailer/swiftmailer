<?php

require_once 'Swift/Mime/MimeEntity.php';
require_once 'Swift/Mime/MimePart.php';
require_once 'Swift/Mime/AbstractMimeEntityTest.php';
require_once 'Swift/Mime/Header.php';

class Swift_Mime_MimePartTest extends Swift_Mime_AbstractMimeEntityTest
{
  
  public function testNestingLevelIsSubpart()
  {
    $context = new Mockery();
    $part = $this->_createMimePart(
      array(), $this->_getEncoder($context), $this->_getCache($context)
      );
    $this->assertEqual(
      Swift_Mime_MimeEntity::LEVEL_SUBPART, $part->getNestingLevel()
      );
  }
  
  public function testSettingCharsetNotifiesEncoder()
  {
    $context = new Mockery();
    
    $encoder = $this->_getEncoder($context, false);
    $context->checking(Expectations::create()
      -> one($encoder)->charsetChanged('utf-32')
      -> ignoring($encoder)
      );
    $part = $this->_createMimePart(array(), $encoder, $this->_getCache($context));
    $part->setCharset('utf-32');
    
    $context->assertIsSatisfied();
  }
  
  public function testSettingCharsetNotifiesChildren()
  {
    $context = new Mockery();
    
    $child1 = $context->mock('Swift_Mime_MimeEntity');
    $child2 = $context->mock('Swift_Mime_MimeEntity');
    $context->checking(Expectations::create()
      -> one($child1)->charsetChanged('utf-32')
      -> ignoring($child1)
      -> one($child2)->charsetChanged('utf-32')
      -> ignoring($child2)
      );
    $part = $this->_createMimePart(
      array(), $this->_getEncoder($context), $this->_getCache($context)
      );
    $part->setChildren(array($child1, $child2));
    $part->setCharset('utf-32');
    
    $context->assertIsSatisfied();
  }
  
  public function testCharsetChangeUpdatesCharset()
  {
    $context = new Mockery();
    $part = $this->_createMimePart(
      array(), $this->_getEncoder($context), $this->_getCache($context)
      );
    $part->setCharset('utf-32');
    $this->assertEqual('utf-32', $part->getCharset());
    $part->charsetChanged('iso-8859-1');
    $this->assertEqual('iso-8859-1', $part->getCharset());
  }
  
  public function testCharsetIsSetInHeader()
  {
    /* -- RFC 2046, 4.1.2.
    A critical parameter that may be specified in the Content-Type field
    for "text/plain" data is the character set.  This is specified with a
    "charset" parameter, as in:

     Content-type: text/plain; charset=iso-8859-1

    Unlike some other parameter values, the values of the charset
    parameter are NOT case sensitive.  The default character set, which
    must be assumed in the absence of a charset parameter, is US-ASCII.
    */
    
    $context = new Mockery();
    
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->setParameter('charset', 'utf-8')
      -> allowing($h)->getFieldName() -> returns('Content-Type')
      -> ignoring($h)
      );
    
    $headers = array($h);
    
    $part = $this->_createMimePart(
      $headers, $this->_getEncoder($context), $this->_getCache($context)
      );
    $part->setCharset('utf-8');
    
    $context->assertIsSatisfied();
  }
  
  public function testCharsetIsReadFromHeader()
  {
    $context = new Mockery();
    
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->getParameter('charset') -> returns('iso-8859-1')
      -> allowing($h)->getFieldName() -> returns('Content-Type')
      -> ignoring($h)
      );
    
    $headers = array($h);
    
    $part = $this->_createMimePart(
      $headers, $this->_getEncoder($context), $this->_getCache($context)
      );
    $this->assertEqual('iso-8859-1', $part->getCharset());
    
    $context->assertIsSatisfied();
  }
  
  public function testFormatIsSetInHeader()
  {
    /* -- RFC 3676.
     */
    
    $context = new Mockery();
    
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->setParameter('format', 'fixed')
      -> allowing($h)->getFieldName() -> returns('Content-Type')
      -> ignoring($h)
      );
    
    $headers = array($h);
    
    $part = $this->_createMimePart(
      $headers, $this->_getEncoder($context), $this->_getCache($context)
      );
    $part->setFormat('fixed');
    
    $context->assertIsSatisfied();
  }
  
  public function testFormatIsReadFromHeader()
  {
    $context = new Mockery();
    
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->getParameter('format') -> returns('flowed')
      -> allowing($h)->getFieldName() -> returns('Content-Type')
      -> ignoring($h)
      );
    
    $headers = array($h);
    
    $part = $this->_createMimePart(
      $headers, $this->_getEncoder($context), $this->_getCache($context)
      );
    $this->assertEqual('flowed', $part->getFormat());
    
    $context->assertIsSatisfied();
  }
  
  public function testDelSpIsSetInHeader()
  {
    /* -- RFC 3676.
     */
     
    $context = new Mockery();
    
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->setParameter('delsp', 'yes')
      -> allowing($h)->getFieldName() -> returns('Content-Type')
      -> ignoring($h)
      );
    
    $headers = array($h);
    
    $part = $this->_createMimePart(
      $headers, $this->_getEncoder($context), $this->_getCache($context)
      );
    $part->setDelSp(true);
    
    $context->assertIsSatisfied();
  }
  
  public function testDelSpIsReadFromHeader()
  {
    $context = new Mockery();
    
    $h = $context->mock('Swift_Mime_ParameterizedHeader');
    $context->checking(Expectations::create()
      -> atLeast(1)->of($h)->getParameter('delsp') -> returns('yes')
      -> allowing($h)->getFieldName() -> returns('Content-Type')
      -> ignoring($h)
      );
    
    $headers = array($h);
    
    $part = $this->_createMimePart(
      $headers, $this->_getEncoder($context), $this->_getCache($context)
      );
    $this->assertTrue($part->getDelSp());
    
    $context->assertIsSatisfied();
  }
  
  public function testFluidInterface()
  {
    $context = new Mockery();
    $child = $context->mock('Swift_Mime_MimeEntity');
    $encoder = $this->_getEncoder($context);
    $context->checking(Expectations::create()
      -> allowing($child)->getNestingLevel() -> returns(Swift_Mime_MimeEntity::LEVEL_SUBPART)
      -> ignoring($child)
      );
    $part = $this->_createMimePart(
      array(), $encoder, $this->_getCache($context)
      );
    $ref = $part
      ->setContentType('text/plain')
      ->setEncoder($encoder)
      ->setId('foo@bar')
      ->setDescription('my description')
      ->setMaxLineLength(998)
      ->setBodyAsString('xx')
      ->setNestingLevel(10)
      ->setBoundary('xyz')
      ->setChildren(array($child))
      ->setHeaders(array())
      ->setCharset('iso-8859-1')
      ->setFormat('flowed')
      ->setDelSp(false)
      ;
    
    $this->assertReference($part, $ref);
    
    $context->assertIsSatisfied();
  }
  
  // -- Private helpers
  
  //abstract
  protected function _createEntity($headers, $encoder, $cache)
  {
    return $this->_createMimePart($headers, $encoder, $cache);
  }
  
  protected function _createMimePart($headers, $encoder, $cache)
  {
    return new Swift_Mime_MimePart($headers, $encoder, $cache);
  }
  
}
