<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * OpenPGP Message Signer used to apply OpenPGP based Signature/Encryption to a message.
 *
 * @author Markus Fasselt <markus.fasselt@gmail.com>
 * @author Artem Zhuravlev <infzanoza@gmail.com>
 */
class Swift_Signers_OpenPGPSigner implements Swift_Signers_BodySigner
{
    /*private*/ const PURPOSE_ENCRYPT = 'encrypt';
    /*private*/ const PURPOSE_SIGN = 'sign';

    /**
     * @var gnupg
     */
    private $gnupg;

    /**
     * The signing hash algorithm. 'MD5', SHA1, or SHA256. SHA256 (the default) is highly recommended
     * unless you need to deal with an old client that doesn't support it. SHA1 and MD5 are
     * currently considered cryptographically weak.
     *
     * This is apparently not supported by the PHP GnuPG module
     *
     * @var string
     */
    private $micalg = 'sha256';

    /**
     * @var bool
     */
    private $encrypt = false;

    /**
     * @var bool
     */
    private $sign = false;

    public function __construct(string $gnupgHome = null, bool $sign = false, bool $encrypt = false)
    {
        if (!class_exists(gnupg::class)) {
            throw new RuntimeException('Please install gnupg extension to use this signer.');
        }

        if ($gnupgHome !== null) {
            if (!file_exists($gnupgHome)) {
                throw new Swift_SwiftException('GnuPG home path does not exist');
            }

            putenv("GNUPGHOME=" . escapeshellcmd($gnupgHome));
        }

        $this->sign = $sign;
        $this->encrypt = $encrypt;

        $this->gnupg = new gnupg();
        $this->gnupg->seterrormode(gnupg::ERROR_EXCEPTION);
        $this->gnupg->setsignmode(gnupg::SIG_MODE_DETACH); // Let gnupg returned a detached signature
        $this->gnupg->setarmor(1); // Enable ascii output
    }

    /**
     * Change the Swift_Signed_Message to apply the singing.
     *
     * @param Swift_Message $message
     *
     * @return self
     * @throws \Swift_IoException
     * @throws Swift_RfcComplianceException
     */
    public function signMessage(Swift_Message $message)
    {
        if (!$this->sign && !$this->encrypt) {
            return $this;
        }

        $plaintextBody = $this->extractPlainTextBody($message);

        if ($this->sign) {
            $plaintextBody = $this->pgpSignMessage($message, $plaintextBody);

            // To use the signed body for encryption, we need to prepend the Content-Type header into the plaintext
            $plaintextBody = sprintf("%s\r\n%s", $message->getHeaders()->get('Content-Type')->toString(), $plaintextBody);
        }

        if ($this->encrypt) {
            $this->encryptMessage($message, $plaintextBody);
        }

        return $this;
    }

    /**
     * Return the list of header a signer might tamper.
     *
     * @return array
     */
    public function getAlteredHeaders()
    {
        return [
            'Content-Type',
        ];
    }

    public function reset()
    {
        $this->gnupg->clearencryptkeys();
        $this->gnupg->clearsignkeys();

        return $this;
    }

    /**
     * Signs the message and returns the new message body (which can be used for a subsequent encryption).
     *
     * @param Swift_Message $message
     * @param string        $plaintextBody
     *
     * @return string
     * @throws Swift_IoException
     */
    private function pgpSignMessage(Swift_Message $message, string $plaintextBody): string
    {
        $fromEmailAddress = array_keys($message->getFrom())[0];
        $keyFingerprint = $this->findKey($fromEmailAddress, self::PURPOSE_SIGN);
        $signedBody = $this->cleanUpLineEndings($plaintextBody);
        $signature = $this->signBody($signedBody, $keyFingerprint);

        //Swift mailer is automatically changing content type and this is the hack to prevent it
        $body = <<<EOT
This is an OpenPGP/MIME signed message (RFC 4880 and 3156)

--{$message->getBoundary()}
$signedBody
--{$message->getBoundary()}
Content-Type: application/pgp-signature; name="signature.asc"
Content-Description: OpenPGP digital signature
Content-Disposition: attachment; filename="signature.asc"

$signature

--{$message->getBoundary()}--
EOT;

        $body = $this->cleanUpLineEndings($body);
        $this->writeNewBody($body, $message);

        // Set new Content-Type
        $type = $message->getHeaders()->get('Content-Type');
        $type->setValue('multipart/signed');
        $type->setParameters([
            'micalg' => sprintf('pgp-%s', strtolower($this->micalg)),
            'protocol' => 'application/pgp-signature',
            'boundary' => $message->getBoundary(),
        ]);

        $messageHeaders = $message->getHeaders();
        $messageHeaders->removeAll('Content-Transfer-Encoding');

        return $body;
    }

    /**
     * Encrypts the given message.
     *
     * @param Swift_Message $message
     * @param string        $plaintextBody
     *
     * @throws Swift_IoException
     */
    private function encryptMessage(Swift_Message $message, string $plaintextBody)
    {
        $keyFingerprints = $this->extractEncryptionKeysFromRecipients($message);
        $encryptedBody = $this->encryptPlaintext($plaintextBody, $keyFingerprints);

        // Message body according to RFC 3156
        $encryptedBody = <<<EOT
This is an OpenPGP/MIME encrypted message (RFC 4880 and 3156)

--{$message->getBoundary()}
Content-Type: application/pgp-encrypted
Content-Description: PGP/MIME version identification

Version: 1

--{$message->getBoundary()}
Content-Type: application/octet-stream; name="encrypted.asc"
Content-Description: OpenPGP encrypted message
Content-Disposition: inline; filename="encrypted.asc"

$encryptedBody

--{$message->getBoundary()}--
EOT;

        $this->writeNewBody($encryptedBody, $message);

        // Set new Content-Type
        $contentType = $message->getHeaders()->get('Content-Type');
        $contentType->setValue('multipart/encrypted');
        $contentType->setParameters([
            'protocol' => 'application/pgp-encrypted',
            'boundary' => $message->getBoundary(),
        ]);
    }

    /**
     * Extracts the whole body part of the message as plain text (to encrypt it later on).
     *
     * @param Swift_Message $message
     *
     * @return string
     * @throws Swift_RfcComplianceException
     */
    public function extractPlainTextBody(Swift_Message $message): string
    {
        $mimeEntity = new Swift_Message('', $message->getBody(), $message->getContentType(), $message->getCharset());
        $mimeEntity->setChildren($message->getChildren());

        // Remove default headers (but keep the Content-Type)
        $messageHeaders = $mimeEntity->getHeaders();
        $messageHeaders->removeAll('Message-ID');
        $messageHeaders->removeAll('Date');
        $messageHeaders->removeAll('Subject');
        $messageHeaders->removeAll('MIME-Version');
        $messageHeaders->removeAll('To');
        $messageHeaders->removeAll('From');

        // Keep boundary of original message and reset boundary on actual message
        $mimeEntity->setBoundary($message->getBoundary());
        $message->setBoundary('_=_swift_'.time().'_'.bin2hex(random_bytes(16)).'_=_');

        return $mimeEntity->toString();
    }

    /**
     * Encrypts the given plaintext mail body with the keys for the given GPG key fingerprints.
     *
     * @param string $plaintext
     * @param array  $keyFingerprints
     *
     * @return string
     * @throws Swift_IoException
     */
    private function encryptPlaintext(string $plaintext, array $keyFingerprints): string
    {
        foreach ($keyFingerprints as $fingerprint) {
            $this->gnupg->addencryptkey($fingerprint);
        }

        $encrypted = $this->gnupg->encrypt($plaintext);
        if (!$encrypted) {
            throw new Swift_IoException('Unable to encrypt message');
        }

        return $encrypted;
    }

    /**
     * Signs the given plaintext and returns the signature.
     *
     * @param string $body
     * @param string $keyFingerprint
     *
     * @return string
     * @throws Swift_IoException
     */
    private function signBody(string $body, string $keyFingerprint): string
    {
        $this->gnupg->addsignkey($keyFingerprint);

        $signature = $this->gnupg->sign($body);
        if (!$signature) {
            throw new Swift_IoException('Unable to sign message');
        }

        return $signature;
    }

    /**
     * This method looks for encryption keys for all recipients and returns their fingerprints.
     *
     * @param Swift_Message $message
     *
     * @return string[]
     * @throws Swift_IoException
     */
    private function extractEncryptionKeysFromRecipients(Swift_Message $message): array
    {
        $keys = [];
        $recipients = array_merge((array) $message->getTo(), (array) $message->getCc(), (array) $message->getBcc());
        $emailAddresses = array_keys($recipients);
        foreach ($emailAddresses as $emailAddress) {
            $keys[] = $this->findKey($emailAddress, self::PURPOSE_ENCRYPT);
        }

        return $keys;
    }

    /**
     * Find an encryption key for the given email address.
     *
     * @param string $emailAddress
     * @param string $purpose either 'encrypt' or 'sign'
     *
     * @return mixed
     * @throws Swift_IoException
     */
    private function findKey(string $emailAddress, string $purpose)
    {
        $keys = $this->gnupg->keyinfo($emailAddress);
        $keyFingerprints = [];

        foreach ($keys as $key) {
            if ($this->isValidKey($key, $purpose)) {
                foreach ($key['subkeys'] as $subKey) {
                    if ($this->isValidKey($subKey, $purpose)) {
                        $keyFingerprints[] = $subKey['fingerprint'];
                    }
                }
            }
        }

        if (count($keyFingerprints) === 1) {
            return $keyFingerprints[0];
        }

        if (count($keyFingerprints) > 1) {
            throw new Swift_IoException(
                sprintf('Found more than one active key for %s', $emailAddress)
            );
        }

        throw new Swift_IoException(
            sprintf('Unable to find an active key to %s for %s, try importing keys first', $purpose, $emailAddress)
        );
    }

    /**
     * Checks whether the given key is still valid.
     *
     * @param array  $key
     * @param string $purpose
     *
     * @return bool|mixed
     */
    private function isValidKey(array $key, string $purpose)
    {
        if ($key['disabled'] || $key['expired'] || $key['revoked']) {
            return false;
        }

        if ($purpose === self::PURPOSE_SIGN) {
            return $key['can_sign'];
        }

        if ($purpose == self::PURPOSE_ENCRYPT) {
            return $key['can_encrypt'];
        }

        throw new \InvalidArgumentException('Invalid purpose: ' . $purpose);
    }

    public function cleanUpLineEndings(string $body)
    {
        // Fix line endings (should only be CRLF)
        $body = str_replace("\r\n", "\n", $body);
        $body = str_replace("\n", "\r\n", $body);

        // Remove excessive trailing new lines (see RFC 3156 section 3 and 5.4)
        $body = rtrim($body) . "\r\n";

        return $body;
    }

    /**
     * Writes the new body into the swift message.
     *
     * @param string        $newBody
     * @param Swift_Message $message
     */
    protected function writeNewBody(string $newBody, Swift_Message $message)
    {
        // Remove all existing transfer encoding headers
        $message->getHeaders()->removeAll('Content-Transfer-Encoding');

        // We use the null content encoder, since the body is already encoded
        $message->setEncoder(new Swift_Mime_ContentEncoder_NullContentEncoder(''));

        // Copy over the body from the stream using the content type dictated
        // by the stream content
        $message->setChildren([]);
        $message->setBody($newBody);
    }
}
