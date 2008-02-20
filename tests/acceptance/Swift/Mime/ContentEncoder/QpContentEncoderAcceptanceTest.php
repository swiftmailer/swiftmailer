<?php

require_once 'Swift/Mime/ContentEncoder/QpContentEncoder.php';
require_once 'Swift/CharacterStream/ArrayCharacterStream.php';
require_once 'Swift/CharacterReaderFactory/SimpleCharacterReaderFactory.php';
require_once 'Swift/ByteStream/ArrayByteStream.php';

class Swift_Mime_ContentEncoder_QpContentEncoderAcceptanceTest
  extends UnitTestCase
{
  
  private $_samplesDir;
  private $_factory;
  
  public function setUp()
  {
    $this->_samplesDir = realpath(dirname(__FILE__) . '/../../../../_samples/');
    $this->_factory = new Swift_CharacterReaderFactory_SimpleCharacterReaderFactory();
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
      
      $encoding = $encodingDir;
      $charStream = new Swift_CharacterStream_ArrayCharacterStream(
        $this->_factory, $encoding);
      $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
      
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
          
          $os = new Swift_ByteStream_ArrayByteStream();
          $os->write($text);
          
          $is = new Swift_ByteStream_ArrayByteStream();
          $encoder->encodeByteStream($os, $is);
          
          $encoded = '';
          while (false !== $bytes = $is->read(8192))
          {
            $encoded .= $bytes;
          }
          
          $this->assertEqual(
            quoted_printable_decode($encoded), $text,
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
