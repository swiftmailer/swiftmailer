<?php

require_once 'Swift/Mime/HeaderEncoder/Base64HeaderEncoder.php';

class Swift_Mime_HeaderEncoder_Base64HeaderEncoderTest extends UnitTestCase
{
    //Most tests are already covered in Base64EncoderTest since this subclass only
    // adds a getName() method

    public function testNameIsB()
    {
        $encoder = new Swift_Mime_HeaderEncoder_Base64HeaderEncoder();
        $this->assertEqual('B', $encoder->getName());
    }
}
