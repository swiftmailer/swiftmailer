<?php
/**
 *	base include file for SimpleTest
 *	@package	SimpleTest
 *	@subpackage	UnitTester
 *	@version	$Id: recorder.php,v 1.1 2007/04/29 14:33:31 pp11 Exp $
 */

/**
 *	include other SimpleTest class files
 */
require_once(dirname(__FILE__) . '/../scorer.php');

/**
 *    Array-based test recorder. Returns an array
 *    with timestamp, status, test name and message for each pass and failure.
 *
 *    This code is made available under the same terms as SimpleTest.  It is based
 *    off of code that Rene vd O originally published in patch [ 1594212 ]
 *    on the SimpleTest patches tracker. 
 *
 *    @author Rene vd O (original code)
 *    @author Perrick Penet
 *	  @package SimpleTest
 *	  @subpackage UnitTester
 */
class Recorder extends SimpleReporter {
    var $results;
  
	function Recorder() {
        $this->SimpleReporter();
        $this->results = array();
	}
	
	function paintPass($message) {
        parent::paintPass($message);
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        $test = implode("->", $breadcrumb);
    
        $result["time"] = time();
        $result["status"] = "Passed";
        $result["test"] = $test;
        $result["message"] = $message;
        $this->results[] = $result;
	}
	
	function paintFail($message) {
        parent::paintFail($message);
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        $test = implode("->", $breadcrumb);
    
        $result["time"] = time();
        $result["status"] = "Failed";
        $result["test"] = $test;
        $result["message"] = $message;
        $this->results[] = $result;
	}
}

?>