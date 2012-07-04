<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Encoder/QpEncoder.php';
require_once 'Swift/CharacterStream/ArrayCharacterStream.php';
require_once 'Swift/CharacterReaderFactory/SimpleCharacterReaderFactory.php';

class Swift_Encoder_QpEncoderAcceptanceTest
    extends Swift_Tests_SwiftUnitTestCase
{
    private $_samplesDir;
    private $_factory;

    public function setUp()
    {
        $this->_samplesDir = realpath(dirname(__FILE__) . '/../../../_samples/charsets');
        $this->_factory = new Swift_CharacterReaderFactory_SimpleCharacterReaderFactory();
    }

    public function testEncodingAndDecodingSamples()
    {
        $sampleFp = opendir($this->_samplesDir);
        while (false !== $encodingDir = readdir($sampleFp)) {
            if (substr($encodingDir, 0, 1) == '.') {
                continue;
            }

            $encoding = $encodingDir;
            $charStream = new Swift_CharacterStream_ArrayCharacterStream(
                $this->_factory, $encoding);
            $encoder = new Swift_Encoder_QpEncoder($charStream);

            $sampleDir = $this->_samplesDir . '/' . $encodingDir;

            if (is_dir($sampleDir)) {

                $fileFp = opendir($sampleDir);
                while (false !== $sampleFile = readdir($fileFp)) {
                    if (substr($sampleFile, 0, 1) == '.') {
                        continue;
                    }

                    $text = file_get_contents($sampleDir . '/' . $sampleFile);
                    $encodedText = $encoder->encodeString($text);

                    $this->assertEqual(
                        quoted_printable_decode($encodedText), $text,
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
