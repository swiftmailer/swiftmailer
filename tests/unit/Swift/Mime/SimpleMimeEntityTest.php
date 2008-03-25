<?php

require_once 'Swift/Mime/AbstractMimeEntityTest.php';
require_once 'Swift/Mime/SimpleMimeEntity.php';

class Swift_Mime_SimpleMimeEntityTest extends Swift_Mime_AbstractMimeEntityTest
{
  
  // -- Private helpers
  
  protected function _createEntity($headers, $encoder, $cache)
  {
    return new Swift_Mime_SimpleMimeEntity($headers, $encoder, $cache);
  }
  
}
