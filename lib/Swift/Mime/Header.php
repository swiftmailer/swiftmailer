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

require_once dirname(__FILE__) . '/HeaderEncoder.php';
require_once dirname(__FILE__) . '/HeaderAttributeSet.php';


/**
 * A MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
interface Swift_Mime_Header
{
  
  /**
   * Set the encoder used for encoding the header.
   * @param Swift_Mime_HeaderEncoder $encoder
   */
  public function setEncoder(Swift_Mime_HeaderEncoder $encoder);
  
  /**
   * Get the HeaderEncoder used in this Header, or NULL if none used.
   * @return Swift_Mime_HeaderEncoder
   */
  public function getEncoder();
  
  /**
   * Set the character set used in this Header.
   * @param string $charset
   */
  public function setCharset($charset);
  
  /**
   * Get the character set of the content in this Header.
   * @return string
   */
  public function getCharset();
  
  /**
   * Get the name of this header (e.g. charset).
   * The name is an identifier and as such will be immutable.
   * @return string
   */
  public function getName();
  
  /**
   * Get the (unprepared) value of this header.
   * @return string
   */
  public function getValue();
  
  /**
   * Set the (unprepared) value of this header.
   * @param string $value
   */
  public function setValue($value);
  
  /**
   * Set a collection of HeaderAttributes to be applied to this Header.
   * @param Swift_Mime_HeaderAttributeSet $attributes
   */
  public function setAttributes(Swift_Mime_HeaderAttributeSet $attributes);
  
  /**
   * Get the collection of HeaderAttributes applied to this Header.
   * @return Swift_Mime_HeaderAttributeSet
   */
  public function getAttributes();
  
  /**
   * Set the maximum length of lines in the header.
   * @param int $length
   */
  public function setMaxLineLength($length);
  
  /**
   * Sets the value of this Header as if it's already been prepared for use.
   * Lines needn't be folded since {@link toString()} will fold long lines.
   * @param string $value
   */
  public function setPreparedValue($value);
  
  /**
   * Get the value, prepared for folding into a final header value.
   * @return string
   */
  public function getPreparedValue();
  
  /**
   * Get this Header rendered as a compliant string.
   * @return string
   */
  public function toString();
  
}
