<?php

use Mockery as m;

class Swift_Bug419Test extends \PHPUnit_Framework_TestCase
{
    public function emailProvider()
    {
        return array(
            array('ек@example.com', true),
            array('example@и.foo', true),
            array('müller@gmail.com', true),
            array('example@.@gmail.com', false),
        );
    }

    /**
     * @dataProvider emailProvider
     */
    public function testUnicodeEmailPartsAreValid($email, $expected)
    {
        try {
            $message = new Swift_Message();
            $message->setTo($email);
            $actual = true;
        } catch (\Swift_RfcComplianceException $e) {
            $actual = false;
        }

        $this->assertEquals($expected, $actual);
    }
}
