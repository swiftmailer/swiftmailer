<?php

/*
 Reporter plugin in Swift Mailer.
 
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

//@require 'Swift/Events/SendListener.php';
//@require 'Swift/Events/SendEvent.php';
//@require 'Swift/Plugins/Reporter.php';

/**
 * Does real time reporting of pass/fail for each recipient.
 * @package Swift
 * @subpackage Plugins
 * @author Chris Corbyn
 */
class Swift_Plugins_ReporterPlugin
  implements Swift_Events_SendListener
{
  
  /**
   * The reporter backend which takes notifications.
   * @var Swift_Plugin_Reporter
   * @access private
   */
  private $_reporter;
  
  /**
   * Create a new ReporterPlugin using $reporter.
   * @param Swift_Plugins_Reporter $reporter
   */
  public function __construct(Swift_Plugins_Reporter $reporter)
  {
    $this->_reporter = $reporter;
  }
  
  /**
   * Not used.
   */
  public function beforeSendPerformed(Swift_Events_SendEvent $evt)
  {
  }
  
  /**
   * Invoked immediately after the Message is sent.
   * @param Swift_Events_SendEvent $evt
   */
  public function sendPerformed(Swift_Events_SendEvent $evt)
  {
    $message = $evt->getMessage();
    $failures = array_flip($evt->getFailedRecipients());
    foreach ((array) $message->getTo() as $address => $null)
    {
      $this->_reporter->notify(
        $message, $address, (array_key_exists($address, $failures)
        ? Swift_Plugins_Reporter::RESULT_FAIL
        : Swift_Plugins_Reporter::RESULT_PASS)
        );
    }
    foreach ((array) $message->getCc() as $address => $null)
    {
      $this->_reporter->notify(
        $message, $address, (array_key_exists($address, $failures)
        ? Swift_Plugins_Reporter::RESULT_FAIL
        : Swift_Plugins_Reporter::RESULT_PASS)
        );
    }
    foreach ((array) $message->getBcc() as $address => $null)
    {
      $this->_reporter->notify(
        $message, $address, (array_key_exists($address, $failures)
        ? Swift_Plugins_Reporter::RESULT_FAIL
        : Swift_Plugins_Reporter::RESULT_PASS)
        );
    }
  }
  
}
