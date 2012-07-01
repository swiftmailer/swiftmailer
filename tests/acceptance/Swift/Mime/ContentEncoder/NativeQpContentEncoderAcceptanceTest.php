<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Mime/ContentEncoder/NativeQpContentEncoder.php';
require_once 'Swift/CharacterStream/ArrayCharacterStream.php';
require_once 'Swift/CharacterReaderFactory/SimpleCharacterReaderFactory.php';
require_once 'Swift/ByteStream/ArrayByteStream.php';

class Swift_Mime_ContentEncoder_NativeQpContentEncoderAcceptanceTest
  extends Swift_Tests_SwiftUnitTestCase
{
  /**
   * @var Swift_Mime_ContentEncoder_NativeQpContentEncoder
   */
  protected $_encoder;

  public function setUp()
  {
    $this->_samplesDir = realpath(dirname(__FILE__) . '/../../../../_samples/charsets');
    $this->_encoder = new Swift_Mime_ContentEncoder_NativeQpContentEncoder();
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
          $this->_encoder->encodeByteStream($os, $is);

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
  
  public function testEncodingAndDecodingSamplesFromDiConfiguredInstance()
  {
    $encoder = $this->_createEncoderFromContainer();
    $this->assertSame('=C3=A4=C3=B6=C3=BC=C3=9F', $encoder->encodeString('äöüß'));
  }

  public function testCharsetChangeNotImplemented()
  {
    $this->_encoder->charsetChanged('utf-8');
    $this->expectException(new RuntimeException('Charset "charset" not supported. NativeQpContentEncoder only supports "utf-8"'));
    $this->_encoder->charsetChanged('charset');
  }

  public function testGetName()
  {
    $this->assertSame('quoted-printable', $this->_encoder->getName());
  }

  // -- Private Methods
  
  private function _createEncoderFromContainer()
  {
    return Swift_DependencyContainer::getInstance()
      ->lookup('mime.nativeqpcontentencoder')
      ;
  }
  
}
