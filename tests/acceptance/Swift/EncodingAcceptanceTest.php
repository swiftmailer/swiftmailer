<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'swift_required.php';

class Swift_EncodingAcceptanceTest extends Swift_Tests_SwiftUnitTestCase
{
    public function testGet7BitEncodingReturns7BitEncoder()
    {
        $encoder = Swift_Encoding::get7BitEncoding();
        $this->assertEqual('7bit', $encoder->getName());
    }

    public function testGet8BitEncodingReturns8BitEncoder()
    {
        $encoder = Swift_Encoding::get8BitEncoding();
        $this->assertEqual('8bit', $encoder->getName());
    }

    public function testGetQpEncodingReturnsQpEncoder()
    {
        $encoder = Swift_Encoding::getQpEncoding();
        $this->assertEqual('quoted-printable', $encoder->getName());
    }

    public function testGetBase64EncodingReturnsBase64Encoder()
    {
        $encoder = Swift_Encoding::getBase64Encoding();
        $this->assertEqual('base64', $encoder->getName());
    }
}
