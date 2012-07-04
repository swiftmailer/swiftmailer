<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Plugins/Reporters/HtmlReporter.php';
require_once 'Swift/Mime/Message.php';

class Swift_Plugins_Reporters_HtmlReporterTest
    extends Swift_Tests_SwiftUnitTestCase
{
    private $_html;
    private $_message;

    public function setUp()
    {
        $this->_html = new Swift_Plugins_Reporters_HtmlReporter();
        $this->_message = $this->_mock('Swift_Mime_Message');
    }

    public function testReportingPass()
    {
        ob_start();
        $this->_html->notify($this->_message, 'foo@bar.tld',
            Swift_Plugins_Reporter::RESULT_PASS
            );
        $html = ob_get_clean();

        $this->assertPattern('~ok|pass~i', $html, '%s: Reporter should indicate pass');
        $this->assertPattern('~foo@bar\.tld~', $html, '%s: Reporter should show address');
    }

    public function testReportingFail()
    {
        ob_start();
        $this->_html->notify($this->_message, 'zip@button',
            Swift_Plugins_Reporter::RESULT_FAIL
            );
        $html = ob_get_clean();

        $this->assertPattern('~fail~i', $html, '%s: Reporter should indicate fail');
        $this->assertPattern('~zip@button~', $html, '%s: Reporter should show address');
    }

    public function testMultipleReports()
    {
        ob_start();
        $this->_html->notify($this->_message, 'foo@bar.tld',
            Swift_Plugins_Reporter::RESULT_PASS
            );
        $this->_html->notify($this->_message, 'zip@button',
            Swift_Plugins_Reporter::RESULT_FAIL
            );
        $html = ob_get_clean();

        $this->assertPattern('~ok|pass~i', $html, '%s: Reporter should indicate pass');
        $this->assertPattern('~foo@bar\.tld~', $html, '%s: Reporter should show address');
        $this->assertPattern('~fail~i', $html, '%s: Reporter should indicate fail');
        $this->assertPattern('~zip@button~', $html, '%s: Reporter should show address');
    }
}
