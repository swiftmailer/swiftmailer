<?php
    // $Id: test.php,v 1.1 2006/11/29 13:17:01 pp11 Exp $

    require_once(dirname(__FILE__) . '/../array_reporter.php');
    require_once(dirname(__FILE__) . '/../../unit_tester.php');
    require_once(dirname(__FILE__) . '/../../reporter.php');

    class TestOfArrayReporter extends UnitTestCase {
        
        function testContentOfArrayReporterWithOnePassAndOneFailure() {
            $test = &new GroupTest();
            $test->addTestFile(dirname(__FILE__) . '/sample_test.php');
            $result = $test->run(new ArrayReporter());
            $this->assertEqual(count($result), 2);
            
            $this->assertEqual(count($result[0]), 4);
            $this->assertPattern("/".substr(time(), 9)."/", $result[0]['time']);
            $this->assertEqual($result[0]['status'], "Passed");
            $this->assertPattern("/test\.php->SampleTestForArrayReporter->testTrueIsTrue/", $result[0]['test']);
            $this->assertPattern("/ at \[.*array_reporter\/sample_test\.php line 7\]/", $result[0]['message']);

            $this->assertEqual(count($result[1]), 4);
            $this->assertPattern("/".substr(time(), 9)."/", $result[1]['time']);
            $this->assertEqual($result[1]['status'], "Failed");
            $this->assertPattern("/test\.php->SampleTestForArrayReporter->testFalseIsTrue/", $result[1]['test']);
            $this->assertPattern("/Expected false, got \[Boolean: true\] at \[.*array_reporter\/sample_test\.php line 11\]/", $result[1]['message']);
        }
    }
    
    $test = &new GroupTest('Tests for the "array reporter"');
	$test->addTestClass('TestOfArrayReporter');
    if (SimpleReporter::inCli()) {
        $result = $test->run(new TextReporter());
        return ($result ? 0 : 1);
    }
    $test->run(new HtmlReporter());
    
?>