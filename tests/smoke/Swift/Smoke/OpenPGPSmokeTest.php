<?php

/**
 * @group smoke
 */
class Swift_Smoke_OpenPGPSmokeTest extends SwiftMailerSmokeTestCase
{
    private $_attFile;

    protected function setUp()
    {
        $this->attFile = __DIR__.'/../../../_samples/files/textfile.zip';
    }

    /**
     * @dataProvider openPgpDataProvider
     */
    public function testOpenPGPMessage(string $label, bool $sign, bool $encrypt)
    {
        $mailer = $this->getMailer();
        $message = (new Swift_Message('[Swift Mailer] OpenPGPSmokeTest ' . $label))
            ->setFrom([SWIFT_SMOKE_EMAIL_ADDRESS => 'Swift Mailer'])
            ->setTo(SWIFT_SMOKE_EMAIL_ADDRESS)
            ->setBody('This text message should be encrypted with OpenPGP', 'text/plain')
            ->attachSigner(new Swift_Signers_OpenPGPSigner(null, $sign, $encrypt));

        $this->assertEquals(1, $mailer->send($message), '%s: The smoke test should send a single message');
    }

    /**
     * @dataProvider openPgpDataProvider
     */
    public function testOpenPGPMultipartMessage(string $label, bool $sign, bool $encrypt)
    {
        $mailer = $this->getMailer();
        $message = (new Swift_Message('[Swift Mailer] OpenPGPSmokeTest ' . $label))
            ->setFrom([SWIFT_SMOKE_EMAIL_ADDRESS => 'Swift Mailer'])
            ->setTo(SWIFT_SMOKE_EMAIL_ADDRESS)
            ->setBody('This text message should be encrypted with OpenPGP', 'text/plain')
            ->addPart('This HTML message should be encrypted with OpenPGP', 'text/html')
            ->attach(Swift_Attachment::fromPath($this->attFile))
            ->attachSigner(new Swift_Signers_OpenPGPSigner(null, $sign, $encrypt));

        $this->assertEquals(1, $mailer->send($message), '%s: The smoke test should send a single message');
    }

    public function openPgpDataProvider(): array
    {
        return [
            ['signed', true, false],
            ['encrypted', false, true],
            ['signed and encrypted', true, true],
        ];
    }
}
