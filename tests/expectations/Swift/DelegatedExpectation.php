<?php

require_once SWEETY_SIMPLETEST_PATH . '/expectation.php';

/**
 * Runs a callback method to determine if an expectation passes or fails.
 * @package Swift
 * @subpackage Expectation
 * @author Chris Corbyn
 */
class Swift_DelegatedExpectation extends SimpleExpectation
{
  
  /**
   * Contains the callback function to run.
   * @var callback
   */
  private $_callback;
  
  /**
   * Create a new DelegatedExpectation with the given callback.
   * @param callback $callback
   * @param string $message, optional
   */
  public function __construct($callback, $message = '%s')
  {
    parent::__construct($message);
    $this->_callback = $callback;
  }
  
  /**
   * Delegates to the callback method.
   * @param mixed $compare
   * @return boolean
   */
  public function test($compare)
  {
    return call_user_func($this->_callback, $compare);
  }
  
  /**
   * Get the expectation message.
   * @param mixed $compare
   * @return string
   */
  public function testMessage($compare)
  {
    if (is_array($this->_callback) && count($this->_callback) == 2)
    {
      $delegate = (
        is_object($this->_callback[0]) ?
        get_class($this->_callback[0]) :
        $this->_callback[0]
        ) .
        '::' . $this->_callback[1];
    }
    else
    {
      $delegate = (string) $callback;
    }
    
    if ($this->test($compare))
    {
      return 'Delegated expectation [' . $delegate . ']';
    }
    else
    {
      return 'Delegated expectation [' . $delegate . '] fails';
    }
  }
  
}
