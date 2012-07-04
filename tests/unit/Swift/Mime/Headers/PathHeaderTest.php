<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Mime/Headers/PathHeader.php';
require_once 'Swift/Mime/Grammar.php';

class Swift_Mime_Headers_PathHeaderTest extends Swift_Tests_SwiftUnitTestCase
{
    public function testTypeIsPathHeader()
    {
        $header = $this->_getHeader('Return-Path');
        $this->assertEqual(Swift_Mime_Header::TYPE_PATH, $header->getFieldType());
    }

    public function testSingleAddressCanBeSetAndFetched()
    {
        $header = $this->_getHeader('Return-Path');
        $header->setAddress('chris@swiftmailer.org');
        $this->assertEqual('chris@swiftmailer.org', $header->getAddress());
    }

    public function testAddressMustComplyWithRfc2822()
    {
        try {
            $header = $this->_getHeader('Return-Path');
            $header->setAddress('chr is@swiftmailer.org');
            $this->fail('Address must be valid according to RFC 2822 addr-spec grammar.');
        } catch (Exception $e) {
            $this->pass();
        }
    }

    public function testValueIsAngleAddrWithValidAddress()
    {
        /* -- RFC 2822, 3.6.7.
            return          =       "Return-Path:" path CRLF

            path            =       ([CFWS] "<" ([CFWS] / addr-spec) ">" [CFWS]) /
                                                            obs-path
     */

        $header = $this->_getHeader('Return-Path');
        $header->setAddress('chris@swiftmailer.org');
        $this->assertEqual('<chris@swiftmailer.org>', $header->getFieldBody());
    }

    public function testValueIsEmptyAngleBracketsIfEmptyAddressSet()
    {
        $header = $this->_getHeader('Return-Path');
        $header->setAddress('');
        $this->assertEqual('<>', $header->getFieldBody());
    }

    public function testSetBodyModel()
    {
        $header = $this->_getHeader('Return-Path');
        $header->setFieldBodyModel('foo@bar.tld');
        $this->assertEqual('foo@bar.tld', $header->getAddress());
    }

    public function testGetBodyModel()
    {
        $header = $this->_getHeader('Return-Path');
        $header->setAddress('foo@bar.tld');
        $this->assertEqual('foo@bar.tld', $header->getFieldBodyModel());
    }

    public function testToString()
    {
        $header = $this->_getHeader('Return-Path');
        $header->setAddress('chris@swiftmailer.org');
        $this->assertEqual('Return-Path: <chris@swiftmailer.org>' . "\r\n",
            $header->toString()
            );
    }

    // -- Private methods

    private function _getHeader($name)
    {
        return new Swift_Mime_Headers_PathHeader($name, new Swift_Mime_Grammar());
    }
}
