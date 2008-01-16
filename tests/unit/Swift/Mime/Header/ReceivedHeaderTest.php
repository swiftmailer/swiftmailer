<?php

require_once 'Swift/Mime/Header/ReceivedHeader.php';

Mock::generate('Swift_Mime_HeaderEncoder', 'Swift_Mime_MockHeaderEncoder');

class Swift_Mime_Header_ReceivedHeaderTest extends UnitTestCase
{
  
  private $_charset = 'utf-8';
  
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
  
  // -- Private methods
  
  private function _getHeader($name, $timestamp = null, $info = array(), $encoder = null)
  {
    return new Swift_Mime_Header_ReceivedHeader(
      $name, $timestamp, $info, $this->_charset, $encoder
      );
  }
  
}
