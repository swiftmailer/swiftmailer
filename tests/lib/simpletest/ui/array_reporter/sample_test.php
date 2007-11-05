<?php
    // $Id: sample_test.php,v 1.1 2006/11/29 13:17:01 pp11 Exp $
    
    class SampleTestForArrayReporter extends UnitTestCase {
        
        function testTrueIsTrue() {
            $this->assertTrue(true);
        }

        function testFalseIsTrue() {
            $this->assertFalse(true);
        }

    }
?>