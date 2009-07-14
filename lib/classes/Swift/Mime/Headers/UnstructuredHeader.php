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

//@require 'Swift/Mime/Headers/AbstractHeader.php';
//@require 'Swift/Mime/HeaderEncoder.php';

/**
 * A Simple MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Headers_UnstructuredHeader
  extends Swift_Mime_Headers_AbstractHeader
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
   * Get the type of Header that this instance represents.
   * @return int
   * @see TYPE_TEXT, TYPE_PARAMETERIZED, TYPE_MAILBOX
   * @see TYPE_DATE, TYPE_ID, TYPE_PATH
   */
  public function getFieldType()
  {
    return self::TYPE_TEXT;
  }
  
  /**
   * Set the model for the field body.
   * This method takes a string for the field value.
   * @param string $model
   */
  public function setFieldBodyModel($model)
  {
    $this->setValue($model);
  }
  
  /**
   * Get the model for the field body.
   * This method returns a string.
   * @return string
   */
  public function getFieldBodyModel()
  {
    return $this->getValue();
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
    $this->clearCachedValueIf($this->_value != $value);
    $this->_value = $value;
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
  
}
