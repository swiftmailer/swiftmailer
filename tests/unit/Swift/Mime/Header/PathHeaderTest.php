<?php

require_once 'Swift/Mime/Header/PathHeader.php';
require_once 'Swift/Mime/HeaderAttribute.php';
require_once 'Swift/Mime/HeaderAttributeSet.php';
require_once 'Swift/Mime/HeaderEncoder.php';

Mock::generate('Swift_Mime_HeaderAttribute', 'Swift_Mime_MockHeaderAttribute');
Mock::generate('Swift_Mime_HeaderAttributeSet',
  'Swift_Mime_MockHeaderAttributeSet'
  );
Mock::generate('Swift_Mime_HeaderEncoder', 'Swift_Mime_MockHeaderEncoder');

class Swift_Mime_Header_PathHeaderTest extends UnitTestCase
{
  
  private $_charset = 'utf-8';
  
  //Such as Keywords: header
  
  public function testNothing()
  {
    $this->assertFalse(true, 'Nothing');
  }
  
  // -- Private methods
  
  private function _getHeader($name, $path = null, $encoder = null)
  {
    if (!$encoder)
    {
      $encoder = new Swift_Mime_MockHeaderEncoder();
    }
    return new Swift_Mime_Header_PathHeader(
      $name, $path, $this->_charset, $encoder
      );
  }
  
}
