<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Plugins/Loggers/ArrayLogger.php';

class Swift_Plugins_Loggers_ArrayLoggerTest
    extends Swift_Tests_SwiftUnitTestCase
{
    public function testAddingSingleEntryDumpsSingleLine()
    {
        $logger = new Swift_Plugins_Loggers_ArrayLogger();
        $logger->add(">> Foo\r\n");
        $this->assertEqual(">> Foo\r\n", $logger->dump());
    }

    public function testAddingMultipleEntriesDumpsMultipleLines()
    {
        $logger = new Swift_Plugins_Loggers_ArrayLogger();
        $logger->add(">> FOO\r\n");
        $logger->add("<< 502 That makes no sense\r\n");
        $logger->add(">> RSET\r\n");
        $logger->add("<< 250 OK\r\n");

        $this->assertEqual(
            ">> FOO\r\n" . PHP_EOL .
            "<< 502 That makes no sense\r\n" . PHP_EOL .
            ">> RSET\r\n" . PHP_EOL .
            "<< 250 OK\r\n",
            $logger->dump()
            );
    }

    public function testLogCanBeCleared()
    {
        $logger = new Swift_Plugins_Loggers_ArrayLogger();
        $logger->add(">> FOO\r\n");
        $logger->add("<< 502 That makes no sense\r\n");
        $logger->add(">> RSET\r\n");
        $logger->add("<< 250 OK\r\n");

        $this->assertEqual(
            ">> FOO\r\n" . PHP_EOL .
            "<< 502 That makes no sense\r\n" . PHP_EOL .
            ">> RSET\r\n" . PHP_EOL .
            "<< 250 OK\r\n",
            $logger->dump()
            );

        $logger->clear();

        $this->assertEqual('', $logger->dump());
    }

    public function testLengthCanBeTruncated()
    {
        $logger = new Swift_Plugins_Loggers_ArrayLogger(2);
        $logger->add(">> FOO\r\n");
        $logger->add("<< 502 That makes no sense\r\n");
        $logger->add(">> RSET\r\n");
        $logger->add("<< 250 OK\r\n");

        $this->assertEqual(
            ">> RSET\r\n" . PHP_EOL .
            "<< 250 OK\r\n",
            $logger->dump(),
            '%s: Log should be truncated to last 2 entries'
            );
    }

}
