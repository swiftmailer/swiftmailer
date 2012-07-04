<?php

require_once 'Swift/Tests/SwiftSmokeTestCase.php';

class Swift_Smoke_AttachmentSmokeTest extends Swift_Tests_SwiftSmokeTestCase
{
    public function setUp()
    {
        $this->_attFile = dirname(__FILE__) . '/../../../_samples/files/textfile.zip';
    }

    public function testAttachmentSending()
    {
        $mailer = $this->_getMailer();
        $message = Swift_Message::newInstance()
            ->setSubject('[Swift Mailer] AttachmentSmokeTest')
            ->setFrom(array(SWIFT_SMOKE_EMAIL_ADDRESS => 'Swift Mailer'))
            ->setTo(SWIFT_SMOKE_EMAIL_ADDRESS)
            ->setBody('This message should contain an attached ZIP file (named "textfile.zip").' . PHP_EOL .
                'When unzipped, the archive should produce a text file which reads:' . PHP_EOL .
                '"This is part of a Swift Mailer v4 smoke test."'
                )
            ->attach(Swift_Attachment::fromPath($this->_attFile))
            ;
        $this->assertEqual(1, $mailer->send($message),
            '%s: The smoke test should send a single message'
            );
        $this->_visualCheck('http://swiftmailer.org/smoke/4.0.0/attachment.jpg');
    }
}
