<?php

require_once 'Swift/Encoder/Base64Encoder.php';

class Swift_Encoder_Base64EncoderTest extends UnitTestCase
{
  
  private $_encoder;
  
  public function setUp()
  {
    $this->_encoder = new Swift_Encoder_Base64Encoder();
  }
  
  public function testNothing()
  {
    $this->assertTrue(false, 'Nothing here');
  }
  
}
