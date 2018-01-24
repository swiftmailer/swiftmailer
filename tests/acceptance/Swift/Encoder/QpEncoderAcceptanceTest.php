<?php

class Swift_Encoder_QpEncoderAcceptanceTest extends \PHPUnit\Framework\TestCase
{
    private $samplesDir;
    private $factory;

    protected function setUp()
    {
        $this->samplesDir = realpath(__DIR__.'/../../../_samples/charsets');
        $this->factory = new Swift_CharacterReaderFactory_SimpleCharacterReaderFactory();
    }

    public function testEncodingAndDecodingSamples()
    {
        $sampleFp = opendir($this->samplesDir);
        while (false !== $encodingDir = readdir($sampleFp)) {
            if ('.' == substr($encodingDir, 0, 1)) {
                continue;
            }

            $encoding = $encodingDir;
            $charStream = new Swift_CharacterStream_ArrayCharacterStream(
                $this->factory, $encoding);
            $encoder = new Swift_Encoder_QpEncoder($charStream);

            $sampleDir = $this->samplesDir.'/'.$encodingDir;

            if (is_dir($sampleDir)) {
                $fileFp = opendir($sampleDir);
                while (false !== $sampleFile = readdir($fileFp)) {
                    if ('.' == substr($sampleFile, 0, 1)) {
                        continue;
                    }

                    $text = file_get_contents($sampleDir.'/'.$sampleFile);
                    $encodedText = $encoder->encodeString($text);

                    foreach (explode("\r\n", $encodedText) as $line) {
                        $this->assertLessThanOrEqual(76, strlen($line));
                    }

                    $this->assertEquals(
                        quoted_printable_decode($encodedText), $text,
                        '%s: Encoded string should decode back to original string for sample '.
                        $sampleDir.'/'.$sampleFile
                        );
                }
                closedir($fileFp);
            }
        }
        closedir($sampleFp);
    }
}
