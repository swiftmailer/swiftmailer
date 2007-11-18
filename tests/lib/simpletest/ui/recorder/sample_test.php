<?php
// $Id: sample_test.php,v 1.1 2007/04/29 14:33:31 pp11 Exp $
    
require_once(dirname(__FILE__) . '/../../autorun.php');

class SampleTestForRecorder extends UnitTestCase {
    function testTrueIsTrue() {
        $this->assertTrue(true);
    }

    function testFalseIsTrue() {
        $this->assertFalse(true);
    }
}
?>