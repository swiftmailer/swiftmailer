<?php

require_once 'Swift/Mime/Header/StructuredHeader.php';
require_once 'Swift/Mime/HeaderAttribute.php';
require_once 'Swift/Mime/HeaderAttributeSet.php';

Mock::generate('Swift_Mime_HeaderAttribute', 'Swift_Mime_MockHeaderAttribute');
Mock::generate('Swift_Mime_HeaderAttributeSet',
  'Swift_Mime_MockHeaderAttributeSet'
  );

class Swift_Mime_Header_StructuredHeaderTest extends UnitTestCase
{

  public function testNothing()
  {
  }
  
}
