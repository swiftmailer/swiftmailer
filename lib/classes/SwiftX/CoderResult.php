<?php

/**
 * Describes the result of encoding/decoding a stream of characters.
 * 
 * @package Swift
 * @subpackage Encoder
 * 
 * @author Chris Corbyn
 */
class SwiftX_CoderResult
{
  
  /** Underflow indicator */
  const RESULT_UNDERFLOW = 1;
  
  /** Overflow indicator */
  const RESULT_OVERFLOW = 2;
  
  /** Unmappable indicator */
  const RESULT_UNMAPPABLE = 3;
  
  /** Malformed input indicator */
  const RESULT_MALFORMED = 4;
  
  /**
   * Result object describing an underflow in coding.
   * 
   * @var CoderResult
   */
  public static $UNDERFLOW;
  
  /**
   * Result object describing an overflow in coding.
   * 
   * @var CoderResult
   */
  public static $OVERFLOW;
  
  /** The actual result type */
  private $_result;
  
  /** Length of the malformed input */
  private $_malformedLength;
  
  /**
   * Create a new CoderResult with $result.
   * 
   * @param int $result
   * @param int $length for malformed data
   */
  public function __construct($result, $malformedLength = 0)
  {
    $this->_result = $result;
    $this->_malformedLength = $malformedLength;
  }
  
  /**
   * Check if this result indicates an underflow.
   * 
   * @return boolean
   */
  public function isUnderflow()
  {
    return $this->_result == self::RESULT_UNDERFLOW;
  }
  
  /**
   * Check if this result indicates an overflow.
   * 
   * @return boolean
   */
  public function isOverflow()
  {
    return $this->_result == self::RESULT_OVERFLOW;
  }
  
  /**
   * Check if this result indicates and error (unmappable or malformed).
   * 
   * @return boolean
   */
  public function isError()
  {
    return ($this->_result == self::RESULT_UNMAPPABLE
        || $this->_result == self::RESULT_MALFORMED);
  }
  
  /**
   * Check if this result indicates that an unmappable character was found.
   * 
   * @return boolean
   */
  public function isUnmappable()
  {
    return $this->_result == self::RESULT_UNMAPPABLE;
  }
  
  /**
   * Check if this result indicates that malformed input was found.
   * 
   * @return boolean
   */
  public function isMalformed()
  {
    return $this->_result == self::RESULT_MALFORMED;
  }
  
  /**
   * Get the length of the malformed input.
   * 
   * @return int
   */
  public function getMalformedLength()
  {
    return $this->_malformedLength;
  }
  
}

//Initialize the underflow result (never changes)
SwiftX_CoderResult::$UNDERFLOW = new SwiftX_CoderResult(
  SwiftX_CoderResult::RESULT_UNDERFLOW
);

//Initialize the overflow result (never changes)
SwiftX_CoderResult::$OVERFLOW = new SwiftX_CoderResult(
  SwiftX_CoderResult::RESULT_OVERFLOW
);
