<?php

/*
 Mime field change API for Swift Mailer.
 
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
 * Interface for all observers of a Mime entity waiting for field changes.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
interface Swift_Mime_FieldChangeObserver
{
  
  /**
   * Notify this observer that a field has changed to $value.
   * "Field" is a loose term and refers to class fields rather than
   * header fields.  $field will always be in lowercase and will be alpha.
   * only.
   * An example could be fieldChanged('contenttype', 'text/plain');
   * This of course reflects a change in the body of the Content-Type header.
   * Another example could be fieldChanged('charset', 'us-ascii');
   * This reflects a change in the charset parameter of the Content-Type header.
   * @param string $field in lowercase ALPHA
   * @param mixed $value
   */
  public function fieldChanged($field, $value);
  
}
