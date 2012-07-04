<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Mime/Headers/DateHeader.php';
require_once 'Swift/Mime/Grammar.php';

class Swift_Mime_Headers_DateHeaderTest
    extends Swift_Tests_SwiftUnitTestCase
{
    /* --
    The following tests refer to RFC 2822, section 3.6.1 and 3.3.
    */

    public function testTypeIsDateHeader()
    {
        $header = $this->_getHeader('Date');
        $this->assertEqual(Swift_Mime_Header::TYPE_DATE, $header->getFieldType());
    }

    public function testGetTimestamp()
    {
        $timestamp = time();
        $header = $this->_getHeader('Date');
        $header->setTimestamp($timestamp);
        $this->assertIdentical($timestamp, $header->getTimestamp());
    }

    public function testTimestampCanBeSetBySetter()
    {
        $timestamp = time();
        $header = $this->_getHeader('Date');
        $header->setTimestamp($timestamp);
        $this->assertIdentical($timestamp, $header->getTimestamp());
    }

    public function testIntegerTimestampIsConvertedToRfc2822Date()
    {
        $timestamp = time();
        $header = $this->_getHeader('Date');
        $header->setTimestamp($timestamp);
        $this->assertEqual(date('r', $timestamp), $header->getFieldBody());
    }

    public function testSetBodyModel()
    {
        $timestamp = time();
        $header = $this->_getHeader('Date');
        $header->setFieldBodyModel($timestamp);
        $this->assertEqual(date('r', $timestamp), $header->getFieldBody());
    }

    public function testGetBodyModel()
    {
        $timestamp = time();
        $header = $this->_getHeader('Date');
        $header->setTimestamp($timestamp);
        $this->assertEqual($timestamp, $header->getFieldBodyModel());
    }

    public function testToString()
    {
        $timestamp = time();
        $header = $this->_getHeader('Date');
        $header->setTimestamp($timestamp);
        $this->assertEqual('Date: ' . date('r', $timestamp) . "\r\n",
            $header->toString()
            );
    }

    // -- Private methods

    private function _getHeader($name)
    {
        return new Swift_Mime_Headers_DateHeader($name, new Swift_Mime_Grammar());
    }
}
