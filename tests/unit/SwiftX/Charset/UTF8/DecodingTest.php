<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'SwiftX/Charset/UTF8.php';

class SwiftX_Charset_UTF8_DecodingTest extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_charset;
  
  public function setUp()
  {
    $this->_charset = $this->_createCharset();
  }
  
  /* -- RFC 2279
  
  0000 0000-0000 007F   0xxxxxxx
  0000 0080-0000 07FF   110xxxxx 10xxxxxx
  0000 0800-0000 FFFF   1110xxxx 10xxxxxx 10xxxxxx

  0001 0000-001F FFFF   11110xxx 10xxxxxx 10xxxxxx 10xxxxxx
  0020 0000-03FF FFFF   111110xx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx
  0400 0000-7FFF FFFF   1111110x 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx
  
  */
  
  public function testSingleOctetsWithValuesFrom00to7FDecodeToSingleAsciiChars()
  {
    for ($i = 0; $i < 100; ++$i)
    {
      $bytes = array(rand(0x00, 0x7F));
      
      $chars = array();
      $this->_charset->decode($bytes, $chars);
      
      $this->assertIdentical(1, count($chars),
        '%s: 1 unicode character should be found'
      );
      $this->assertTrue(
        ($chars[0] <= 0x007F),
        '%s: Decoded character should be in the 0x007F range'
      );
    }
  }
  
  public function testStreamOf10AsciiOctetsDecodeToTenUnicodeChars()
  {
    for ($i = 0; $i < 10; ++$i)
    {
      $bytes = array();
      for ($j = 0; $j < 10; ++$j)
      {
        $bytes[] = rand(0x00, 0x7F);
      }
      
      $chars = array();
      $this->_charset->decode($bytes, $chars);
      
      $this->assertIdentical(10, count($chars),
        '%s: 10 unicode characters should be found'
      );
    }
  }
  
  public function testTwinOctetsStartingBetweenC0andDFDecodeToCharsBetween00000080and000007ff()
  {
    for ($i = 0; $i < 100; ++$i)
    {
      $bytes = array(
        rand(0xC0, 0xDF), rand(0x80, 0xBF)
      );
      
      $chars = array();
      $this->_charset->decode($bytes, $chars);
      
      $this->assertIdentical(1, count($chars),
        '%s: 1 unicode character should be found'
      );
      $this->assertTrue(
        ($chars[0] <= 0x07FF),
        '%s: Decoded character should be in the 0x07FF range'
      );
    }
  }
  
  public function testStreamOf10PairsOfOctetsOctetsDecodeToTenUnicodeChars()
  {
    for ($i = 0; $i < 10; ++$i)
    {
      $bytes = array();
      for ($j = 0; $j < 10; ++$j)
      {
        $bytes[] = rand(0xC0, 0xDF);
        $bytes[] = rand(0x80, 0xBF);
      }
      
      $chars = array();
      $this->_charset->decode($bytes, $chars);
      
      $this->assertIdentical(10, count($chars),
        '%s: 10 unicode characters should be found'
      );
    }
  }
  
  public function testTripleOctetsStartingBetweenE0andEFDecodeToCharsBetween00000800and0000FFFF()
  {
    for ($i = 0; $i < 100; ++$i)
    {
      $bytes = array(
        rand(0xE0, 0xEF), rand(0x80, 0xBF), rand(0x80, 0xBF)
      );
      
      $chars = array();
      $this->_charset->decode($bytes, $chars);
      
      $this->assertIdentical(1, count($chars),
        '%s: 1 unicode character should be found'
      );
      $this->assertTrue(
        ($chars[0] <= 0xFFFF),
        '%s: Decoded character should be in the 0xFFFF range'
      );
    }
  }
  
  public function testStreamOf10GroupsOfThreeOctetsOctetsDecodeToTenUnicodeChars()
  {
    for ($i = 0; $i < 10; ++$i)
    {
      $bytes = array();
      for ($j = 0; $j < 10; ++$j)
      {
        $bytes[] = rand(0xE0, 0xEF);
        $bytes[] = rand(0x80, 0xBF);
        $bytes[] = rand(0x80, 0xBF);
      }
      
      $chars = array();
      $this->_charset->decode($bytes, $chars);
      
      $this->assertIdentical(10, count($chars),
        '%s: 10 unicode characters should be found'
      );
    }
  }
  
  public function testFourOctetSequencesStartingBetweenF0andF7DecodeToCharsBetween00010000and001FFFFF()
  {
    for ($i = 0; $i < 100; ++$i)
    {
      $bytes = array(
        rand(0xF0, 0xF7), rand(0x80, 0xBF), rand(0x80, 0xBF), rand(0x80, 0xBF)
      );
      
      $chars = array();
      $this->_charset->decode($bytes, $chars);
      
      $this->assertIdentical(1, count($chars),
        '%s: 1 unicode character should be found'
      );
      $this->assertTrue(
        ($chars[0] <= 0x001FFFFF),
        '%s: Decoded character should be in the 0x001FFFFF range'
      );
    }
  }
  
  public function testStreamOf10GroupsOfFourOctetsOctetsDecodeToTenUnicodeChars()
  {
    for ($i = 0; $i < 10; ++$i)
    {
      $bytes = array();
      for ($j = 0; $j < 10; ++$j)
      {
        $bytes[] = rand(0xF0, 0xF7);
        $bytes[] = rand(0x80, 0xBF);
        $bytes[] = rand(0x80, 0xBF);
        $bytes[] = rand(0x80, 0xBF);
      }
      
      $chars = array();
      $this->_charset->decode($bytes, $chars);
      
      $this->assertIdentical(10, count($chars),
        '%s: 10 unicode characters should be found'
      );
    }
  }
  
  public function testFiveOctetSequencesStartingBetweenF8andFBDecodeToCharsBetween00200000and03FFFFFF()
  {
    for ($i = 0; $i < 100; ++$i)
    {
      $bytes = array(
        rand(0xF8, 0xFB), rand(0x80, 0xBF), rand(0x80, 0xBF), rand(0x80, 0xBF), rand(0x80, 0xBF)
      );
      
      $chars = array();
      $this->_charset->decode($bytes, $chars);
      
      $this->assertIdentical(1, count($chars),
        '%s: 1 unicode character should be found'
      );
      $this->assertTrue(
        ($chars[0] <= 0x03FFFFFF),
        '%s: Decoded character should be in the 0x03FFFFFF range'
      );
    }
  }
  
  public function testStreamOf10GroupsOfFiveOctetsOctetsDecodeToTenUnicodeChars()
  {
    for ($i = 0; $i < 10; ++$i)
    {
      $bytes = array();
      for ($j = 0; $j < 10; ++$j)
      {
        $bytes[] = rand(0xF8, 0xFB);
        $bytes[] = rand(0x80, 0xBF);
        $bytes[] = rand(0x80, 0xBF);
        $bytes[] = rand(0x80, 0xBF);
        $bytes[] = rand(0x80, 0xBF);
      }
      
      $chars = array();
      $this->_charset->decode($bytes, $chars);
      
      $this->assertIdentical(10, count($chars),
        '%s: 10 unicode characters should be found'
      );
    }
  }
  
  public function testSixOctetSequencesStartingBetweenFCandFDDecodeToCharsBetween0400000and7FFFFFF()
  {
    for ($i = 0; $i < 100; ++$i)
    {
      $bytes = array(
        rand(0xFC, 0xFD), rand(0x80, 0xBF), rand(0x80, 0xBF), rand(0x80, 0xBF), rand(0x80, 0xBF), rand(0x80, 0xBF)
      );
      
      $chars = array();
      $this->_charset->decode($bytes, $chars);
      
      $this->assertIdentical(1, count($chars),
        '%s: 1 unicode character should be found'
      );
      $this->assertTrue(
        ($chars[0] <= 0x7FFFFFFF),
        '%s: Decoded character should be in the 0x7FFFFFFF range'
      );
    }
  }
  
  public function testStreamOf10GroupsOfSixOctetsOctetsDecodeToTenUnicodeChars()
  {
    for ($i = 0; $i < 10; ++$i)
    {
      $bytes = array();
      for ($j = 0; $j < 10; ++$j)
      {
        $bytes[] = rand(0xFC, 0xFD);
        $bytes[] = rand(0x80, 0xBF);
        $bytes[] = rand(0x80, 0xBF);
        $bytes[] = rand(0x80, 0xBF);
        $bytes[] = rand(0x80, 0xBF);
        $bytes[] = rand(0x80, 0xBF);
      }
      
      $chars = array();
      $this->_charset->decode($bytes, $chars);
      
      $this->assertIdentical(10, count($chars),
        '%s: 10 unicode characters should be found'
      );
    }
  }
  
  // -- Creation Methods
  
  private function _createCharset()
  {
    return new SwiftX_Charset_UTF8();
  }
  
}
