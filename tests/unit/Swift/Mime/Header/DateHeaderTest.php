<?php

require_once 'Swift/AbstractSwiftUnitTestCase.php';
require_once 'Swift/Mime/Header/DateHeader.php';

class Swift_Mime_Header_DateHeaderTest
  extends Swift_AbstractSwiftUnitTestCase
{
  
  private $_charset = 'utf-8';
  
  /* --
  The following tests refer to RFC 2822, section 3.6.1 and 3.3.
  */
  
  public function testGetTimestamp()
  {
    $timestamp = time();
    $header = $this->_getHeader('Date', $timestamp);
    $this->assertIdentical($timestamp, $header->getTimestamp());
  }
  
  public function testTimestampCanBeSetBySetter()
  {
    $timestamp = time();
    $header = $this->_getHeader('Date');
    $header->setTimestamp($timestamp);
    $this->assertIdentical($timestamp, $header->getTimestamp());
  }
  
  public function testIntegerTimestampIsConvertedToRfc2822Date()
  {
    $timestamp = time();
    $header = $this->_getHeader('Date', $timestamp);
    $this->assertEqual(date('r', $timestamp), $header->getValue());
  }
  
  public function testSettingValidRfc2822DateValue()
  {
    $header = $this->_getHeader('Date');
    $header->setValue('Mon, 14 Jan 2008 22:30:08 +1100');
    $this->assertEqual('Mon, 14 Jan 2008 22:30:08 +1100', $header->getValue());
    $this->assertEqual(1200310208, $header->getTimestamp());
  }
  
  public function testSettingValidShortFormRfc2822DateValue()
  {
    $header = $this->_getHeader('Date');
    $header->setValue('14 Jan 2008 22:30:08 +1100');
    $this->assertEqual('14 Jan 2008 22:30:08 +1100', $header->getValue());
    $this->assertEqual(1200310208, $header->getTimestamp());
  }
  
  public function testCommentCanAppearAtEndOfValue()
  {
    $header = $this->_getHeader('Date');
    $header->setValue('Mon, 14 Jan 2008 22:30:08 +1100 (some comment)');
    $this->assertEqual('Mon, 14 Jan 2008 22:30:08 +1100 (some comment)',
      $header->getValue()
      );
    $this->assertEqual(1200310208, $header->getTimestamp());
  }
  
  public function testFWSCanAppearBetweenDateAndTime()
  {
    $header = $this->_getHeader('Date');
    $header->setValue('Mon, 14 Jan 2008' . "\r\n " . '22:30:08 +1100');
    $this->assertEqual('Mon, 14 Jan 2008' . "\r\n " . '22:30:08 +1100',
      $header->getValue()
      );
    $this->assertEqual(1200310208, $header->getTimestamp());
  }
  
  public function testFWSCanAppearBetweenTimeAndZone()
  {
    $header = $this->_getHeader('Date');
    $header->setValue('Mon, 14 Jan 2008 22:30:08' . "\r\n " . '+1100');
    $this->assertEqual('Mon, 14 Jan 2008 22:30:08' . "\r\n " . '+1100',
      $header->getValue()
      );
    $this->assertEqual(1200310208, $header->getTimestamp());
  }
  
  public function testFWSCanAppearBetweenMonthAndYear()
  {
    $header = $this->_getHeader('Date');
    $header->setValue('Mon, 14 Jan' . "\r\n " . '2008 22:30:08 +1100');
    $this->assertEqual('Mon, 14 Jan' . "\r\n " . '2008 22:30:08 +1100',
      $header->getValue()
      );
    $this->assertEqual(1200310208, $header->getTimestamp());
  }
  
  public function testFWSCanBeHTAB()
  {
    $header = $this->_getHeader('Date');
    $header->setValue('Mon, 14 Jan' . "\t" . '2008 22:30:08 +1100');
    $this->assertEqual('Mon, 14 Jan' . "\t" . '2008 22:30:08 +1100',
      $header->getValue()
      );
    $this->assertEqual(1200310208, $header->getTimestamp());
  }
  
  public function testSettingInvalidDateThrowsException()
  {
    /* -- RFC 2822, 3.3.
    date-time       =       [ day-of-week "," ] date FWS time [CFWS]

    day-of-week     =       ([FWS] day-name) / obs-day-of-week

    day-name        =       "Mon" / "Tue" / "Wed" / "Thu" /
                            "Fri" / "Sat" / "Sun"

    date            =       day month year

    year            =       4*DIGIT / obs-year

    month           =       (FWS month-name FWS) / obs-month

    month-name      =       "Jan" / "Feb" / "Mar" / "Apr" /
                            "May" / "Jun" / "Jul" / "Aug" /
                            "Sep" / "Oct" / "Nov" / "Dec"

    day             =       ([FWS] 1*2DIGIT) / obs-day

    time            =       time-of-day FWS zone

    time-of-day     =       hour ":" minute [ ":" second ]

    hour            =       2DIGIT / obs-hour

    minute          =       2DIGIT / obs-minute

    second          =       2DIGIT / obs-second

    zone            =       (( "+" / "-" ) 4DIGIT) / obs-zone
    */
    
    $header = $this->_getHeader('Date');
    try
    {
      $header->setValue('Monday, 14 Jan 2008 22:30:08 +1100');
      $this->fail('day-of-week can only be Mon/Tue/Wed/Thu/Fri/Sat/Sun.');
    }
    catch (Exception $e)
    {
      $this->pass();
    }
    
    try
    {
      $header->setValue('Mon, 14 January 2008 22:30:08 +1100');
      $this->fail('month-name can only be Jan/Feb/Mar/Apr/May/Jun/Jul/Aug/Sep/Oct/Nov/Dec.');
    }
    catch (Exception $e)
    {
      $this->pass();
    }
    
    try
    {
      $header->setValue('Mon, 14 Jan 08 22:30:08 +1100');
      $this->fail('Year must be 4 digits');
    }
    catch (Exception $e)
    {
      $this->pass();
    }
    
    try
    {
      $header->setValue('Mon, 14 Jan 2008 2:30:08 +1100');
      $this->fail('hour must be 2 digits.');
    }
    catch (Exception $e)
    {
      $this->pass();
    }
    
    try
    {
      $header->setValue('Mon, 14 Jan 2008 22:30:08');
      $this->fail('time zone must be present.');
    }
    catch (Exception $e)
    {
      $this->pass();
    }
    
    try
    {
      $header->setValue('(Opening comment) Mon, 14 Jan 2008 22:30:08 +1100');
      $this->fail('CFWS can only appear at end of date.');
    }
    catch (Exception $e)
    {
      $this->pass();
    }
  }
  
  public function testToString()
  {
    $timestamp = time();
    $header = $this->_getHeader('Date', $timestamp);
    $this->assertEqual('Date: ' . date('r', $timestamp) . "\r\n",
      $header->toString()
      );
  }
  
  // -- Private methods
  
  private function _getHeader($name, $value = null, $encoder = null)
  {
    return new Swift_Mime_Header_DateHeader(
      $name, $value, $this->_charset, $encoder
      );
  }
  
}
