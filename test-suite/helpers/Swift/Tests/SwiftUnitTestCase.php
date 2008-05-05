<?php

require_once 'Swift/Tests/IdenticalBinaryExpectation.php';

/**
 * A base test case with some custom expectations.
 * @package Swift
 * @subpackage Tests
 * @author Chris Corbyn
 */
class Swift_Tests_SwiftUnitTestCase extends UnitTestCase
{

  /** An instance of the Yay_Mockery class */
  private $_mockery;
  
  /**
   * Decorates SimpleTest's implementation to auto-validate mock objects.
   */
  public function before($method)
  {
    $this->_mockery()->assertIsSatisfied();
    $this->_mockery = null;
    return parent::before($method); 
  }
  
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

  // -- Protected methods
  
  /**
   * Returns a singleton-per-test method for Yay_Mockery.
   * @return Yay_Mockery
   */
  protected function _mockery()
  {
    if (!isset($this->_mockery))
    {
      $this->_mockery = new Yay_Mockery();
    }
    return $this->_mockery;
  }
  
}
