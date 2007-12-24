<?php

/*
 Header Attribute Interface in Swift Mailer.
 
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

require_once dirname(__FILE__) . '/../Encoder.php';


/**
 * An attribute to a MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
interface Swift_Mime_HeaderAttribute
{
  
  /**
   * Set the encoder used for encoding the attribute.
   * @param Swift_Encoder $encoder
   */
  public function setEncoder(Swift_Encoder $encoder);
  
  /**
   * Get the name of this attribute (e.g. charset).
   * @return string
   */
  public function getName();
  
  /**
   * Get the (unencoded) value of this header attribute.
   * @return string
   */
  public function getValue();
  
  /**
   * Set the (unencoded) value of this header attribute.
   * @param string $value
   */
  public function setValue($value);
  
  /**
   * Get this attribute rendered as a compliant string.
   * @return string
   */
  public function toString();
  
}
