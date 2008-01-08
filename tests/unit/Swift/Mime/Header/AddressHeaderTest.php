<?php

require_once 'Swift/Mime/Header/AddressHeader.php';
require_once 'Swift/Mime/HeaderAttribute.php';
require_once 'Swift/Mime/HeaderAttributeSet.php';
require_once 'Swift/Mime/HeaderEncoder.php';

Mock::generate('Swift_Mime_HeaderAttribute', 'Swift_Mime_MockHeaderAttribute');
Mock::generate('Swift_Mime_HeaderAttributeSet',
  'Swift_Mime_MockHeaderAttributeSet'
  );
Mock::generate('Swift_Mime_HeaderEncoder', 'Swift_Mime_MockHeaderEncoder');

class Swift_Mime_Header_AddressHeaderTest extends UnitTestCase
{
  
  public function testNothing()
  {
    $this->assertTrue(false, 'Nothing tested');
  }
  
  // -- Private methods
  
  private function _getHeader($name, $value, $encoder = null)
  {
    if (!$encoder)
    {
      $encoder = new Swift_Mime_MockHeaderEncoder();
    }
    return new Swift_Mime_Header_AddressHeader(
      $name, $value, $this->_charset, $encoder
      );
  }
  
}
