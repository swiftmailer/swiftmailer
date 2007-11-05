<?php

// $Id: remote-control_test.php,v 1.2 2007/06/06 16:58:53 pp11 Exp $

require_once(dirname(__FILE__) . '/../../autorun.php');
require_once(dirname(__FILE__) . '/remote-control.php');

class TestOfSimpleSeleniumRemoteControl extends UnitTestCase {
	function testSesssionIdShouldBePreserved() {
		$remote_control = new SimpleSeleniumRemoteControl("tester", "http://simpletest.org/");
		$this->assertEqual($remote_control->sessionIdParser('OK,123456789123456789'), '123456789123456789');
	}
	
	function testIsUpReturnsFalseWhenDirectedToLocalhostDown() {
		$remote_control = new SimpleSeleniumRemoteControl("tester", "http://simpletest.org/", "localhost-down");;
		$this->assertFalse($remote_control->isUp());
	}

	function testIsUpReturnsTrueWhenDirectedToLocalhostOnPort80() {
		$remote_control = new SimpleSeleniumRemoteControl("tester", "http://simpletest.org/", "localhost", "80");
		$this->assertTrue($remote_control->isUp());
	}

    function testIsUpReturnsTrue() {
        $remote_control = new SimpleSeleniumRemoteControl("*custom opera -nosession", "http://simpletest.org/");
        $this->assertTrue($remote_control->isUp());
    }

    function testOfCommandCreation() {
        $remote_control = new SimpleSeleniumRemoteControl("tester", "http://simpletest.org/");
        $this->assertEqual($remote_control->buildUrlCmd("test"), 'http://localhost:4444/selenium-server/driver/?cmd=test');
        $this->assertEqual($remote_control->buildUrlCmd("test", array("next")), 'http://localhost:4444/selenium-server/driver/?cmd=test&1=next');
        $this->assertEqual($remote_control->buildUrlCmd("test", array("ŽtŽ")), 'http://localhost:4444/selenium-server/driver/?cmd=test&1=%C3%A9t%C3%A9');
        $this->assertEqual($remote_control->buildUrlCmd("test", array("next", "then")), 'http://localhost:4444/selenium-server/driver/?cmd=test&1=next&2=then');
    }
}