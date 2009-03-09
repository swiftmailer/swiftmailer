<?php

/*
 Invokes the mail() function in Swift Mailer.
 
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

/**
 * This interface intercepts calls to the mail() function.
 * 
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
interface Swift_Transport_MailInvoker
{
  
  /**
   * Send mail via the mail() function.
   * 
   * This method takes the same arguments as PHP mail().
   * 
   * @param string $to
   * @param string $subject
   * @param string $body
   * @param string $headers
   * @param string $extraParams
   * 
   * @return boolean
   */
  public function mail($to, $subject, $body, $headers = null, $extraParams = null);
  
}
