<?php

use Mockery as m;

class Swift_Bug534Test extends \PHPUnit_Framework_TestCase
{
    public function testEmbeddedImagesAreEmbedded()
    {
        $message = Swift_Message::newInstance('Subject of mail');
        $message->setFrom(array('from@example.com' => 'From address'));
        $message->setTo(array('to@example.com' => 'To address'));

        $image = Swift_Image::fromPath(__DIR__ . '/swiftmailer.png');
        $cid = $message->embed($image);
        $messageBody = 'Look at <b>this</b> image: <img alt="Embedded Image" width="181" height="68" src="'
            . $cid . '" />';
        $message->setBody($messageBody, 'text/html');

        $that = $this;
        $messageValidation = function ($message) use ($that) {
            /** @var $message \Swift_Mime_Message */

            preg_match("/cid:(.*)\"/", $message->toString(), $imageCIDs);
            $imageCID = $imageCIDs[1];

            preg_match("/Content-ID: <(.*)>/", $message->toString(), $imageContentIDs);
            $imageContentID = $imageContentIDs[1];

            $this->assertEquals($imageCID, $imageContentID, 'cid in body and mime part Content-ID differ');

            return true;
        };

        $transport = m::mock('Swift_Transport');
        $transport->shouldReceive('isStarted')->andReturn(true);

        $failedRecipients = array();
        $transport->shouldReceive('send')
            ->with(m::on($messageValidation), $failedRecipients)
            ->andReturn(1);

        $memorySpool = new Swift_MemorySpool();
        $memorySpool->queueMessage($message);
        $memorySpool->flushQueue($transport, $failedRecipients);
    }
}
