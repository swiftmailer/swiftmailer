<?php

class Swift_IdenticalBinaryExpectation extends SimpleExpectation
{
  
  private $_left;
  
  public function __construct($left, $message = '%s')
  {
    $this->SimpleExpectation($message);
    $this->_left = $left;
  }
  
  public function asHexString($binary)
  {
    $hex = '';
    
    $bytes = unpack('H*', $binary);
    
    foreach ($bytes as &$byte)
    {
      $byte = strtoupper($byte);
    }
    
    return implode('', $bytes);
  }
  
  public function test($right)
  {
    $aHex = $this->asHexString($this->_left);
    $bHex = $this->asHexString($right);
    
    return $aHex === $bHex;
  }
  
  public function testMessage($right)
  {
    if ($this->test($right))
    {
      return 'Identical binary expectation [' . $this->asHexString($right) . ']';
    }
    else
    {
      return 'Identical binary expectation fails ' .
        $this->_dumper->describeDifference(
          $this->asHexString($this->_left),
          $this->asHexString($right)
          );
    }
  }
  
}


abstract class Swift_AbstractSwiftUnitTestCase extends UnitTestCase
{

  /**
   * Assert two binary strings are an exact match.
   * @param string $a
   * @param string $b
   * @param string $s formatted message
   */
  public function assertIdenticalBinary($a, $b, $s = '%s')
  {
    return $this->assert(new Swift_IdenticalBinaryExpectation($a), $b, $s);
  }
  
}
