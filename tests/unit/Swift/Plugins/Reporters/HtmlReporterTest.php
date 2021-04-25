<?php

class Swift_Plugins_Reporters_HtmlReporterTest extends \PHPUnit\Framework\TestCase
{
    private $html;
    private $message;

    protected function setUp()
    {
        $this->html = new Swift_Plugins_Reporters_HtmlReporter();
        $this->message = $this->getMockBuilder('Swift_Mime_SimpleMessage')->disableOriginalConstructor()->getMock();
    }

    public function testReportingPass()
    {
        ob_start();
        $this->html->notify($this->message, 'foo@bar.tld',
            Swift_Plugins_Reporter::RESULT_PASS
            );
        $html = ob_get_clean();

        $this->assertMatchesRegularExpression('~ok|pass~i', $html, '%s: Reporter should indicate pass');
        $this->assertMatchesRegularExpression('~foo@bar\.tld~', $html, '%s: Reporter should show address');
    }

    public function testReportingFail()
    {
        ob_start();
        $this->html->notify($this->message, 'zip@button',
            Swift_Plugins_Reporter::RESULT_FAIL
            );
        $html = ob_get_clean();

        $this->assertMatchesRegularExpression('~fail~i', $html, '%s: Reporter should indicate fail');
        $this->assertMatchesRegularExpression('~zip@button~', $html, '%s: Reporter should show address');
    }

    public function testMultipleReports()
    {
        ob_start();
        $this->html->notify($this->message, 'foo@bar.tld',
            Swift_Plugins_Reporter::RESULT_PASS
            );
        $this->html->notify($this->message, 'zip@button',
            Swift_Plugins_Reporter::RESULT_FAIL
            );
        $html = ob_get_clean();

        $this->assertMatchesRegularExpression('~ok|pass~i', $html, '%s: Reporter should indicate pass');
        $this->assertMatchesRegularExpression('~foo@bar\.tld~', $html, '%s: Reporter should show address');
        $this->assertMatchesRegularExpression('~fail~i', $html, '%s: Reporter should indicate fail');
        $this->assertMatchesRegularExpression('~zip@button~', $html, '%s: Reporter should show address');
    }
}
