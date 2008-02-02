<?php

require_once 'Swift/AbstractSwiftUnitTestCase.php';
require_once 'Swift/Mime/MimePart.php';
require_once 'Swift/Mime/HeaderSet.php';
require_once 'Swift/Mime/Header.php';

Mock::generate('Swift_Mime_HeaderSet', 'Swift_Mime_MockHeaderSet');

class Swift_Mime_MimePartTest extends Swift_AbstractSwiftUnitTestCase
{
  
  private $_charset = 'utf-8';
  
  public function testHeadersCanBeSetAndFetched()
  {
    $headers = new Swift_Mime_MockHeaderSet();
    $part = $this->_getMimePart('foo', 'text/html');
    $part->setHeaders($headers);
  }
  
  // -- Private helpers
  
  private function _getMimePart($body = null, $type = null)
  {
    return new Swift_Mime_MimePart($body, $type);
  }
  
}
