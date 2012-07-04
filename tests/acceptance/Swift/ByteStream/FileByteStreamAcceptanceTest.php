<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/InputByteStream.php';
require_once 'Swift/ByteStream/FileByteStream.php';
require_once 'Swift/StreamFilters/StringReplacementFilter.php';

class Swift_ByteStream_FileByteStreamAcceptanceTest
    extends Swift_Tests_SwiftUnitTestCase
{
    private $_tmpDir;
    private $_testFile;

    public function skip()
    {
        $this->skipUnless(
            SWIFT_TMP_DIR, 'Cannot run test without a writable directory to use (' .
            'define SWIFT_TMP_DIR in tests/config.php if you wish to run this test)'
            );
    }

    public function setUp()
    {
        $this->_tmpDir = SWIFT_TMP_DIR;
        $this->_testFile = $this->_tmpDir . '/swift-test-file' . __CLASS__;
        file_put_contents($this->_testFile, 'abcdefghijklm');
    }

    public function tearDown()
    {
        unlink($this->_testFile);
    }

    public function testFileDataCanBeRead()
    {
        $file = $this->_createFileStream($this->_testFile);
        $str = '';
        while (false !== $bytes = $file->read(8192)) {
            $str .= $bytes;
        }
        $this->assertEqual('abcdefghijklm', $str);
    }

    public function testFileDataCanBeReadSequentially()
    {
        $file = $this->_createFileStream($this->_testFile);
        $this->assertEqual('abcde', $file->read(5));
        $this->assertEqual('fghijklm', $file->read(8));
        $this->assertFalse($file->read(1));
    }

    public function testFilenameIsReturned()
    {
        $file = $this->_createFileStream($this->_testFile);
        $this->assertEqual($this->_testFile, $file->getPath());
    }

    public function testFileCanBeWrittenTo()
    {
        $file = $this->_createFileStream(
            $this->_testFile, true
            );
        $file->write('foobar');
        $this->assertEqual('foobar', $file->read(8192));
    }

    public function testReadingFromThenWritingToFile()
    {
        $file = $this->_createFileStream(
            $this->_testFile, true
            );
        $file->write('foobar');
        $this->assertEqual('foobar', $file->read(8192));
        $file->write('zipbutton');
        $this->assertEqual('zipbutton', $file->read(8192));
    }

    public function testWritingToFileWithCanonicalization()
    {
        $file = $this->_createFileStream(
            $this->_testFile, true
            );
        $file->addFilter($this->_createFilter(array("\r\n", "\r"), "\n"), 'allToLF');
        $file->write("foo\r\nbar\r");
        $file->write("\nzip\r\ntest\r");
        $file->flushBuffers();
        $this->assertEqual("foo\nbar\nzip\ntest\n", file_get_contents($this->_testFile));
    }

    public function testBindingOtherStreamsMirrorsWriteOperations()
    {
        $file = $this->_createFileStream(
            $this->_testFile, true
            );
        $is1 = $this->_createMockInputStream();
        $is2 = $this->_createMockInputStream();

        $this->_checking(Expectations::create()
            -> one($is1)->write('x')
            -> one($is2)->write('x')
            -> one($is1)->write('y')
            -> one($is2)->write('y')
        );

        $file->bind($is1);
        $file->bind($is2);

        $file->write('x');
        $file->write('y');
    }

    public function testBindingOtherStreamsMirrorsFlushOperations()
    {
        $file = $this->_createFileStream(
            $this->_testFile, true
            );
        $is1 = $this->_createMockInputStream();
        $is2 = $this->_createMockInputStream();

        $this->_checking(Expectations::create()
            -> one($is1)->flushBuffers()
            -> one($is2)->flushBuffers()
        );

        $file->bind($is1);
        $file->bind($is2);

        $file->flushBuffers();
    }

    public function testUnbindingStreamPreventsFurtherWrites()
    {
        $file = $this->_createFileStream(
            $this->_testFile, true
            );
        $is1 = $this->_createMockInputStream();
        $is2 = $this->_createMockInputStream();

        $this->_checking(Expectations::create()
            -> one($is1)->write('x')
            -> one($is2)->write('x')
            -> one($is1)->write('y')
        );

        $file->bind($is1);
        $file->bind($is2);

        $file->write('x');

        $file->unbind($is2);

        $file->write('y');
    }

    // -- Creation methods

    private function _createFilter($search, $replace)
    {
        return new Swift_StreamFilters_StringReplacementFilter($search, $replace);
    }

    private function _createMockInputStream()
    {
        return $this->_mock('Swift_InputByteStream');
    }

    private function _createFileStream($file, $writable = false)
    {
        return new Swift_ByteStream_FileByteStream($file, $writable);
    }
}
