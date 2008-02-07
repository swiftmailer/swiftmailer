<?php

require_once 'Swift/Mime/Header/IdentificationHeader.php';

class Swift_Mime_Header_IdentificationHeaderTest extends UnitTestCase
{
  
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
    
    $header = $this->_getHeader('Message-ID');
    $header->setId('id-left@id-right');
    $this->assertEqual('<id-left@id-right>', $header->getFieldBody());
  }
  
  public function testIdCanBeRetreivedVerbatim()
  {
    $header = $this->_getHeader('Message-ID');
    $header->setId('id-left@id-right');
    $this->assertEqual('id-left@id-right', $header->getId());
  }
  
  public function testMultipleIdsCanBeSet()
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
    
    $header = $this->_getHeader('References');
    $header->setIds(array('a@b', 'x@y'));
    $this->assertEqual('<a@b> <x@y>', $header->getFieldBody());
  }
  
  public function testIdLeftCanBeQuoted()
  {
    /* -- RFC 2822, 3.6.4.
     id-left         =       dot-atom-text / no-fold-quote / obs-id-left
     */
    
    $header = $this->_getHeader('References');
    $header->setId('"ab"@c');
    $this->assertEqual('"ab"@c', $header->getId());
    $this->assertEqual('<"ab"@c>', $header->getFieldBody());
  }
  
  public function testIdLeftCanContainAnglesAsQuotedPairs()
  {
    /* -- RFC 2822, 3.6.4.
     no-fold-quote   =       DQUOTE *(qtext / quoted-pair) DQUOTE
     */
    
    $header = $this->_getHeader('References');
    $header->setId('"a\\<\\>b"@c');
    $this->assertEqual('"a\\<\\>b"@c', $header->getId());
    $this->assertEqual('<"a\\<\\>b"@c>', $header->getFieldBody());
  }
  
  public function testIdLeftCanBeDotAtom()
  {
    $header = $this->_getHeader('References');
    $header->setId('a.b+&%$.c@d');
    $this->assertEqual('a.b+&%$.c@d', $header->getId());
    $this->assertEqual('<a.b+&%$.c@d>', $header->getFieldBody());
  }
  
  public function testInvalidIdLeftThrowsException()
  {
    try
    {
      $header = $this->_getHeader('References');
      $header->setId('a b c@d');
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
    
    $header = $this->_getHeader('References');
    $header->setId('a@b.c+&%$.d');
    $this->assertEqual('a@b.c+&%$.d', $header->getId());
    $this->assertEqual('<a@b.c+&%$.d>', $header->getFieldBody());
  }
  
  public function testIdRightCanBeLiteral()
  {
    /* -- RFC 2822, 3.6.4.
     no-fold-literal =       "[" *(dtext / quoted-pair) "]"
     */
    
    $header = $this->_getHeader('References');
    $header->setId('a@[1.2.3.4]');
    $this->assertEqual('a@[1.2.3.4]', $header->getId());
    $this->assertEqual('<a@[1.2.3.4]>', $header->getFieldBody());
  }
  
  public function testInvalidIdRightThrowsException()
  {
    try
    {
      $header = $this->_getHeader('References');
      $header->setId('a@b c d');
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
      $header = $this->_getHeader('References');
      $header->setId('abc');
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
    $header = $this->_getHeader('References');
    $header->setIds(array('a@b', 'x@y'));
    $this->assertEqual('References: <a@b> <x@y>' . "\r\n", $header->toString());
  }
  
  // --- THESE TESTS ARE IMPLEMENTATION SPECIFIC FOR COMPATIBILITY WITH --
  // --- SimpleMimeEntity ---
  
  public function testObserverInterfaceUpdatesContentId()
  {
    $header = $this->_getHeader('Content-ID');
    $header->fieldChanged('id', 'fooxyz@bar.abc');
    $this->assertEqual('fooxyz@bar.abc', $header->getId());
  }
  
  public function testContentIdIsNotChangedForOtherFields()
  {
    $header = $this->_getHeader('Content-ID');
    $header->fieldChanged('to', 'fooxyz@bar.abc');
    $this->assertNotEqual('fooxyz@bar.abc', $header->getId());
  }
  
  public function testIdIsIgnoredForOtherHeaders()
  {
    foreach (array('References', 'In-Reply-To') as $name)
    {
      $header = $this->_getHeader($name);
      $header->fieldChanged('id', 'foo@bar');
      $this->assertNotEqual('foo@bar', $header->getId());
    }
  }
  
  // -- Private methods
  
  private function _getHeader($name)
  {
    return new Swift_Mime_Header_IdentificationHeader($name);
  }
  
}
