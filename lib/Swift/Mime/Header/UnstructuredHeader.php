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
require_once dirname(__FILE__) . '/../FieldChangeObserver.php';
require_once dirname(__FILE__) . '/AbstractHeader.php';


/**
 * A Simple MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_UnstructuredHeader
  extends Swift_Mime_Header_AbstractHeader
  implements Swift_Mime_FieldChangeObserver
{
  
  /**
   * The value of this Header.
   * @var string
   * @access private
   */
  private $_value;
  
  /**
   * Creates a new SimpleHeader with $name.
   * @param string $name
   * @param Swift_Mime_HeaderEncoder $encoder
   */
  public function __construct($name, Swift_Mime_HeaderEncoder $encoder)
  {
    $this->setFieldName($name);
    $this->setEncoder($encoder);
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
        str_replace('\\', '\\\\', $this->encodeWords(
          $this, $this->_value, -1, $this->getCharset(), $this->getEncoder()
          ))
        );
    }
    return $this->getCachedValue();
  }
  
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
  public function fieldChanged($field, $value)
  {
    $fieldName = strtolower($this->getFieldName());
    
    if ('content-transfer-encoding' == $fieldName)
    {
      if ('encoding' == $field)
      {
        $this->setValue($value);
      }
    }
    elseif ('content-description' == $fieldName)
    {
      if ('description' == $field)
      {
        $this->setValue($value);
      }
    }
  }
  
}
