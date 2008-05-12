<?php

require_once 'Swift/Attachment.php';
require_once 'Swift/Mime/AttachmentAcceptanceTest.php';

class Swift_AttachmentAcceptanceTest
  extends Swift_Mime_AttachmentAcceptanceTest
{
  
  protected function _createAttachment()
  {
    return new Swift_Attachment();
  }
  
}
