<?php

/*
 Array Recipient iterator in Swift Mailer.
 
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

//@require 'Swift/Mailer/RecipientIterator.php';

/**
 * Wraps a standard PHP array in an interator.
 * @package Swift
 * @subpackage Mailer
 * @author Chris Corbyn
 */
class Swift_Mailer_ArrayRecipientIterator
  implements Swift_Mailer_RecipientIterator
{
  
  /**
   * The list of recipients.
   * @var array
   * @access private
   */
  private $_recipients = array();
  
  /**
   * Create a new ArrayRecipientIterator from $recipients.
   * @param array $recipients
   */
  public function __construct(array $recipients)
  {
    $this->_recipients = $recipients;
  }
  
  /**
   * Returns true only if there are more recipients to send to.
   * @return boolean
   */
  public function hasNext()
  {
    return !empty($this->_recipients);
  }
  
  /**
   * Returns an array where the keys are the addresses of recipients and the
   * values are the names.
   * e.g. ('foo@bar' => 'Foo') or ('foo@bar' => NULL)
   * @return array
   */
  public function nextRecipient()
  {
    return array_splice($this->_recipients, 0, 1);
  }
  
}
