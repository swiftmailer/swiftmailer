<?php

use Mockery as m;

class Swift_Bug543Test extends \PHPUnit_Framework_TestCase
{
    public function testEmbeddedImagesAreEmbedded()
    {
        $failedRecipients = 'value';
        $message = new Swift_Message();
        $messageBody = 'Look at <b>this</b> image: <img alt="Embedded Image" width="181" height="68" src="data:image/png;base64,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%3D" />';
        $message->setBody($messageBody);

        $that = $this;
        $messageValidation = function ($m) use ($that, $messageBody) {
            /** @var $m \Swift_Mime_Message */

            // TODO This is where we could do some checks on the mail.

            return true;
        };

        $transport = m::mock('Swift_Transport');
        $transport->shouldReceive('isStarted')->andReturn(true);
        $transport->shouldReceive('send')
            ->with(m::on($messageValidation), $failedRecipients)
            ->andReturn(1);

        $memorySpool = new Swift_MemorySpool();
        $memorySpool->queueMessage($message);
        $memorySpool->flushQueue($transport, $failedRecipients);
    }
}
