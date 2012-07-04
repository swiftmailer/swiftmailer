<?php

require_once 'Swift/Mime/ContentEncoder/PlainContentEncoder.php';
require_once 'Swift/ByteStream/ArrayByteStream.php';

class Swift_Mime_ContentEncoder_PlainContentEncoderAcceptanceTest
    extends UnitTestCase
{
    private $_samplesDir;
    private $_encoder;

    public function setUp()
    {
        $this->_samplesDir = realpath(dirname(__FILE__) . '/../../../../_samples/charsets');
        $this->_encoder = new Swift_Mime_ContentEncoder_PlainContentEncoder('8bit');
    }

    public function testEncodingAndDecodingSamplesString()
    {
        $sampleFp = opendir($this->_samplesDir);
        while (false !== $encodingDir = readdir($sampleFp)) {
            if (substr($encodingDir, 0, 1) == '.') {
                continue;
            }

            $sampleDir = $this->_samplesDir . '/' . $encodingDir;

            if (is_dir($sampleDir)) {

                $fileFp = opendir($sampleDir);
                while (false !== $sampleFile = readdir($fileFp)) {
                    if (substr($sampleFile, 0, 1) == '.') {
                        continue;
                    }

                    $text = file_get_contents($sampleDir . '/' . $sampleFile);
                    $encodedText = $this->_encoder->encodeString($text);

                    $this->assertEqual(
                        $encodedText, $text,
                        '%s: Encoded string should be identical to original string for sample ' .
                        $sampleDir . '/' . $sampleFile
                        );
                }
                closedir($fileFp);
            }

        }
        closedir($sampleFp);
    }

    public function testEncodingAndDecodingSamplesByteStream()
    {
        $sampleFp = opendir($this->_samplesDir);
        while (false !== $encodingDir = readdir($sampleFp)) {
            if (substr($encodingDir, 0, 1) == '.') {
                continue;
            }

            $sampleDir = $this->_samplesDir . '/' . $encodingDir;

            if (is_dir($sampleDir)) {

                $fileFp = opendir($sampleDir);
                while (false !== $sampleFile = readdir($fileFp)) {
                    if (substr($sampleFile, 0, 1) == '.') {
                        continue;
                    }

                    $text = file_get_contents($sampleDir . '/' . $sampleFile);

                    $os = new Swift_ByteStream_ArrayByteStream();
                    $os->write($text);

                    $is = new Swift_ByteStream_ArrayByteStream();

                    $this->_encoder->encodeByteStream($os, $is);

                    $encoded = '';
                    while (false !== $bytes = $is->read(8192)) {
                        $encoded .= $bytes;
                    }

                    $this->assertEqual(
                        $encoded, $text,
                        '%s: Encoded string should be identical to original string for sample ' .
                        $sampleDir . '/' . $sampleFile
                        );
                }
                closedir($fileFp);
            }

        }
        closedir($sampleFp);
    }
}
