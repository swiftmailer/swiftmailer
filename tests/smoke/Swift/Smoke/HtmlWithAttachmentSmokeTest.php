<?php

require_once 'Swift/Tests/SwiftSmokeTestCase.php';

class Swift_Smoke_HtmlWithAttachmentSmokeTest
    extends Swift_Tests_SwiftSmokeTestCase
{
    public function setUp()
    {
        $this->_attFile = dirname(__FILE__) . '/../../../_samples/files/textfile.zip';
    }

    public function testAttachmentSending()
    {
        $mailer = $this->_getMailer();
        $message = Swift_Message::newInstance('[Swift Mailer] HtmlWithAttachmentSmokeTest')
            ->setFrom(array(SWIFT_SMOKE_EMAIL_ADDRESS => 'Swift Mailer'))
            ->setTo(SWIFT_SMOKE_EMAIL_ADDRESS)
            ->attach(Swift_Attachment::fromPath($this->_attFile))
            ->setBody('<p>This HTML-formatted message should contain an attached ZIP file (named "textfile.zip").' . PHP_EOL .
                'When unzipped, the archive should produce a text file which reads:</p>' . PHP_EOL .
                '<p><q>This is part of a Swift Mailer v4 smoke test.</q></p>', 'text/html'
            )
            ;
        $this->assertEqual(1, $mailer->send($message),
            '%s: The smoke test should send a single message'
            );
        $this->_visualCheck('http://swiftmailer.org/smoke/4.0.0/attachment.jpg');
    }
}
