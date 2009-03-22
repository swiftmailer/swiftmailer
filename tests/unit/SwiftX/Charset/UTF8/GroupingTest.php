<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'SwiftX/Charset/UTF8.php';

class SwiftX_Charset_UTF8_GroupingTest extends Swift_Tests_SwiftUnitTestCase
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
  
  public function testStreamOf10AsciiOctetsGroupToTenOctetSequences()
  {
    for ($i = 0; $i < 10; ++$i)
    {
      $correctSequences = array();
      $bytes = array();
      for ($j = 0; $j < 10; ++$j)
      {
        $sequence = array();
        $sequence[1] = rand(0x00, 0x7F);
        $correctSequences[] = $sequence;
        $bytes = array_merge($bytes, $sequence);
      }
      
      $octetSequences = array();
      $this->_charset->group($bytes, $octetSequences);
      
      $this->assertIdentical($correctSequences, $octetSequences,
        '%s: 10 octet sequences should be found'
      );
    }
  }
  
  public function testStreamOf10PairsOfOctetsOctetsDecodeTo10OctetSequences()
  {
    for ($i = 0; $i < 10; ++$i)
    {
      $correctSequences = array();
      $bytes = array();
      for ($j = 0; $j < 10; ++$j)
      {
        $sequence = array();
        $sequence[1] = rand(0xC0, 0xDF);
        $sequence[2] = rand(0x80, 0xBF);
        $correctSequences[] = $sequence;
        $bytes = array_merge($bytes, $sequence);
      }
      
      $octetSequences = array();
      $this->_charset->group($bytes, $octetSequences);
      
      $this->assertIdentical($correctSequences, $octetSequences,
        '%s: 10 octet sequences should be found'
      );
    }
  }
  
  public function testStreamOf10GroupsOfThreeOctetsOctetsDecodeTo10OctetSequences()
  {
    for ($i = 0; $i < 10; ++$i)
    {
      $correctSequences = array();
      $bytes = array();
      for ($j = 0; $j < 10; ++$j)
      {
        $sequence = array();
        $sequence[1] = rand(0xE0, 0xEF);
        $sequence[2] = rand(0x80, 0xBF);
        $sequence[3] = rand(0x80, 0xBF);
        $correctSequences[] = $sequence;
        $bytes = array_merge($bytes, $sequence);
      }
      
      $octetSequences = array();
      $this->_charset->group($bytes, $octetSequences);
      
      $this->assertIdentical($correctSequences, $octetSequences,
        '%s: 10 octet sequences should be found'
      );
    }
  }
  
  public function testStreamOf10GroupsOfFourOctetsOctetsDecodeTo10OctetSequences()
  {
    for ($i = 0; $i < 10; ++$i)
    {
      $correctSequences = array();
      $bytes = array();
      for ($j = 0; $j < 10; ++$j)
      {
        $sequence = array();
        $sequence[1] = rand(0xF0, 0xF7);
        $sequence[2] = rand(0x80, 0xBF);
        $sequence[3] = rand(0x80, 0xBF);
        $sequence[4] = rand(0x80, 0xBF);
        $correctSequences[] = $sequence;
        $bytes = array_merge($bytes, $sequence);
      }
      
      $octetSequences = array();
      $this->_charset->group($bytes, $octetSequences);
      
      $this->assertIdentical($correctSequences, $octetSequences,
        '%s: 10 octet sequences should be found'
      );
    }
  }
  
  public function testStreamOf10GroupsOfFiveOctetsOctetsDecodeTo10OctetSequences()
  {
    for ($i = 0; $i < 10; ++$i)
    {
      $correctSequences = array();
      $bytes = array();
      for ($j = 0; $j < 10; ++$j)
      {
        $sequence = array();
        $sequence[1] = rand(0xF8, 0xFB);
        $sequence[2] = rand(0x80, 0xBF);
        $sequence[3] = rand(0x80, 0xBF);
        $sequence[4] = rand(0x80, 0xBF);
        $sequence[5] = rand(0x80, 0xBF);
        $correctSequences[] = $sequence;
        $bytes = array_merge($bytes, $sequence);
      }
      
      $octetSequences = array();
      $this->_charset->group($bytes, $octetSequences);
      
      $this->assertIdentical($correctSequences, $octetSequences,
        '%s: 10 octet sequences should be found'
      );
    }
  }
  
  public function testStreamOf10GroupsOfSixOctetsOctetsDecodeTo10OctetSequences()
  {
    for ($i = 0; $i < 10; ++$i)
    {
      $correctSequences = array();
      $bytes = array();
      for ($j = 0; $j < 10; ++$j)
      {
        $sequence = array();
        $sequence[1] = rand(0xFC, 0xFD);
        $sequence[2] = rand(0x80, 0xBF);
        $sequence[3] = rand(0x80, 0xBF);
        $sequence[4] = rand(0x80, 0xBF);
        $sequence[5] = rand(0x80, 0xBF);
        $sequence[6] = rand(0x80, 0xBF);
        $correctSequences[] = $sequence;
        $bytes = array_merge($bytes, $sequence);
      }
      
      $octetSequences = array();
      $this->_charset->group($bytes, $octetSequences);
      
      $this->assertIdentical($correctSequences, $octetSequences,
        '%s: 10 octet sequences should be found'
      );
    }
  }
  
  // -- Creation Methods
  
  private function _createCharset()
  {
    return new SwiftX_Charset_UTF8();
  }
  
}
