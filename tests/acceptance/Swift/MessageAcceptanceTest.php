<?php

require_once 'swift_required.php';
require_once 'Swift/Mime/SimpleMessageAcceptanceTest.php';

class Swift_MessageAcceptanceTest
  extends Swift_Mime_SimpleMessageAcceptanceTest
{
  
  protected function _createMessage()
  {
    return Swift_Message::newInstance();
  }
  
}
