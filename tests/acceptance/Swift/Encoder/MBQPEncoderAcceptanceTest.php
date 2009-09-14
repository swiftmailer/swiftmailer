<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Encoder/QpEncoder.php';
require_once 'Swift/CharacterStream/ArrayCharacterStream.php';
require_once 'Swift/CharacterReaderFactory/SimpleCharacterReaderFactory.php';

class Swift_Encoder_MBQPEncoderAcceptanceTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_samplesDir;
  private $_factory;
  
  public function setUp()
  {
    $this->_samplesDir = realpath(dirname(__FILE__) . '/../../../_samples/charsets');
  }
  
  public function testEncodingAndDecodingSamples()
  {
    $sampleFp = opendir($this->_samplesDir);
    while (false !== $encodingDir = readdir($sampleFp))
    {
      if (substr($encodingDir, 0, 1) == '.')
      {
        continue;
      }
      
      $encoder = new Swift_Encoder_MBQPEncoder();
      $encoder->charsetChanged($encodingDir);
      
      $sampleDir = $this->_samplesDir . '/' . $encodingDir;
      
      if (is_dir($sampleDir))
      {
        
        $fileFp = opendir($sampleDir);
        while (false !== $sampleFile = readdir($fileFp))
        {
          if (substr($sampleFile, 0, 1) == '.')
          {
            continue;
          }
        
          $text = file_get_contents($sampleDir . '/' . $sampleFile);
          $encodedText = $encoder->encodeString($text);
	  $decodedText = quoted_printable_decode($encodedText);
        
          $this->assertEqual(
            str_replace(array("\r\n","\r","\n"), array("\n", "\n", "\r\n"), quoted_printable_decode($encodedText)), 		    str_replace(array("\r\n","\r","\n"), array("\n", "\n", "\r\n"), $text),
            '%s: Encoded string should decode back to original string for sample ' .
            $sampleDir . '/' . $sampleFile
            );
        }
        closedir($fileFp); 
      }
      
    }
    closedir($sampleFp);
  }
  
}
