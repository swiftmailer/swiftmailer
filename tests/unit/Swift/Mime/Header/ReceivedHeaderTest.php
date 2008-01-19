<?php

require_once 'Swift/Mime/Header/ReceivedHeader.php';

Mock::generate('Swift_Mime_HeaderEncoder', 'Swift_Mime_MockHeaderEncoder');

class Swift_Mime_Header_ReceivedHeaderTest extends UnitTestCase
{
  
  public function testTimestampCanBeSetAndFetched()
  {
    $timestamp = time();
    $header = $this->_getHeader('Received', $timestamp);
    $this->assertEqual($timestamp, $header->getTimestamp());
  }
  
  public function testTimestampCanBeSetBySetter()
  {
    $timestamp = time();
    $header = $this->_getHeader('Received');
    $header->setTimestamp($timestamp);
    $this->assertEqual($timestamp, $header->getTimestamp());
  }
  
  public function testDateIsRenderedAccordingToRfc2822()
  {
    /* -- RFC 2822, 3.6.7.
       The "Received:" field contains a
       (possibly empty) list of name/value pairs followed by a semicolon and
       a date-time specification.  The first item of the name/value pair is
       defined by item-name, and the second item is either an addr-spec, an
       atom, a domain, or a msg-id.  Further restrictions may be applied to
       the syntax of the trace fields by standards that provide for their
       use, such as [RFC2821].
       */
    
    $timestamp = time();
    $header = $this->_getHeader('Received', $timestamp);
    $this->assertEqual('; ' . date('r', $timestamp), $header->getValue());
  }
  
  public function testNameValuePairsCanBeSet()
  {
    /* -- RFC 2822, 3.6.7.
      name-val-list   =       [CFWS] [name-val-pair *(CFWS name-val-pair)]

      name-val-pair   =       item-name CFWS item-value

      item-name       =       ALPHA *(["-"] (ALPHA / DIGIT))

      item-value      =       1*angle-addr / addr-spec /
                               atom / domain / msg-id
    */
    
    $timestamp = time();
    $header = $this->_getHeader('Received', $timestamp,
      array(
        array('name' => 'from', 'value' => 'mx1.googlemail.com')
        )
      );
    $this->assertEqual('from mx1.googlemail.com; ' . date('r', $timestamp),
      $header->getValue()
      );
  }
  
  public function testUsingMultipleNameValuePairs()
  {
    $timestamp = time();
    $header = $this->_getHeader('Received', $timestamp,
      array(
        array('name' => 'from', 'value' => 'mx1.googlemail.com'),
        array('name' => 'by', 'value' => 'mail.swiftmailer.org')
        )
      );
    $this->assertEqual(
      'from mx1.googlemail.com by mail.swiftmailer.org; ' . date('r', $timestamp),
      $header->getValue()
      );
  }
  
  public function testLongSetsOfNameValuePairsCanBeSplitIntoLines()
  {
    $timestamp = time();
    $header = $this->_getHeader('Received', $timestamp,
      array(
        array('name' => 'from', 'value' => 'mx1.googlemail.com'),
        array('name' => 'by', 'value' => 'mail.swiftmailer.org'),
        array('name' => 'with', 'value' => 'ESMTP'),
        array('name' => 'for', 'value' => '<chris@swiftmailer.org>')
        )
      );
    
    $header->setPairsPerLine(2);
    
    $this->assertEqual(
      'from mx1.googlemail.com by mail.swiftmailer.org' . "\r\n " .
      'with ESMTP for <chris@swiftmailer.org>; ' . date('r', $timestamp),
      $header->getValue()
      );
  }
  
  public function testCommentsCanBeGivenAfterNameValuePairs()
  {
    $timestamp = time();
    $header = $this->_getHeader('Received', $timestamp,
      array(
        array(
          'name' => 'from',
          'value' => 'mx1.googlemail.com',
          'comment' => 'A Gmail server'
          ),
        array(
          'name' => 'by',
          'value' => 'mail.swiftmailer.org'
          )
        )
      );
    $this->assertEqual(
      'from mx1.googlemail.com (A Gmail server) by mail.swiftmailer.org; ' .
      date('r', $timestamp),
      $header->getValue()
      );
  }
  
  public function testInvalidValuesArePermitted()
  {
    /* -- RFC 2821, 3.8.2.
    When forwarding a message into or out of the Internet environment, a
    gateway MUST prepend a Received: line, but it MUST NOT alter in any
    way a Received: line that is already in the header.

    "Received:" fields of messages originating from other environments
    may not conform exactly to this specification.  However, the most
    important use of Received: lines is for debugging mail faults, and
    this debugging can be severely hampered by well-meaning gateways that
    try to "fix" a Received: line.  As another consequence of trace
    fields arising in non-SMTP environments, receiving systems MUST NOT
    reject mail based on the format of a trace field and SHOULD be
    extremely robust in the light of unexpected information or formats in
    those fields.
    */
    
    //This particular header goes against the grain of the other Headers
    // and requires the caller to understand the importance of using only
    // printable US-ASCII within the values.  Special characters such as
    // <>,.[]()\" should be avoided at all costs, but RFC 2821 informs no
    // validation/alteration should occur
    
    $timestamp = time();
    $header = $this->_getHeader('Received', $timestamp,
      array(
        array(
          'name' => 'from',
          'value' => 'mx1.googlemail.com',
          'comment' => 'A Gmail )server'
          ),
        array(
          'name' => 'by',
          'value' => 'mail.swiftm\\ailer.org'
          )
        )
      );
    $this->assertEqual(
      'from mx1.googlemail.com (A Gmail )server) by mail.swiftm\\ailer.org; ' .
      date('r', $timestamp),
      $header->getValue()
      );
  }
  
  public function testSetValueParsesTimestamp()
  {
    $header = $this->_getHeader('Received');
    $header->setValue('; Mon, 14 Jan 2008 22:30:08 +1100');
    $this->assertEqual('; Mon, 14 Jan 2008 22:30:08 +1100', $header->getValue());
    $this->assertEqual(1200310208, $header->getTimestamp());
  }
  
  public function testSetValueReturnsNullTimestampIfInvalid()
  {
    $header = $this->_getHeader('Received');
    //Missing comma
    $header->setValue('; Mon 14 Jan 2008 22:30:08 +1100');
    $this->assertEqual('; Mon 14 Jan 2008 22:30:08 +1100', $header->getValue());
    $this->assertNull($header->getTimestamp());
  }
  
  public function testSetValueParsesOutNameValuePairs()
  {
    $header = $this->_getHeader('Received');
    $header->setValue('from mx1.googlemail.com by mail.swiftmailer.org' . "\r\n " .
      'with ESMTP for chris@swiftmailer.org; Mon, 14 Jan 2008 22:30:08 +1100'
      );
    $this->assertEqual('from mx1.googlemail.com by mail.swiftmailer.org' . "\r\n " .
      'with ESMTP for chris@swiftmailer.org; Mon, 14 Jan 2008 22:30:08 +1100',
      $header->getValue()
      );
    $this->assertEqual(1200310208, $header->getTimestamp());
    $this->assertEqual(
      array(
        array('name' => 'from', 'value' => 'mx1.googlemail.com', 'comment' => null),
        array('name' => 'by', 'value' => 'mail.swiftmailer.org', 'comment' => null),
        array('name' => 'with', 'value' => 'ESMTP', 'comment' => null),
        array('name' => 'for', 'value' => 'chris@swiftmailer.org', 'comment' => null)
        ),
      $header->getData()
      );
  }
  
  public function testCommentsCanBeParsedOut()
  {
    $header = $this->_getHeader('Received');
    $header->setValue(
      'from mx1.googlemail.com (google server) by mail.swiftmailer.org' . "\r\n " .
      'with ESMTP (Using Exim 4.17) for (Chris) chris@swiftmailer.org;' . "\r\n " .
      'Mon, 14 Jan 2008 22:30:08 +1100'
      );
    $this->assertEqual(
      'from mx1.googlemail.com (google server) by mail.swiftmailer.org' . "\r\n " .
      'with ESMTP (Using Exim 4.17) for (Chris) chris@swiftmailer.org;' . "\r\n " .
      'Mon, 14 Jan 2008 22:30:08 +1100',
      $header->getValue()
      );
    $this->assertEqual(1200310208, $header->getTimestamp());
    
    //We're only bothering about the comments following a full name-val-pair
    $this->assertEqual(
      array(
        array('name' => 'from', 'value' => 'mx1.googlemail.com', 'comment' => 'google server'),
        array('name' => 'by', 'value' => 'mail.swiftmailer.org', 'comment' => null),
        array('name' => 'with', 'value' => 'ESMTP', 'comment' => 'Using Exim 4.17'),
        array('name' => 'for', 'value' => 'chris@swiftmailer.org', 'comment' => null)
        ),
      $header->getData()
      );
  }
  
  public function testToString()
  {
    $timestamp = time();
    $header = $this->_getHeader('Received', $timestamp,
      array(
        array(
          'name' => 'from',
          'value' => 'mx1.googlemail.com',
          'comment' => 'A Gmail server'
          ),
        array(
          'name' => 'by',
          'value' => 'mail.swiftmailer.org'
          )
        )
      );
    $this->assertEqual(
      'Received: from mx1.googlemail.com (A Gmail server) by mail.swiftmailer.org; ' .
      date('r', $timestamp) . "\r\n",
      $header->toString()
      );
  }
  
  // -- Private methods
  
  private function _getHeader($name, $timestamp = null, $info = array())
  {
    return new Swift_Mime_Header_ReceivedHeader($name, $timestamp, $info);
  }
  
}
