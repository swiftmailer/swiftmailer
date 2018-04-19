<?php

require_once __DIR__.'/AbstractStreamBufferAcceptanceTest.php';

class Swift_Transport_StreamBuffer_ProcessAcceptanceTest extends Swift_Transport_StreamBuffer_AbstractStreamBufferAcceptanceTest
{
    protected function setUp()
    {
        if (!defined('SWIFT_SENDMAIL_PATH')) {
            $this->markTestSkipped(
                'Cannot run test without a path to sendmail (define '.
                'SWIFT_SENDMAIL_PATH in tests/acceptance.conf.php if you wish to run this test)'
             );
        }

        parent::setUp();
    }

    protected function initializeBuffer()
    {
        $this->buffer->initialize([
            'type' => Swift_Transport_IoBuffer::TYPE_PROCESS,
            'command' => SWIFT_SENDMAIL_PATH.' -bs',
        ]);
    }
}
