<?php

require_once 'Swift/Encoder/Base64Encoder.php';

class Swift_Encoder_Base64EncoderAcceptanceTest extends UnitTestCase
{
  
  private $_samplesDir;
  private $_encoder;
  
  public function setUp()
  {
    $this->_samplesDir = realpath(dirname(__FILE__) . '/../../../samples/utf8');
    $this->_encoder = new Swift_Encoder_Base64Encoder();
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
          base64_decode($encodedText), $text,
          '%s: Encoded string should decode back to original string for sample ' .
          $sampleFile
          );
      }
      
    }
    
    closedir($fp);
  }
  
}
