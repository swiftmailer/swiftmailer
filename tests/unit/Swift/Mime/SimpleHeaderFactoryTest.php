<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Mime/SimpleHeaderFactory.php';
require_once 'Swift/Mime/HeaderEncoder.php';
require_once 'Swift/Encoder.php';
require_once 'Swift/Mime/Grammar.php';

class Swift_Mime_SimpleHeaderFactoryTest extends Swift_Tests_SwiftUnitTestCase
{
    private $_factory;

    public function setUp()
    {
        $this->_factory = $this->_createFactory();
    }

    public function testMailboxHeaderIsCorrectType()
    {
        $header = $this->_factory->createMailboxHeader('X-Foo');
        $this->assertIsA($header, 'Swift_Mime_Headers_MailboxHeader');
    }

    public function testMailboxHeaderHasCorrectName()
    {
        $header = $this->_factory->createMailboxHeader('X-Foo');
        $this->assertEqual('X-Foo', $header->getFieldName());
    }

    public function testMailboxHeaderHasCorrectModel()
    {
        $header = $this->_factory->createMailboxHeader('X-Foo',
            array('foo@bar'=>'FooBar')
            );
        $this->assertEqual(array('foo@bar'=>'FooBar'), $header->getFieldBodyModel());
    }

    public function testDateHeaderHasCorrectType()
    {
        $header = $this->_factory->createDateHeader('X-Date');
        $this->assertIsA($header, 'Swift_Mime_Headers_DateHeader');
    }

    public function testDateHeaderHasCorrectName()
    {
        $header = $this->_factory->createDateHeader('X-Date');
        $this->assertEqual('X-Date', $header->getFieldName());
    }

    public function testDateHeaderHasCorrectModel()
    {
        $header = $this->_factory->createDateHeader('X-Date', 123);
        $this->assertEqual(123, $header->getFieldBodyModel());
    }

    public function testTextHeaderHasCorrectType()
    {
        $header = $this->_factory->createTextHeader('X-Foo');
        $this->assertIsA($header, 'Swift_Mime_Headers_UnstructuredHeader');
    }

    public function testTextHeaderHasCorrectName()
    {
        $header = $this->_factory->createTextHeader('X-Foo');
        $this->assertEqual('X-Foo', $header->getFieldName());
    }

    public function testTextHeaderHasCorrectModel()
    {
        $header = $this->_factory->createTextHeader('X-Foo', 'bar');
        $this->assertEqual('bar', $header->getFieldBodyModel());
    }

    public function testParameterizedHeaderHasCorrectType()
    {
        $header = $this->_factory->createParameterizedHeader('X-Foo');
        $this->assertIsA($header, 'Swift_Mime_Headers_ParameterizedHeader');
    }

    public function testParameterizedHeaderHasCorrectName()
    {
        $header = $this->_factory->createParameterizedHeader('X-Foo');
        $this->assertEqual('X-Foo', $header->getFieldName());
    }

    public function testParameterizedHeaderHasCorrectModel()
    {
        $header = $this->_factory->createParameterizedHeader('X-Foo', 'bar');
        $this->assertEqual('bar', $header->getFieldBodyModel());
    }

    public function testParameterizedHeaderHasCorrectParams()
    {
        $header = $this->_factory->createParameterizedHeader('X-Foo', 'bar',
            array('zip' => 'button')
            );
        $this->assertEqual(array('zip'=>'button'), $header->getParameters());
    }

    public function testIdHeaderHasCorrectType()
    {
        $header = $this->_factory->createIdHeader('X-ID');
        $this->assertIsA($header, 'Swift_Mime_Headers_IdentificationHeader');
    }

    public function testIdHeaderHasCorrectName()
    {
        $header = $this->_factory->createIdHeader('X-ID');
        $this->assertEqual('X-ID', $header->getFieldName());
    }

    public function testIdHeaderHasCorrectModel()
    {
        $header = $this->_factory->createIdHeader('X-ID', 'xyz@abc');
        $this->assertEqual(array('xyz@abc'), $header->getFieldBodyModel());
    }

    public function testPathHeaderHasCorrectType()
    {
        $header = $this->_factory->createPathHeader('X-Path');
        $this->assertIsA($header, 'Swift_Mime_Headers_PathHeader');
    }

    public function testPathHeaderHasCorrectName()
    {
        $header = $this->_factory->createPathHeader('X-Path');
        $this->assertEqual('X-Path', $header->getFieldName());
    }

    public function testPathHeaderHasCorrectModel()
    {
        $header = $this->_factory->createPathHeader('X-Path', 'foo@bar');
        $this->assertEqual('foo@bar', $header->getFieldBodyModel());
    }

    public function testCharsetChangeNotificationNotifiesEncoders()
    {
        $encoder = $this->_createHeaderEncoder(false);
        $paramEncoder = $this->_createParamEncoder(false);

        $factory = $this->_createFactory($encoder, $paramEncoder);

        $this->_checking(Expectations::create()
            -> one($encoder)->charsetChanged('utf-8')
            -> one($paramEncoder)->charsetChanged('utf-8')
            -> ignoring($encoder)
            -> ignoring($paramEncoder)
            );

        $factory->charsetChanged('utf-8');
    }

    // -- Creation methods

    private function _createFactory($encoder = null, $paramEncoder = null)
    {
        return new Swift_Mime_SimpleHeaderFactory(
            $encoder
                ? $encoder : $this->_createHeaderEncoder(),
            $paramEncoder
                ? $paramEncoder : $this->_createParamEncoder(),
            new Swift_Mime_Grammar()
            );
    }

    private function _createHeaderEncoder($stub = true)
    {
        return $stub
            ? $this->_stub('Swift_Mime_HeaderEncoder')
            : $this->_mock('Swift_Mime_HeaderEncoder');
    }

    private function _createParamEncoder($stub = true)
    {
        return $stub
            ? $this->_stub('Swift_Encoder')
            : $this->_mock('Swift_Encoder');
    }
}
