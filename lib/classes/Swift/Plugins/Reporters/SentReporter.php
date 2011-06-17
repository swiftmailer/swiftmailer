<?php

/**
 * A reporter which "collects" failures and successes for the Reporter plugin.
 * @package Swift
 * @subpackage Plugins
 * @author Chris Corbyn
 * @author Tarjei Huse 
 */
class Swift_Plugins_Reporters_SentReporter implements Swift_Plugins_Reporter
{
  
  /**
   * The list of failures.
   * @var array
   * @access private
   */
  private $_failures = array();

  private $_successes = array();
  
  /**
   * Notifies this ReportNotifier that $address failed or succeeded.
   * @param Swift_Mime_Message $message
   * @param string $address
   * @param int $result from {@link RESULT_PASS, RESULT_FAIL}
   */
  public function notify(Swift_Mime_Message $message, $address, $result)
  {
    $res = new StdClass;
    $res->address = $address;
    $res->message = $message;
    if (self::RESULT_FAIL == $result)
    {
      $this->_failures[] = $res;
    } else {
      $this->_successes[] = $res;
    }
  }
  /*
   * total nr of messages sent
   **/
  public function getSendCount() {
    return count($this->_failures) + count($this->_successes);
  }
  
  /**
   * Get an array of addresses for which delivery failed.
   * @return array
   */
  public function getFailures()
  {
    return $this->_failures;
  }

  public function getSuccesses() {
    return $this->_successes;
  }

  
  /**
   * Clear the buffer (empty the list).
   */
  public function clear()
  {
    $this->_failures = $this->_successes = array();
  }
  
}


