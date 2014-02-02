<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Mime/Message.php';
require_once 'Swift/Mime/HeaderSet.php';

class Swift_Signers_OpenDKIMSignerTest extends Swift_Tests_SwiftUnitTestCase
{
    public function skip()
    {
        $this->skipIf(!extension_loaded('opendkim'));
    }

    public function testBasicSigningHeaderManipulation()
    {
        $headers = $this->_createHeaders();
        $messageContent = "Hello World\r\n";
        $signer = new Swift_Signers_OpenDKIMSigner(file_get_contents(dirname(dirname(dirname(dirname(__FILE__)))) . '/_samples/dkim/dkim.test.priv'), 'dummy.nxdomain.be', 'dummySelector');
        /* @var $signer Swift_Signers_HeaderSigner */
        $altered = $signer->getAlteredHeaders();
        $signer->reset();
        // Headers
        $signer->setHeaders($headers);
        // Body
        $signer->startBody();
        $signer->write($messageContent);
        $signer->endBody();
        // Signing
        $signer->addSignature($headers);
    }

    // Default Signing
    public function testSigningDefaults()
    {
        $headerSet = $this->_createHeaderSet();
        $messageContent = "Hello World\r\n";
        $signer = new Swift_Signers_OpenDKIMSigner(file_get_contents(dirname(dirname(dirname(dirname(__FILE__)))) . '/_samples/dkim/dkim.test.priv'), 'dummy.nxdomain.be', 'dummySelector');
        $signer->setSignatureTimestamp('1299879181');
        $altered = $signer->getAlteredHeaders();
        $this->assertEqual(array('DKIM-Signature'), $altered);
        $signer->reset();
        $signer->setHeaders($headerSet);
        $this->assertFalse($headerSet->has('DKIM-Signature'));
        $signer->startBody();
        $signer->write($messageContent);
        $signer->endBody();
        $signer->addSignature($headerSet);
        $this->assertTrue($headerSet->has('DKIM-Signature'));
        $dkim = $headerSet->getAll('DKIM-Signature');
        $sig = end($dkim);
        $this->assertEqual($sig->getValue(), "v=1; a=rsa-sha1; c=simple/simple; d=dummy.nxdomain.be;\r\n\ts=dummySelector; t=1299879181; i=@dummy.nxdomain.be;\r\n\tbh=2jmj7l5rSw0yVb/vlWAYkK/YBwk=; h=From:To:Subject:Date;\r\n\tb=qIfE4nGTcB25Ft/6bPwlmXxFl8tSpSc/TT1q+ypVd6baxfuo2bMCTqz2zyuqFiZDqY4N\r\n\t Aqlu8UvfSiswblfhCPy2y1+igFUrzJ7xuTxsWmgOMpGgE2uy0o3sAUYQvkWobR6RdPvCB\r\n\t KzDDnUjjyveKXdCx/0vS+WNCQGucBrNUf8=");
    }

    // SHA256 Signing
    public function testSigning256()
    {
        $headerSet = $this->_createHeaderSet();
        $messageContent = "Hello World\r\n";
        $signer = new Swift_Signers_OpenDKIMSigner(file_get_contents(dirname(dirname(dirname(dirname(__FILE__)))) . '/_samples/dkim/dkim.test.priv'), 'dummy.nxdomain.be', 'dummySelector');
        $signer->setHashAlgorithm('rsa-sha256');
        $signer->setSignatureTimestamp('1299879181');
        $altered = $signer->getAlteredHeaders();
        $this->assertEqual(array('DKIM-Signature'), $altered);
        $signer->reset();
        $signer->setHeaders($headerSet);
        $this->assertFalse($headerSet->has('DKIM-Signature'));
        $signer->startBody();
        $signer->write($messageContent);
        $signer->endBody();
        $signer->addSignature($headerSet);
        $this->assertTrue($headerSet->has('DKIM-Signature'));
        $dkim = $headerSet->getAll('DKIM-Signature');
        $sig = reset($dkim);
        $this->assertEqual($sig->getValue(), "v=1; a=rsa-sha256; c=simple/simple; d=dummy.nxdomain.be;\r\n\ts=dummySelector; t=1299879181; i=@dummy.nxdomain.be;\r\n\tbh=47DEQpj8HBSa+/TImW+5JCeuQeRkm5NMpJWZG3hSuFU=;\r\n\th=From:To:Subject:Date;\r\n\tb=hpf821+a+3QQRW+772e6U3jkzmzfhcBayDu58kiBb09EYXYTEysiBSOwQIH7d7SOAmKt\r\n\t PDS57VqJRZz83iK/bNQ/nNpVloZL1grwTqYnL6ImICODpNA/D8eVxmT/0URSfxme/Do4J\r\n\t +ZE3jz9Jw7AJn4xtiJN+5q3pNOGtDB3N4I=");
    }

    // Relaxed/Relaxed Hash Signing
    public function testSigningRelaxedRelaxed256()
    {
    	$headerSet = $this->_createHeaderSet();
        $messageContent = "Hello World\r\n";
        $signer = new Swift_Signers_OpenDKIMSigner(file_get_contents(dirname(dirname(dirname(dirname(__FILE__)))) . '/_samples/dkim/dkim.test.priv'), 'dummy.nxdomain.be', 'dummySelector');
        $signer->setHashAlgorithm('rsa-sha256');
        $signer->setSignatureTimestamp('1299879181');
        $signer->setBodyCanon('relaxed');
        $signer->setHeaderCanon('relaxed');
        $altered = $signer->getAlteredHeaders();
        $this->assertEqual(array('DKIM-Signature'), $altered);
        $signer->reset();
        $signer->setHeaders($headerSet);
        $this->assertFalse($headerSet->has('DKIM-Signature'));
        $signer->startBody();
        $signer->write($messageContent);
        $signer->endBody();
        $signer->addSignature($headerSet);
        $this->assertTrue($headerSet->has('DKIM-Signature'));
        $dkim = $headerSet->getAll('DKIM-Signature');
        $sig = reset($dkim);
        $this->assertEqual($sig->getValue(), "v=1; a=rsa-sha256; c=relaxed/relaxed; d=dummy.nxdomain.be;\r\n\ts=dummySelector; t=1299879181; i=@dummy.nxdomain.be;\r\n\tbh=47DEQpj8HBSa+/TImW+5JCeuQeRkm5NMpJWZG3hSuFU=;\r\n\th=From:To:Subject:Date;\r\n\tb=l/fZ+dcpgAONdLb1zRD82iiG8MaR4L3Zl1QktCx658KCY6txdEE0QPssee/wKPt6piSC\r\n\t RtSdYBQZwk1XIJk0yvh/T0gXBtVRk1RIqNuBQMmNOiYUettKTsXUl0l1NxI3tA17GGven\r\n\t 1gwUQ0MawBNR65AhxE9b+x25RSBOPm4br0=");
    }


    // Relaxed/Simple Hash Signing
    public function testSigningRelaxedSimple256()
    {
        $headerSet = $this->_createHeaderSet();
        $messageContent = "Hello World\r\n";
        $signer = new Swift_Signers_OpenDKIMSigner(file_get_contents(dirname(dirname(dirname(dirname(__FILE__)))) . '/_samples/dkim/dkim.test.priv'), 'dummy.nxdomain.be', 'dummySelector');
        $signer->setHashAlgorithm('rsa-sha256');
        $signer->setSignatureTimestamp('1299879181');
        $signer->setHeaderCanon('relaxed');
        $altered = $signer->getAlteredHeaders();
        $this->assertEqual(array('DKIM-Signature'), $altered);
        $signer->reset();
        $signer->setHeaders($headerSet);
        $this->assertFalse($headerSet->has('DKIM-Signature'));
        $signer->startBody();
        $signer->write($messageContent);
        $signer->endBody();
        $signer->addSignature($headerSet);
        $this->assertTrue($headerSet->has('DKIM-Signature'));
        $dkim = $headerSet->getAll('DKIM-Signature');
        $sig = reset($dkim);
        $this->assertEqual($sig->getValue(), "v=1; a=rsa-sha256; c=relaxed/simple; d=dummy.nxdomain.be;\r\n\ts=dummySelector; t=1299879181; i=@dummy.nxdomain.be;\r\n\tbh=47DEQpj8HBSa+/TImW+5JCeuQeRkm5NMpJWZG3hSuFU=;\r\n\th=From:To:Subject:Date;\r\n\tb=YN7qrNsyUT7qBsxpG06CaDCF/vzbHJ5z/Id/p+5YXFGPBgUFoPCN3bal9GPhdMttamlY\r\n\t XwhZlCvKOl8DM1aVbaqJ0RPh708av4oLOXgfMI8uMG1qdWp/CviDiUxs0OdsawzHobXQa\r\n\t 8rxjVXKVCrCeDf80oJD9ClWxGv9EkZcQqw=");
    }

    // Simple/Relaxed Hash Signing
    public function testSigningSimpleRelaxed256()
    {
    	$headerSet = $this->_createHeaderSet();
        $messageContent = "Hello World\r\n";
        $signer = new Swift_Signers_OpenDKIMSigner(file_get_contents(dirname(dirname(dirname(dirname(__FILE__)))) . '/_samples/dkim/dkim.test.priv'), 'dummy.nxdomain.be', 'dummySelector');
        $signer->setHashAlgorithm('rsa-sha256');
        $signer->setSignatureTimestamp('1299879181');
        $signer->setBodyCanon('relaxed');
        $altered = $signer->getAlteredHeaders();
        $this->assertEqual(array('DKIM-Signature'), $altered);
        $signer->reset();
        $signer->setHeaders($headerSet);
        $this->assertFalse($headerSet->has('DKIM-Signature'));
        $signer->startBody();
        $signer->write($messageContent);
        $signer->endBody();
        $signer->addSignature($headerSet);
        $this->assertTrue($headerSet->has('DKIM-Signature'));
        $dkim = $headerSet->getAll('DKIM-Signature');
        $sig = reset($dkim);
        $this->assertEqual($sig->getValue(), "v=1; a=rsa-sha256; c=simple/relaxed; d=dummy.nxdomain.be;\r\n\ts=dummySelector; t=1299879181; i=@dummy.nxdomain.be;\r\n\tbh=47DEQpj8HBSa+/TImW+5JCeuQeRkm5NMpJWZG3hSuFU=;\r\n\th=From:To:Subject:Date;\r\n\tb=gXJDBWbwGxx1nx2LIf8dQpq95lL33Pvr68igO1Z831at9ZXelUS2cTwomsem0oxv2Slf\r\n\t sjfjtPNSLjGLjjvZ//c2+oXqXD5fRK1tbmUFGxnWtNi36UnTlWLp1yrao4K4dzCpkCG6G\r\n\t 3oe0EmA261EaN/TgfoKs9LClWtfuIXAaD8=");
    }

    // -- Creation Methods
    private function _createHeaderSet()
    {
        $cache = new Swift_KeyCache_ArrayKeyCache(new Swift_KeyCache_SimpleKeyCacheInputStream());
        $factory = new Swift_CharacterReaderFactory_SimpleCharacterReaderFactory();
        $contentEncoder = new Swift_Mime_ContentEncoder_Base64ContentEncoder();

        $headerEncoder = new Swift_Mime_HeaderEncoder_QpHeaderEncoder(new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8'));
        $paramEncoder = new Swift_Encoder_Rfc2231Encoder(new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8'));
        $grammar = new Swift_Mime_Grammar();
        $headers = new Swift_Mime_SimpleHeaderSet(new Swift_Mime_SimpleHeaderFactory($headerEncoder, $paramEncoder, $grammar));
        $headers->addMailboxHeader('From', 'test@example.com');
        $headers->addMailboxHeader('To', 'test@example.com');
        $headers->addTextHeader('Subject', 'Hello World');
        $headers->addDateHeader('Date', 1299879181);
        return $headers;
    }

    /**
     * @return Swift_Mime_Headers
     */
    private function _createHeaders()
    {
        $x = 0;
        $cache = new Swift_KeyCache_ArrayKeyCache(new Swift_KeyCache_SimpleKeyCacheInputStream());
        $factory = new Swift_CharacterReaderFactory_SimpleCharacterReaderFactory();
        $contentEncoder = new Swift_Mime_ContentEncoder_Base64ContentEncoder();

        $headerEncoder = new Swift_Mime_HeaderEncoder_QpHeaderEncoder(new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8'));
        $paramEncoder = new Swift_Encoder_Rfc2231Encoder(new Swift_CharacterStream_ArrayCharacterStream($factory, 'utf-8'));
        $grammar = new Swift_Mime_Grammar();
        $headerFactory = new Swift_Mime_SimpleHeaderFactory($headerEncoder, $paramEncoder, $grammar);
        $headers = $this->_mock('Swift_Mime_HeaderSet');
        $this->_checking(Expectations::create()
                ->ignoring($headers)
                ->listAll()
                ->returns(array('From', 'To', 'Date', 'Subject'))
                ->ignoring($headers)
                ->has('From')
                ->returns(True)
                ->ignoring($headers)
                ->getAll('From')
                ->returns(array($headerFactory->createMailboxHeader('From', 'test@test.test')))
                ->ignoring($headers)
                ->has('To')
                ->returns(True)
                ->ignoring($headers)
                ->getAll('To')
                ->returns(array($headerFactory->createMailboxHeader('To', 'test@test.test')))
                ->ignoring($headers)
                ->has('Date')
                ->returns(True)
                ->ignoring($headers)
                ->getAll('Date')
                ->returns(array($headerFactory->createDateHeader('Date', 1299879181)))
                ->ignoring($headers)
                ->has('Subject')
                ->returns(True)
                ->ignoring($headers)
                ->getAll('Subject')
                ->returns(array($headerFactory->createTextHeader('Subject', 'Foo Bar Text Message')))
                ->ignoring($headers)
                ->set(any())
                ->returns(true));
        return $headers;
    }
}
