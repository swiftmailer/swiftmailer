<?php

require_once 'Swift/Mime/Header/IdentificationHeader.php';
require_once 'Swift/Mime/HeaderAttribute.php';
require_once 'Swift/Mime/HeaderAttributeSet.php';
require_once 'Swift/Mime/HeaderEncoder.php';

Mock::generate('Swift_Mime_HeaderAttribute', 'Swift_Mime_MockHeaderAttribute');
Mock::generate('Swift_Mime_HeaderAttributeSet',
  'Swift_Mime_MockHeaderAttributeSet'
  );
Mock::generate('Swift_Mime_HeaderEncoder', 'Swift_Mime_MockHeaderEncoder');

class Swift_Mime_Header_IdentificationHeaderTest extends UnitTestCase
{
  
  private $_charset = 'utf-8';
  
  public function testValueMatchesMsgIdSpec()
  {
    /* -- RFC 2822, 3.6.4.
     message-id      =       "Message-ID:" msg-id CRLF

     in-reply-to     =       "In-Reply-To:" 1*msg-id CRLF

     references      =       "References:" 1*msg-id CRLF

     msg-id          =       [CFWS] "<" id-left "@" id-right ">" [CFWS]

     id-left         =       dot-atom-text / no-fold-quote / obs-id-left

     id-right        =       dot-atom-text / no-fold-literal / obs-id-right

     no-fold-quote   =       DQUOTE *(qtext / quoted-pair) DQUOTE

     no-fold-literal =       "[" *(dtext / quoted-pair) "]"
     */
    
    $header = $this->_getHeader('Message-ID', 'id-left@id-right');
    $this->assertEqual('<id-left@id-right>', $header->getValue());
  }
  
  public function testIdCanBeRetreivedVerbatim()
  {
    $header = $this->_getHeader('Message-ID', 'id-left@id-right');
    $this->assertEqual('id-left@id-right', $header->getId());
  }
  
  public function testIdCanBeSetViaSetter()
  {
    $header = $this->_getHeader('Message-ID');
    $header->setId('xyz@abc');
    $this->assertEqual('xyz@abc', $header->getId());
    $this->assertEqual('<xyz@abc>', $header->getValue());
  }
  
  public function testMultipleIdsCanBeSet()
  {
    $header = $this->_getHeader('References', array('a@b', 'x@y'));
    $this->assertEqual(array('a@b', 'x@y'), $header->getIds());
  }
  
  public function testMultipleIdsCanBeSetViaSetter()
  {
    $header = $this->_getHeader('References');
    $header->setIds(array('a@b', 'x@y'));
    $this->assertEqual(array('a@b', 'x@y'), $header->getIds());
  }
  
  public function testSettingMultipleIdsProducesAListValue()
  {
    /* -- RFC 2822, 3.6.4.
     The "References:" and "In-Reply-To:" field each contain one or more
     unique message identifiers, optionally separated by CFWS.
     
     .. SNIP ..
     
     in-reply-to     =       "In-Reply-To:" 1*msg-id CRLF

     references      =       "References:" 1*msg-id CRLF
     */
    
    $header = $this->_getHeader('References', array('a@b', 'x@y'));
    $this->assertEqual('<a@b> <x@y>', $header->getValue());
  }
  
  public function testValueSetterCanBeUsedDirectly()
  {
    $header = $this->_getHeader('References');
    $header->setValue('<a@b>');
    $this->assertEqual('<a@b>', $header->getValue());
  }
  
  public function testIdIsResolvedFromSettingValue()
  {
    $header = $this->_getHeader('References');
    $header->setValue('<a@b>');
    $this->assertEqual('a@b', $header->getId());
  }
  
  public function testSettingMultipleIdsByDirectValue()
  {
    $header = $this->_getHeader('In-Reply-To');
    $header->setValue('<a@b> <c@d> <x@y>');
    $this->assertEqual('<a@b> <c@d> <x@y>', $header->getValue());
    $this->assertEqual(array('a@b', 'c@d', 'x@y'), $header->getIds());
  }
  
  public function testGetIdReturnsFirstAvailableId()
  {
    $header = $this->_getHeader('References', array('a@b', 'x@y'));
    $this->assertEqual('a@b', $header->getId());
  }
  
  public function testFwsIsIgnoredInValueParsing()
  {
    $header = $this->_getHeader('References');
    $header->setValue('<a@b> <c@d>' . "\r\n " . '<x@y>');
    $this->assertEqual('<a@b> <c@d>' . "\r\n " . '<x@y>', $header->getValue());
    $this->assertEqual(array('a@b', 'c@d', 'x@y'), $header->getIds());
  }
  
  public function testCommentsAreIngoredInValueParsing()
  {
    $header = $this->_getHeader('References');
    $header->setValue('<a@b> (comment \\) here % stuff) <c@d>(test)<x@y> (comment)');
    $this->assertEqual(
      '<a@b> (comment \\) here % stuff) <c@d>(test)<x@y> (comment)',
      $header->getValue()
      );
    $this->assertEqual(array('a@b', 'c@d', 'x@y'), $header->getIds());
  }
  
  public function testIdLeftCanBeQuoted()
  {
    /* -- RFC 2822, 3.6.4.
     id-left         =       dot-atom-text / no-fold-quote / obs-id-left
     */
    
    $header = $this->_getHeader('References', '"ab"@c');
    $this->assertEqual('"ab"@c', $header->getId());
    $this->assertEqual('<"ab"@c>', $header->getValue());
  }
  
  public function testIdLeftCanContainAnglesAsQuotedPairs()
  {
    /* -- RFC 2822, 3.6.4.
     no-fold-quote   =       DQUOTE *(qtext / quoted-pair) DQUOTE
     */
    
    $header = $this->_getHeader('References', '"a\\<\\>b"@c');
    $this->assertEqual('"a\\<\\>b"@c', $header->getId());
    $this->assertEqual('<"a\\<\\>b"@c>', $header->getValue());
  }
  
  public function testIdLeftCanBeDotAtom()
  {
    $header = $this->_getHeader('References', 'a.b+&%$.c@d');
    $this->assertEqual('a.b+&%$.c@d', $header->getId());
    $this->assertEqual('<a.b+&%$.c@d>', $header->getValue());
  }
  
  public function testInvalidIdLeftThrowsException()
  {
    try
    {
      $header = $this->_getHeader('References', 'a b c@d');
      $this->fail(
        'Exception should be thrown since "a b c" is not valid id-left.'
        );
    }
    catch (Exception $e)
    {
      $this->pass();
    }
  }
  
  public function testIdRightCanBeDotAtom()
  {
    /* -- RFC 2822, 3.6.4.
     id-right        =       dot-atom-text / no-fold-literal / obs-id-right
     */
    
    $header = $this->_getHeader('References', 'a@b.c+&%$.d');
    $this->assertEqual('a@b.c+&%$.d', $header->getId());
    $this->assertEqual('<a@b.c+&%$.d>', $header->getValue());
  }
  
  public function testIdRightCanBeLiteral()
  {
    /* -- RFC 2822, 3.6.4.
     no-fold-literal =       "[" *(dtext / quoted-pair) "]"
     */
    
    $header = $this->_getHeader('References', 'a@[1.2.3.4]');
    $this->assertEqual('a@[1.2.3.4]', $header->getId());
    $this->assertEqual('<a@[1.2.3.4]>', $header->getValue());
  }
  
  public function testInvalidIdRightThrowsException()
  {
    try
    {
      $header = $this->_getHeader('References', 'a@b c d');
      $this->fail(
        'Exception should be thrown since "b c d" is not valid id-right.'
        );
    }
    catch (Exception $e)
    {
      $this->pass();
    }
  }
  
  public function testMissingAtSignThrowsException()
  {
    /* -- RFC 2822, 3.6.4.
     msg-id          =       [CFWS] "<" id-left "@" id-right ">" [CFWS]
     */
    
    try
    {
      $header = $this->_getHeader('References', 'abc');
      $this->fail(
        'Exception should be thrown since "abc" is does not contain @.'
        );
    }
    catch (Exception $e)
    {
      $this->pass();
    }
  }
  
  public function testStringValue()
  {
    $header = $this->_getHeader('References', array('a@b', 'x@y'));
    $this->assertEqual('References: <a@b> <x@y>' . "\r\n", $header->toString());
  }
  
  public function testStringValueWithCfws()
  {
    $header = $this->_getHeader('References');
    $header->setValue('<a@b>' . "\r\n " . '(comment here) <x@y>');
    $this->assertEqual(
      'References: <a@b>' . "\r\n " . '(comment here) <x@y>' . "\r\n",
      $header->toString()
      );
  }
  
  // -- Private methods
  
  private function _getHeader($name, $value = null, $encoder = null)
  {
    if (!$encoder)
    {
      $encoder = new Swift_Mime_MockHeaderEncoder();
    }
    return new Swift_Mime_Header_IdentificationHeader(
      $name, $value, $this->_charset, $encoder
      );
  }
  
}
