<?php

/*
 Hit (success/fail) Reporter for the Reporter plugin in Swift Mailer.
 
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
 */

//@require 'Swift/Plugins/Reporter.php';
//@require 'Swift/Mime/Message.php';

/**
 * A reporter which "collects" failures for the Reporter plugin.
 * @package Swift
 * @subpackage Plugins
 * @author Chris Corbyn
 */
class Swift_Plugins_Reporters_HitReporter implements Swift_Plugins_Reporter
{
  
  /**
   * The list of failures.
   * @var array
   * @access private
   */
  private $_failures = array();
  private $_failures_cache = array();
  
  /**
   * Notifies this ReportNotifier that $address failed or succeeded.
   * @param Swift_Mime_Message $message
   * @param string $address
   * @param int $result from {@link RESULT_PASS, RESULT_FAIL}
   */
  public function notify(Swift_Mime_Message $message, $address, $result)
  {
    if (self::RESULT_FAIL == $result && !isset($this->_failures_cache[$address]))
    {
      $this->_failures[] = $address;
      $this->_failures_cache[$address] = true;
    }
  }
  
  /**
   * Get an array of addresses for which delivery failed.
   * @return array
   */
  public function getFailedRecipients()
  {
    return $this->_failures;
  }
  
  /**
   * Clear the buffer (empty the list).
   */
  public function clear()
  {
    $this->_failures = $this->_failures_cache = array();
  }
  
}
