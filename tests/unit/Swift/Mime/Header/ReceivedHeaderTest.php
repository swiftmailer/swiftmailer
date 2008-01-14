<?php

require_once 'Swift/Mime/Header/ReceivedHeader.php';
require_once 'Swift/Mime/HeaderAttribute.php';
require_once 'Swift/Mime/HeaderAttributeSet.php';
require_once 'Swift/Mime/HeaderEncoder.php';

Mock::generate('Swift_Mime_HeaderAttribute', 'Swift_Mime_MockHeaderAttribute');
Mock::generate('Swift_Mime_HeaderAttributeSet',
  'Swift_Mime_MockHeaderAttributeSet'
  );
Mock::generate('Swift_Mime_HeaderEncoder', 'Swift_Mime_MockHeaderEncoder');

class Swift_Mime_Header_ReceivedHeaderTest extends UnitTestCase
{
  
  private $_charset = 'utf-8';
  
  public function testNothing()
  {
    $this->assertFalse(true, 'Nothing');
  }
  
  // -- Private methods
  
  private function _getHeader($name, $info = array(), $encoder = null)
  {
    if (!$encoder)
    {
      $encoder = new Swift_Mime_MockHeaderEncoder();
    }
    return new Swift_Mime_Header_ReceivedHeader(
      $name, $info, $this->_charset, $encoder
      );
  }
  
}
