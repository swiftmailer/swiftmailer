<?php

require_once 'swift_required.php';
require_once 'Swift/Mime/EmbeddedFileAcceptanceTest.php';

class Swift_EmbeddedFileAcceptanceTest
  extends Swift_Mime_EmbeddedFileAcceptanceTest
{
  
  protected function _createEmbeddedFile()
  {
    return Swift_EmbeddedFile::newInstance();
  }
  
}
