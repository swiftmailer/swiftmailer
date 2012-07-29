<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Plugins/Loggers/EchoLogger.php';

class Swift_Plugins_Loggers_EchoLoggerTest
    extends Swift_Tests_SwiftUnitTestCase
{
    public function testAddingEntryDumpsSingleLineWithoutHtml()
    {
        $logger = new Swift_Plugins_Loggers_EchoLogger(false);
        ob_start();
        $logger->add(">> Foo");
        $data = ob_get_clean();

        $this->assertEqual(">> Foo" . PHP_EOL, $data);
    }

    public function testAddingEntryDumpsEscapedLineWithHtml()
    {
        $logger = new Swift_Plugins_Loggers_EchoLogger(true);
        ob_start();
        $logger->add(">> Foo");
        $data = ob_get_clean();

        $this->assertEqual("&gt;&gt; Foo<br />" . PHP_EOL, $data);
    }

}
