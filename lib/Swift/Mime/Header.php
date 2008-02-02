<?php

/*
 Header Interface in Swift Mailer.
 
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
 * A MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
interface Swift_Mime_Header
{
  
  /**
   * Get the name of this header (e.g. charset).
   * The name is an identifier and as such will be immutable.
   * @return string
   */
  public function getFieldName();
  
  /**
   * Get the field body, prepared for folding into a final header value.
   * @return string
   */
  public function getFieldBody();
  
  /**
   * Get this Header rendered as a compliant string.
   * @return string
   */
  public function toString();
  
}
