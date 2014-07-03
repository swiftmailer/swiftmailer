<?php

require_once 'swift_required.php';
require_once __DIR__.'/Mime/AttachmentAcceptanceTest.php';

class Swift_AttachmentAcceptanceTest extends Swift_Mime_AttachmentAcceptanceTest
{
    protected function _createAttachment()
    {
        return new Swift_Attachment();
    }
}
