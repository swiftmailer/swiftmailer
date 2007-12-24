<?php

/*
 A simple Header Attribute in Swift Mailer.
 
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

require_once dirname(__FILE__) . '/HeaderAttribute.php';
require_once dirname(__FILE__) . '/../Encoder.php';


/**
 * An attribute to a MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_SimpleHeaderAttribute implements Swift_Mime_HeaderAttribute
{
  
  /**
   * The name of this header attribute.
   * @var string
   * @access private
   */
  private $_name;
  
  /**
   * The value of this header attribute.
   * @var string
   * @access private
   */
  private $_value;
  
  /**
   * Creates a new HeaderAttribute with $name and $value.
   * @param string $name
   * @param string $value
   */
  public function __construct($name, $value)
  {
    $this->_name = $name;
    $this->_value = $value;
  }
  
  /**
   * Set the encoder used for encoding the attribute.
   * @param Swift_Encoder $encoder
   */
  public function setEncoder(Swift_Encoder $encoder)
  {
  }
  
  /**
   * Get the name of this attribute (e.g. charset).
   * @return string
   */
  public function getName()
  {
    return $this->_name;
  }
  
  /**
   * Get the (unencoded) value of this header attribute.
   * @return string
   */
  public function getValue()
  {
    return $this->_value;
  }
  
  /**
   * Set the (unencoded) value of this header attribute.
   * @param string $value
   */
  public function setValue($value)
  {
  }
  
  /**
   * Get this attribute rendered as a compliant string.
   * @return string
   */
  public function toString()
  {
  }
  
}
