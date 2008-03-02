<?php

/*
 Reporter interface for the Reporter plugin in Swift Mailer.
 
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

//@require 'Swift/Mime/Message.php';

/**
 * The Reporter plugin sends pass/fail notification to a Reporter.
 * @package Swift
 * @subpackage Plugins
 * @author Chris Corbyn
 */
interface Swift_Plugins_Reporter
{
  
  /** The recipient was accepted for delivery */
  const RESULT_PASS = 0x01;
  
  /** The recipient could not be accepted */
  const RESULT_FAIL = 0x10;
  
  /**
   * Notifies this ReportNotifier that $address failed or succeeded.
   * @param Swift_Mime_Message $message
   * @param string $address
   * @param int $result from {@link RESULT_PASS, RESULT_FAIL}
   */
  public function notify(Swift_Mime_Message $message, $address, $result);
  
}
