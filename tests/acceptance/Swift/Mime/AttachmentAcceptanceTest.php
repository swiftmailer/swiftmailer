<?php

require_once 'Swift/Mime/Attachment.php';
require_once 'Swift/Mime/Headers/UnstructuredHeader.php';
require_once 'Swift/Mime/Headers/ParameterizedHeader.php';
require_once 'Swift/Encoder/Rfc2231Encoder.php';
require_once 'Swift/Mime/ContentEncoder/Base64ContentEncoder.php';
require_once 'Swift/Mime/HeaderEncoder/QpHeaderEncoder.php';
require_once 'Swift/CharacterStream/ArrayCharacterStream.php';
require_once 'Swift/CharacterReaderFactory/SimpleCharacterReaderFactory.php';
require_once 'Swift/KeyCache/ArrayKeyCache.php';
require_once 'Swift/KeyCache/SimpleKeyCacheInputStream.php';
require_once 'Swift/Mime/Grammar.php';

class Swift_Mime_AttachmentAcceptanceTest extends UnitTestCase
{
    private $_contentEncoder;
    private $_cache;
    private $_grammar;
    private $_headers;

    public function setUp()
    {
        $this->_cache = new Swift_KeyCache_ArrayKeyCache(
            new Swift_KeyCache_SimpleKeyCacheInputStream()
            );
        $factory = new Swift_CharacterReaderFactory_SimpleCharacterReaderFactory();
        $this->_contentEncoder = new Swift_Mime_ContentEncoder_Base64ContentEncoder();

        $headerEncoder = new Swift_Mime_HeaderEncoder_QpHeaderEncoder(
            new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8')
            );
        $paramEncoder = new Swift_Encoder_Rfc2231Encoder(
            new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8')
            );
        $this->_grammar = new Swift_Mime_Grammar();
        $this->_headers = new Swift_Mime_SimpleHeaderSet(
            new Swift_Mime_SimpleHeaderFactory($headerEncoder, $paramEncoder, $this->_grammar)
            );
    }

    public function testDispositionIsSetInHeader()
    {
        $attachment = $this->_createAttachment();
        $attachment->setContentType('application/pdf');
        $attachment->setDisposition('inline');
        $this->assertEqual(
            'Content-Type: application/pdf' . "\r\n" .
            'Content-Transfer-Encoding: base64' . "\r\n" .
            'Content-Disposition: inline' . "\r\n",
            $attachment->toString()
            );
    }

    public function testDispositionIsAttachmentByDefault()
    {
        $attachment = $this->_createAttachment();
        $attachment->setContentType('application/pdf');
        $this->assertEqual(
            'Content-Type: application/pdf' . "\r\n" .
            'Content-Transfer-Encoding: base64' . "\r\n" .
            'Content-Disposition: attachment' . "\r\n",
            $attachment->toString()
            );
    }

    public function testFilenameIsSetInHeader()
    {
        $attachment = $this->_createAttachment();
        $attachment->setContentType('application/pdf');
        $attachment->setFilename('foo.pdf');
        $this->assertEqual(
            'Content-Type: application/pdf; name=foo.pdf' . "\r\n" .
            'Content-Transfer-Encoding: base64' . "\r\n" .
            'Content-Disposition: attachment; filename=foo.pdf' . "\r\n",
            $attachment->toString()
            );
    }

    public function testSizeIsSetInHeader()
    {
        $attachment = $this->_createAttachment();
        $attachment->setContentType('application/pdf');
        $attachment->setSize(12340);
        $this->assertEqual(
            'Content-Type: application/pdf' . "\r\n" .
            'Content-Transfer-Encoding: base64' . "\r\n" .
            'Content-Disposition: attachment; size=12340' . "\r\n",
            $attachment->toString()
            );
    }

    public function testMultipleParametersInHeader()
    {
        $attachment = $this->_createAttachment();
        $attachment->setContentType('application/pdf');
        $attachment->setFilename('foo.pdf');
        $attachment->setSize(12340);
        $this->assertEqual(
            'Content-Type: application/pdf; name=foo.pdf' . "\r\n" .
            'Content-Transfer-Encoding: base64' . "\r\n" .
            'Content-Disposition: attachment; filename=foo.pdf; size=12340' . "\r\n",
            $attachment->toString()
            );
    }

    public function testEndToEnd()
    {
        $attachment = $this->_createAttachment();
        $attachment->setContentType('application/pdf');
        $attachment->setFilename('foo.pdf');
        $attachment->setSize(12340);
        $attachment->setBody('abcd');
        $this->assertEqual(
            'Content-Type: application/pdf; name=foo.pdf' . "\r\n" .
            'Content-Transfer-Encoding: base64' . "\r\n" .
            'Content-Disposition: attachment; filename=foo.pdf; size=12340' . "\r\n" .
            "\r\n" .
            base64_encode('abcd'),
            $attachment->toString()
            );
    }

    // -- Private helpers

    protected function _createAttachment()
    {
        $entity = new Swift_Mime_Attachment(
            $this->_headers,
            $this->_contentEncoder,
            $this->_cache,
            $this->_grammar
            );

        return $entity;
    }
}
