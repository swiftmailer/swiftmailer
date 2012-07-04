<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Mime/ContentEncoder/PlainContentEncoder.php';
require_once 'Swift/InputByteStream.php';
require_once 'Swift/OutputByteStream.php';

class Swift_StreamCollector implements Yay_Action {
    public $content = '';
    public function &invoke(Yay_Invocation $inv) {
        $args = $inv->getArguments();
        $this->content .= current($args);
    }
    public function describeTo(Yay_Description $description) {
        $description->appendText(' gathers input;');
    }
}

class Swift_Mime_ContentEncoder_PlainContentEncoderTest
    extends Swift_Tests_SwiftUnitTestCase
{
    public function testNameCanBeSpecifiedInConstructor()
    {
        $encoder = $this->_getEncoder('7bit');
        $this->assertEqual('7bit', $encoder->getName());

        $encoder = $this->_getEncoder('8bit');
        $this->assertEqual('8bit', $encoder->getName());
    }

    public function testNoOctetsAreModifiedInString()
    {
        $encoder = $this->_getEncoder('7bit');
        foreach (range(0x00, 0xFF) as $octet) {
            $byte = pack('C', $octet);
            $this->assertIdenticalBinary($byte, $encoder->encodeString($byte));
        }
    }

    public function testNoOctetsAreModifiedInByteStream()
    {
        $encoder = $this->_getEncoder('7bit');
        foreach (range(0x00, 0xFF) as $octet) {
            $byte = pack('C', $octet);

            $os = $this->_createOutputByteStream();
            $is = $this->_createInputByteStream();
            $collection = new Swift_StreamCollector();

            $this->_checking(Expectations::create()
                -> allowing($is)->write(any(), optional()) -> will($collection)
                -> ignoring($is)

                -> one($os)->read(optional()) -> returns($byte)
                -> allowing($os)->read(optional()) -> returns(false)

                -> ignoring($os)
                );

            $encoder->encodeByteStream($os, $is);
            $this->assertIdenticalBinary($byte, $collection->content);
        }
    }

    public function testLineLengthCanBeSpecified()
    {
        $encoder = $this->_getEncoder('7bit');

        $chars = array();
        for ($i = 0; $i < 50; $i++) {
            $chars[] = 'a';
        }
        $input = implode(' ', $chars); //99 chars long

        $this->assertEqual(
            'a a a a a a a a a a a a a a a a a a a a a a a a a ' . "\r\n" . //50 *
            'a a a a a a a a a a a a a a a a a a a a a a a a a',            //99
            $encoder->encodeString($input, 0, 50),
            '%s: Lines should be wrapped at 50 chars'
            );
    }

    public function testLineLengthCanBeSpecifiedInByteStream()
    {
        $encoder = $this->_getEncoder('7bit');

        $os = $this->_createOutputByteStream();
        $is = $this->_createInputByteStream();
        $collection = new Swift_StreamCollector();

        $this->_checking(Expectations::create()
            -> allowing($is)->write(any(), optional()) -> will($collection)
            -> ignoring($is)
            );

        for ($i = 0; $i < 50; $i++) {
            $this->_checking(Expectations::create()
                -> one($os)->read(optional()) -> returns('a ')
                );
        }

        $this->_checking(Expectations::create()
            -> allowing($os)->read(optional()) -> returns(false)
            );

        $encoder->encodeByteStream($os, $is, 0, 50);
        $this->assertEqual(
            str_repeat('a ', 25) . "\r\n" . str_repeat('a ', 25),
            $collection->content
            );
    }

    public function testencodeStringGeneratesCorrectCrlf()
    {
        $encoder = $this->_getEncoder('7bit', true);
        $this->assertEqual("a\r\nb", $encoder->encodeString("a\rb"),
            '%s: Line endings should be standardized'
            );
        $this->assertEqual("a\r\nb", $encoder->encodeString("a\nb"),
            '%s: Line endings should be standardized'
            );
        $this->assertEqual("a\r\n\r\nb", $encoder->encodeString("a\n\rb"),
            '%s: Line endings should be standardized'
            );
        $this->assertEqual("a\r\n\r\nb", $encoder->encodeString("a\r\rb"),
            '%s: Line endings should be standardized'
            );
        $this->assertEqual("a\r\n\r\nb", $encoder->encodeString("a\n\nb"),
            '%s: Line endings should be standardized'
            );
    }

    public function testCanonicEncodeByteStreamGeneratesCorrectCrlf_1()
    {
        $encoder = $this->_getEncoder('7bit', true);

        $os = $this->_createOutputByteStream();
        $is = $this->_createInputByteStream();
        $collection = new Swift_StreamCollector();

        $this->_checking(Expectations::create()
            -> allowing($is)->write(any(), optional()) -> will($collection)
            -> ignoring($is)

            -> one($os)->read(optional()) -> returns('a')
            -> one($os)->read(optional()) -> returns("\r")
            -> one($os)->read(optional()) -> returns('b')
            -> allowing($os)->read(optional()) -> returns(false)

            -> ignoring($os)
            );

        $encoder->encodeByteStream($os, $is);
        $this->assertEqual("a\r\nb", $collection->content);
    }

    public function testCanonicEncodeByteStreamGeneratesCorrectCrlf_2()
    {
        $encoder = $this->_getEncoder('7bit', true);

        $os = $this->_createOutputByteStream();
        $is = $this->_createInputByteStream();
        $collection = new Swift_StreamCollector();

        $this->_checking(Expectations::create()
            -> allowing($is)->write(any(), optional()) -> will($collection)
            -> ignoring($is)

            -> one($os)->read(optional()) -> returns('a')
            -> one($os)->read(optional()) -> returns("\n")
            -> one($os)->read(optional()) -> returns('b')
            -> allowing($os)->read(optional()) -> returns(false)

            -> ignoring($os)
            );

        $encoder->encodeByteStream($os, $is);
        $this->assertEqual("a\r\nb", $collection->content);
    }

    public function testCanonicEncodeByteStreamGeneratesCorrectCrlf_3()
    {
        $encoder = $this->_getEncoder('7bit', true);

        $os = $this->_createOutputByteStream();
        $is = $this->_createInputByteStream();
        $collection = new Swift_StreamCollector();

        $this->_checking(Expectations::create()
            -> allowing($is)->write(any(), optional()) -> will($collection)
            -> ignoring($is)

            -> one($os)->read(optional()) -> returns('a')
            -> one($os)->read(optional()) -> returns("\n\r")
            -> one($os)->read(optional()) -> returns('b')
            -> allowing($os)->read(optional()) -> returns(false)

            -> ignoring($os)
            );

        $encoder->encodeByteStream($os, $is);
        $this->assertEqual("a\r\n\r\nb", $collection->content);
    }

    public function testCanonicEncodeByteStreamGeneratesCorrectCrlf_4()
    {
        $encoder = $this->_getEncoder('7bit', true);

        $os = $this->_createOutputByteStream();
        $is = $this->_createInputByteStream();
        $collection = new Swift_StreamCollector();

        $this->_checking(Expectations::create()
            -> allowing($is)->write(any(), optional()) -> will($collection)
            -> ignoring($is)

            -> one($os)->read(optional()) -> returns('a')
            -> one($os)->read(optional()) -> returns("\n\n")
            -> one($os)->read(optional()) -> returns('b')
            -> allowing($os)->read(optional()) -> returns(false)

            -> ignoring($os)
            );

        $encoder->encodeByteStream($os, $is);
        $this->assertEqual("a\r\n\r\nb", $collection->content);
    }

    public function testCanonicEncodeByteStreamGeneratesCorrectCrlf_5()
    {
        $encoder = $this->_getEncoder('7bit', true);

        $os = $this->_createOutputByteStream();
        $is = $this->_createInputByteStream();
        $collection = new Swift_StreamCollector();

        $this->_checking(Expectations::create()
            -> allowing($is)->write(any(), optional()) -> will($collection)
            -> ignoring($is)

            -> one($os)->read(optional()) -> returns('a')
            -> one($os)->read(optional()) -> returns("\r\r")
            -> one($os)->read(optional()) -> returns('b')
            -> allowing($os)->read(optional()) -> returns(false)

            -> ignoring($os)
            );

        $encoder->encodeByteStream($os, $is);
        $this->assertEqual("a\r\n\r\nb", $collection->content);
    }

    // -- Private helpers

    private function _getEncoder($name, $canonical = false)
    {
        return new Swift_Mime_ContentEncoder_PlainContentEncoder($name, $canonical);
    }

    private function _createOutputByteStream($stub = false)
    {
        return $stub
            ? $this->_stub('Swift_OutputByteStream')
            : $this->_mock('Swift_OutputByteStream')
            ;
    }

    private function _createInputByteStream($stub = false)
    {
        return $stub
            ? $this->_stub('Swift_InputByteStream')
            : $this->_mock('Swift_InputByteStream')
            ;
    }
}
