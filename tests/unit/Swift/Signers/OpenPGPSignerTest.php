<?php

use PHPUnit\Framework\TestCase;

class Swift_Signers_OpenPGPSignerTest extends TestCase
{
    /*private*/ const GNUPG_HOME = __DIR__ . '/gnupg';

    /*private*/ const BEGIN_PGP_MESSAGE = '-----BEGIN PGP MESSAGE-----';

    /*private*/ const END_PGP_MESSAGE = '-----END PGP MESSAGE-----';

    /*private*/ const BEGIN_PGP_SIGNATURE = '-----BEGIN PGP SIGNATURE-----';

    /**
     * @var gnupg
     */
    private $gnupg;

    public function setUp()
    {
        putenv("GNUPGHOME=" . escapeshellcmd(self::GNUPG_HOME));
        $this->gnupg = new gnupg();
    }

    public function testEncryption()
    {
        $signer = new Swift_Signers_OpenPGPSigner(self::GNUPG_HOME, false, true);
        $message = $this->createTestMessage();
        $plaintext = $signer->extractPlainTextBody($message);

        $signer->signMessage($message);
        $this->assertEquals($plaintext, $this->decryptEncryptedMessage($message));
    }

    public function testMultipartEncryption()
    {
        $signer = new Swift_Signers_OpenPGPSigner(self::GNUPG_HOME, false, true);
        $message = $this->createMultipartTestMessage();
        $originalInnerBoundary = $message->getBoundary();
        $plaintext = $signer->extractPlainTextBody($message);
        $message->setBoundary($originalInnerBoundary); // Reset boundary to original value

        $signer->signMessage($message);

        $this->assertEquals($plaintext, $this->decryptEncryptedMessage($message));
    }

    public function testSigning()
    {
        $signer = new Swift_Signers_OpenPGPSigner(__DIR__ . '/gnupg', true, false);
        $message = $this->createTestMessage();
        $signer->signMessage($message);

        $this->assertEquals('multipart/signed', $message->getContentType());
        $body = $message->getBody();

        $expectedBody = <<<EOT
This is an OpenPGP/MIME signed message \(RFC 4880 and 3156\)

--{$message->getBoundary()}
Content-Type: text/plain; charset=utf-8
Content-Transfer-Encoding: quoted-printable

Hello World

--{$message->getBoundary()}
Content-Type: application/pgp-signature; name="signature.asc"
Content-Description: OpenPGP digital signature
Content-Disposition: attachment; filename="signature.asc"

-----BEGIN PGP SIGNATURE-----
(Version: GnuPG v2)?
(?:^[a-zA-Z0-9\/\\r\\n+]*={0,2})
=(?:[a-zA-Z0-9\/\\r\\n+]*)
-----END PGP SIGNATURE-----


--{$message->getBoundary()}--
EOT;
        $this->verifySigning($expectedBody, $body, $message, $signer);
    }

    public function testMultipartSigning()
    {
        $signer = new Swift_Signers_OpenPGPSigner(__DIR__ . '/gnupg', true, false);
        $message = $this->createMultipartTestMessage();
        $signer->signMessage($message);

        $this->assertEquals('multipart/signed', $message->getContentType());
        $body = $message->getBody();

        // Extract inner boundary
        if (!preg_match("%Content-Type: multipart/alternative;\r\n boundary=\"(.*)\"%", $body, $matches)) {
            $this->fail('Failed to extract inner message boundary');
        }
        $innerBoundary = $matches[1];

        $expectedBody = <<<EOT
This is an OpenPGP/MIME signed message \(RFC 4880 and 3156\)

--{$message->getBoundary()}
Content-Type: multipart/alternative;
 boundary="{$innerBoundary}"


--{$innerBoundary}
Content-Type: text/html; charset=utf-8
Content-Transfer-Encoding: quoted-printable

Some HTML message

--{$innerBoundary}
Content-Type: multipart/alternative; charset=utf-8
Content-Transfer-Encoding: quoted-printable

Hello World

--{$innerBoundary}--

--{$message->getBoundary()}
Content-Type: application/pgp-signature; name="signature.asc"
Content-Description: OpenPGP digital signature
Content-Disposition: attachment; filename="signature.asc"

-----BEGIN PGP SIGNATURE-----
(Version: GnuPG v2)?
(?:^[a-zA-Z0-9\/\\r\\n+]*={0,2})
=(?:[a-zA-Z0-9\/\\r\\n+]*)
-----END PGP SIGNATURE-----


--{$message->getBoundary()}--
EOT;

        $this->verifySigning($expectedBody, $body, $message, $signer);
    }

    public function testSigningAndEncrypting()
    {
        $signer = new Swift_Signers_OpenPGPSigner(self::GNUPG_HOME, true, true);
        $message = $this->createTestMessage();
        $signer->signMessage($message);

        // The decrypted message text contains the signed message
        $decrypted = $this->decryptEncryptedMessage($message);

        // In case of the decrypted signed message, the signing mime headers are on top of the message
        $expectedMessageStart = <<<EOT
^Content-Type: multipart/signed; micalg=pgp-sha256;
 protocol="application/pgp-signature";
 boundary="{$message->getBoundary()}"
EOT;

        // File is UNIX encoded so convert them to correct line ending
        $expectedMessageStart = str_replace("\n", "\r\n", $expectedMessageStart);
        $this->assertRegExp('%^' . $expectedMessageStart . '\s*%m', $decrypted);

        // Remove message start headers to verify signing next
        $decrypted = preg_replace('%^' . $expectedMessageStart . '\s*%m', '', $decrypted);

        $expectedBody = <<<EOT
This is an OpenPGP/MIME signed message \(RFC 4880 and 3156\)

--{$message->getBoundary()}
Content-Type: text/plain; charset=utf-8
Content-Transfer-Encoding: quoted-printable

Hello World

--{$message->getBoundary()}
Content-Type: application/pgp-signature; name="signature.asc"
Content-Description: OpenPGP digital signature
Content-Disposition: attachment; filename="signature.asc"

-----BEGIN PGP SIGNATURE-----
(Version: GnuPG v2)?
(?:^[a-zA-Z0-9\/\\r\\n+]*={0,2})
=(?:[a-zA-Z0-9\/\\r\\n+]*)
-----END PGP SIGNATURE-----


--{$message->getBoundary()}--
EOT;
        $this->verifySigning($expectedBody, $decrypted, $message, $signer);
    }

    /**
     * Verifies that the message was signed correctly and matches the expected format.
     *
     * @param string                      $expectedBody
     * @param string                      $body
     * @param Swift_Message               $message
     * @param Swift_Signers_OpenPGPSigner $signer
     */
    private function verifySigning(string $expectedBody, string $body, Swift_Message $message, Swift_Signers_OpenPGPSigner $signer)
    {
        // File is UNIX encoded so convert them to correct line ending
        $expectedBody = str_replace("\n", "\r\n", $expectedBody);

        $this->assertRegExp('%^' . $expectedBody . '\s*%m', trim($body));

        $parts = explode('--' . $message->getBoundary(), $body);
        $parts = array_map('trim', $parts);

        $this->assertCount(4, $parts);
        $this->assertEquals('This is an OpenPGP/MIME signed message (RFC 4880 and 3156)', trim($parts[0]));

        $signature = trim(substr($body, strpos($body, self::BEGIN_PGP_SIGNATURE)));
        $this->assertEquals(
            '7D8E3D7D2B7CEC11CE28875FB1FC1B65DEB639F7',
            $this->gnupg->verify($signer->cleanUpLineEndings($parts[1]), $signature)[0]['fingerprint']
        );

        // Was exploded by boundary, and the third part should contain only the end of the final boundary
        $this->assertEquals('--', $parts[3]);
    }

    /**
     * Extract the ciphertext from the given encrypted message.
     *
     * @param Swift_Message $message
     *
     * @return string
     */
    private function decryptEncryptedMessage(Swift_Message $message): string
    {
        $contentTypePattern = '#^Content-Type: multipart/encrypted; protocol="application/pgp-encrypted";#';
        if (!preg_match($contentTypePattern, $message->getHeaders()->get('Content-Type')->toString())) {
            $this->fail('Content-type does not match. Message not encrypted?');
        }

        $body = $message->getBody();
        $this->assertTrue(strpos($body, self::BEGIN_PGP_MESSAGE) !== false);
        $this->assertStringStartsWith('This is an OpenPGP/MIME encrypted message (RFC 4880 and 3156)', $body);
        $this->assertEquals('multipart/encrypted', $message->getContentType());

        // Extract ciphertext from message
        $startPos = strpos($body, self::BEGIN_PGP_MESSAGE);
        $ciphertext = substr(
            $body,
            $startPos,
            strpos($body, self::END_PGP_MESSAGE) - $startPos + strlen(self::END_PGP_MESSAGE)
        );

        return $this->gnupg->decrypt($ciphertext);
    }

    private function createTestMessage(): Swift_Message
    {
        $swiftMessage = new Swift_Message('Some example subject');
        $swiftMessage->setFrom('sender@example.org');

        $swiftMessage->setTo('test@example.org');
        $swiftMessage->setCc('test2@example.org');

        $swiftMessage->setBody('Hello World');

        return $swiftMessage;
    }

    private function createMultipartTestMessage(): Swift_Message
    {
        $swiftMessage = $this->createTestMessage();
        $swiftMessage->addPart('Some HTML message', 'text/html');

        return $swiftMessage;
    }
}
