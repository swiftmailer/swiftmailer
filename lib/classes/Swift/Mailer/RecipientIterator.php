<?php

/*
 RecipientIterator interface in Swift Mailer.
 
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
 * Provides an abstract way of specifying recipients for batch sending.
 * @package Swift
 * @subpackage Mailer
 * @author Chris Corbyn
 */
interface Swift_Mailer_RecipientIterator
{
  
  /**
   * Returns true only if there are more recipients to send to.
   * @return boolean
   */
  public function hasNext();
  
  /**
   * Returns an array where the keys are the addresses of recipients and the
   * values are the names.
   * e.g. ('foo@bar' => 'Foo') or ('foo@bar' => NULL)
   * @return array
   */
  public function nextRecipient();
  
}
