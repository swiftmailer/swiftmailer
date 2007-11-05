<?php

// $Id: dom_tester_doc_test.php,v 1.1 2007/05/31 21:29:21 pp11 Exp $

require_once(dirname(__FILE__) . '/../../autorun.php');
require_once(dirname(__FILE__) . '/../dom_tester.php');

SimpleTest :: prefer(new TextReporter());

class TestOfLiveCssSelectors extends DomTestCase {
    function setUp() {
        $this->addHeader('User-Agent: SimpleTest ' . SimpleTest::getVersion());
    }
    
    function testGet() {
        $url = 'http://localhost/~perrick/simpletest/docs/simpletest.org/';
        $this->assertTrue($this->get($url));
        $this->assertEqual($this->getUrl(), $url);
        $this->assertElementsBySelector('h2', array('Screenshots', 'Documentation', 'Contributing'));
		$this->assertElementsBySelector('a[href="http://simpletest.org/api/"]', array('the complete API', 'documented API'));
   		$this->assertElementsBySelector('div#content > p > strong', array('SimpleTest PHP unit tester'));
    }
}

?>