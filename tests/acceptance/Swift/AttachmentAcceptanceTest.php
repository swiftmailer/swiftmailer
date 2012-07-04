<?php

require_once 'swift_required.php';
require_once 'Swift/Mime/AttachmentAcceptanceTest.php';

class Swift_AttachmentAcceptanceTest
    extends Swift_Mime_AttachmentAcceptanceTest
{
    protected function _createAttachment()
    {
        return Swift_Attachment::newInstance();
    }
}
