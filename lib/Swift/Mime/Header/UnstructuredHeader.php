<?php

/*
 A Simple Mime Header in Swift Mailer.
 
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

require_once dirname(__FILE__) . '/../HeaderEncoder.php';
require_once dirname(__FILE__) . '/../HeaderAttributeSet.php';
require_once dirname(__FILE__) . '/../HeaderComponentHelper.php';
require_once dirname(__FILE__) . '/AbstractHeader.php';


/**
 * A Simple MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_UnstructuredHeader
  extends Swift_Mime_Header_AbstractHeader
{
  
  /**
   * The value of this Header.
   * @var string
   * @access private
   */
  private $_value;
  
  /**
   * Creates a new SimpleHeader with $name and $value.
   * @param string $name
   * @param string $value, optional
   * @param string $charset, optional
   * @param Swift_Mime_HeaderEncoder $encoder, optional
   */
  public function __construct($name, $value = null, $charset = null,
    Swift_Mime_HeaderEncoder $encoder = null)
  {
    $this->setFieldName($name);
    $this->_value = $value;
    if (!is_null($charset))
    {
      $this->setCharset($charset);
    }
    if (!is_null($encoder))
    {
      $this->setEncoder($encoder);
    }
    
    $this->setHelper(new Swift_Mime_HeaderComponentHelper());
  }
  
  /**
   * Get the (unencoded) value of this header.
   * @return string
   */
  public function getValue()
  {
    return $this->_value;
  }
  
  /**
   * Set the (unencoded) value of this header.
   * @param string $value
   */
  public function setValue($value)
  {
    $this->_value = $value;
    $this->setCachedValue(null);
  }
  
  /**
   * Get the value of this header prepared for rendering.
   * @return string
   */
  public function getFieldBody()
  {
    if (!$this->getCachedValue())
    {
      $this->setCachedValue(
        str_replace('\\', '\\\\', $this->getHelper()->encodeWords(
          $this, $this->_value, -1, $this->getCharset(), $this->getEncoder()
          ))
        );
    }
    return $this->getCachedValue();
  }
  
}
