<?php

require_once 'Swift/Tests/IdenticalBinaryExpectation.php';

/**
 * A base test case with some custom expectations.
 * @package Swift
 * @subpackage Tests
 * @author Chris Corbyn
 */
abstract class Swift_Tests_SwiftUnitTestCase extends UnitTestCase
{

  /**
   * Assert two binary strings are an exact match.
   * @param string $a
   * @param string $b
   * @param string $s formatted message
   */
  public function assertIdenticalBinary($a, $b, $s = '%s')
  {
    return $this->assert(new Swift_Tests_IdenticalBinaryExpectation($a), $b, $s);
  }
  
}
