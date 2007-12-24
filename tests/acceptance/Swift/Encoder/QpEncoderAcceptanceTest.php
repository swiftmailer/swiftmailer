<?php

require_once 'Swift/Encoder/QpEncoder.php';
require_once 'Swift/CharacterStream/ArrayCharacterStream.php';
require_once 'Swift/CharacterReaderFactory.php';
require_once 'Swift/CharacterReader/Utf8Reader.php';

Mock::generate(
  'Swift_CharacterReaderFactory', 'Swift_MockCharacterReaderFactory'
  );

class Swift_Encoder_QpEncoderAcceptanceTest extends UnitTestCase
{
  
  private $_samplesDir;
  private $_encoder;
  private $_charset = 'utf-8';
  private $_charStream;
  
  public function setUp()
  {
    $this->_samplesDir = realpath(dirname(__FILE__) . '/../../../samples/utf8');
    
    $charReader = new Swift_CharacterReader_Utf8Reader();
    
    $factory = new Swift_MockCharacterReaderFactory();
    $factory->setReturnValue('getReaderFor', $charReader);
    
    $this->_charStream = new Swift_CharacterStream_ArrayCharacterStream(
      null, $this->_charset, $factory);
    $this->_encoder = new Swift_Encoder_QpEncoder($this->_charStream);
  }
  
  public function testEncodingAndDecodingSamples()
  {
    $fp = opendir($this->_samplesDir);
    
    while (false !== $f = readdir($fp))
    {
      if (substr($f, 0, 1) == '.')
      {
        continue;
      }
      
      $sampleFile = $this->_samplesDir . '/' . $f;
      
      if (is_file($sampleFile))
      {
        $text = file_get_contents($sampleFile);
        $encodedText = $this->_encoder->encodeString($text);
        
        $this->assertEqual(
          quoted_printable_decode($encodedText), $text,
          '%s: Encoded string should decode back to original string for sample ' .
          $sampleFile
          );
      }
      
    }
    
    closedir($fp);
  }
  
}
