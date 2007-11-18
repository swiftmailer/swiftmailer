<?php
/**
 *	base include file for SimpleTest
 *	@package	SimpleTest
 *	@subpackage	UnitTester
 *	@version	$Id: array_reporter.php,v 1.1 2006/11/29 13:17:01 pp11 Exp $
 */

/**
 *	include other SimpleTest class files
 */
require_once(dirname(__FILE__) . '/../scorer.php');

/**
 *    Array-based test reporter. Returns an array
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
class ArrayReporter extends SimpleReporter {
    var $_results;
  
	function ArrayReporter() {
        $this->SimpleReporter();
        $this->_results = array();
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
        $this->_results[] = $result;
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
        $this->_results[] = $result;
	}
	
	function getStatus() {
        return $this->_results;
	}
}

?>