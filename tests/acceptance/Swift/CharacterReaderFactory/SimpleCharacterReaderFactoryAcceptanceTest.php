<?php

class Swift_CharacterReaderFactory_SimpleCharacterReaderFactoryAcceptanceTest extends \PHPUnit\Framework\TestCase
{
    private $factory;
    private $prefix = 'Swift_CharacterReader_';

    protected function setUp()
    {
        $this->factory = new Swift_CharacterReaderFactory_SimpleCharacterReaderFactory();
    }

    public function testCreatingUtf8Reader()
    {
        foreach (['utf8', 'utf-8', 'UTF-8', 'UTF8'] as $utf8) {
            $reader = $this->factory->getReaderFor($utf8);
            $this->assertInstanceOf($this->prefix.'Utf8Reader', $reader);
        }
    }

    public function testCreatingIso8859XReaders()
    {
        $charsets = [];
        foreach (range(1, 16) as $number) {
            foreach (['iso', 'iec'] as $body) {
                $charsets[] = $body.'-8859-'.$number;
                $charsets[] = $body.'8859-'.$number;
                $charsets[] = strtoupper($body).'-8859-'.$number;
                $charsets[] = strtoupper($body).'8859-'.$number;
            }
        }

        foreach ($charsets as $charset) {
            $reader = $this->factory->getReaderFor($charset);
            $this->assertInstanceOf($this->prefix.'GenericFixedWidthReader', $reader);
            $this->assertEquals(1, $reader->getInitialByteSize());
        }
    }

    public function testCreatingWindows125XReaders()
    {
        $charsets = [];
        foreach (range(0, 8) as $number) {
            $charsets[] = 'windows-125'.$number;
            $charsets[] = 'windows125'.$number;
            $charsets[] = 'WINDOWS-125'.$number;
            $charsets[] = 'WINDOWS125'.$number;
        }

        foreach ($charsets as $charset) {
            $reader = $this->factory->getReaderFor($charset);
            $this->assertInstanceOf($this->prefix.'GenericFixedWidthReader', $reader);
            $this->assertEquals(1, $reader->getInitialByteSize());
        }
    }

    public function testCreatingCodePageReaders()
    {
        $charsets = [];
        foreach (range(0, 8) as $number) {
            $charsets[] = 'cp-125'.$number;
            $charsets[] = 'cp125'.$number;
            $charsets[] = 'CP-125'.$number;
            $charsets[] = 'CP125'.$number;
        }

        foreach ([437, 737, 850, 855, 857, 858, 860,
            861, 863, 865, 866, 869, ] as $number) {
            $charsets[] = 'cp-'.$number;
            $charsets[] = 'cp'.$number;
            $charsets[] = 'CP-'.$number;
            $charsets[] = 'CP'.$number;
        }

        foreach ($charsets as $charset) {
            $reader = $this->factory->getReaderFor($charset);
            $this->assertInstanceOf($this->prefix.'GenericFixedWidthReader', $reader);
            $this->assertEquals(1, $reader->getInitialByteSize());
        }
    }

    public function testCreatingAnsiReader()
    {
        foreach (['ansi', 'ANSI'] as $ansi) {
            $reader = $this->factory->getReaderFor($ansi);
            $this->assertInstanceOf($this->prefix.'GenericFixedWidthReader', $reader);
            $this->assertEquals(1, $reader->getInitialByteSize());
        }
    }

    public function testCreatingMacintoshReader()
    {
        foreach (['macintosh', 'MACINTOSH'] as $mac) {
            $reader = $this->factory->getReaderFor($mac);
            $this->assertInstanceOf($this->prefix.'GenericFixedWidthReader', $reader);
            $this->assertEquals(1, $reader->getInitialByteSize());
        }
    }

    public function testCreatingKOIReaders()
    {
        $charsets = [];
        foreach (['7', '8-r', '8-u', '8u', '8r'] as $end) {
            $charsets[] = 'koi-'.$end;
            $charsets[] = 'koi'.$end;
            $charsets[] = 'KOI-'.$end;
            $charsets[] = 'KOI'.$end;
        }

        foreach ($charsets as $charset) {
            $reader = $this->factory->getReaderFor($charset);
            $this->assertInstanceOf($this->prefix.'GenericFixedWidthReader', $reader);
            $this->assertEquals(1, $reader->getInitialByteSize());
        }
    }

    public function testCreatingIsciiReaders()
    {
        foreach (['iscii', 'ISCII', 'viscii', 'VISCII'] as $charset) {
            $reader = $this->factory->getReaderFor($charset);
            $this->assertInstanceOf($this->prefix.'GenericFixedWidthReader', $reader);
            $this->assertEquals(1, $reader->getInitialByteSize());
        }
    }

    public function testCreatingMIKReader()
    {
        foreach (['mik', 'MIK'] as $charset) {
            $reader = $this->factory->getReaderFor($charset);
            $this->assertInstanceOf($this->prefix.'GenericFixedWidthReader', $reader);
            $this->assertEquals(1, $reader->getInitialByteSize());
        }
    }

    public function testCreatingCorkReader()
    {
        foreach (['cork', 'CORK', 't1', 'T1'] as $charset) {
            $reader = $this->factory->getReaderFor($charset);
            $this->assertInstanceOf($this->prefix.'GenericFixedWidthReader', $reader);
            $this->assertEquals(1, $reader->getInitialByteSize());
        }
    }

    public function testCreatingUcs2Reader()
    {
        foreach (['ucs-2', 'UCS-2', 'ucs2', 'UCS2'] as $charset) {
            $reader = $this->factory->getReaderFor($charset);
            $this->assertInstanceOf($this->prefix.'GenericFixedWidthReader', $reader);
            $this->assertEquals(2, $reader->getInitialByteSize());
        }
    }

    public function testCreatingUtf16Reader()
    {
        foreach (['utf-16', 'UTF-16', 'utf16', 'UTF16'] as $charset) {
            $reader = $this->factory->getReaderFor($charset);
            $this->assertInstanceOf($this->prefix.'GenericFixedWidthReader', $reader);
            $this->assertEquals(2, $reader->getInitialByteSize());
        }
    }

    public function testCreatingUcs4Reader()
    {
        foreach (['ucs-4', 'UCS-4', 'ucs4', 'UCS4'] as $charset) {
            $reader = $this->factory->getReaderFor($charset);
            $this->assertInstanceOf($this->prefix.'GenericFixedWidthReader', $reader);
            $this->assertEquals(4, $reader->getInitialByteSize());
        }
    }

    public function testCreatingUtf32Reader()
    {
        foreach (['utf-32', 'UTF-32', 'utf32', 'UTF32'] as $charset) {
            $reader = $this->factory->getReaderFor($charset);
            $this->assertInstanceOf($this->prefix.'GenericFixedWidthReader', $reader);
            $this->assertEquals(4, $reader->getInitialByteSize());
        }
    }
}
