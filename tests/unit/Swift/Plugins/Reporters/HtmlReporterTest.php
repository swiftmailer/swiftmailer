<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Plugins/Reporters/HtmlReporter.php';
require_once 'Swift/Mime/Message.php';

Mock::generate('Swift_Mime_Message', 'Swift_Mime_MockMessage');

class Swift_Plugins_Reporters_HtmlReporterTest
  extends Swift_Tests_SwiftUnitTestCase
{

  private $_html;
  
  public function setUp()
  {
    $this->_html = new Swift_Plugins_Reporters_HtmlReporter();
  }
  
  public function testReportingPass()
  {
    ob_start();
    $this->_html->notify(new Swift_Mime_MockMessage(), 'foo@bar.tld',
      Swift_Plugins_Reporter::RESULT_PASS
      );
    $html = ob_get_clean();
    
    $this->assertPattern('~ok|pass~i', $html, '%s: Reporter should indicate pass');
    $this->assertPattern('~foo@bar\.tld~', $html, '%s: Reporter should show address');
  }
  
  public function testReportingFail()
  {
    ob_start();
    $this->_html->notify(new Swift_Mime_MockMessage(), 'zip@button',
      Swift_Plugins_Reporter::RESULT_FAIL
      );
    $html = ob_get_clean();
    
    $this->assertPattern('~fail~i', $html, '%s: Reporter should indicate fail');
    $this->assertPattern('~zip@button~', $html, '%s: Reporter should show address');
  }
  
  public function testMultipleReports()
  {
    ob_start();
    $this->_html->notify(new Swift_Mime_MockMessage(), 'foo@bar.tld',
      Swift_Plugins_Reporter::RESULT_PASS
      );
    $this->_html->notify(new Swift_Mime_MockMessage(), 'zip@button',
      Swift_Plugins_Reporter::RESULT_FAIL
      );
    $html = ob_get_clean();
    
    $this->assertPattern('~ok|pass~i', $html, '%s: Reporter should indicate pass');
    $this->assertPattern('~foo@bar\.tld~', $html, '%s: Reporter should show address');
    $this->assertPattern('~fail~i', $html, '%s: Reporter should indicate fail');
    $this->assertPattern('~zip@button~', $html, '%s: Reporter should show address');
  }
  
}
