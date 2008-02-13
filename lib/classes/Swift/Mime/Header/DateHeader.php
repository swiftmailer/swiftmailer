<?php

/*
 A Date Mime Header in Swift Mailer.
 
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

//@require 'Swift/Mime/Header/StructuredHeader.php';
//@require 'Swift/Mime/FieldChangeObserver.php';


/**
 * A Date MIME Header for Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_DateHeader extends Swift_Mime_Header_StructuredHeader
  implements Swift_Mime_FieldChangeObserver
{
  
  /**
   * The UNIX timestamp value of this Header.
   * @var int
   * @access private
   */
  private $_timestamp;
  
  /**
   * Creates a new DateHeader with $name and $timestamp.
   * Example:
   * <code>
   * <?php
   * $header = new Swift_Mime_Header_DateHeader('Date', time());
   * ?>
   * </code>
   * @param string $name of Header
   */
  public function __construct($name)
  {
    $this->setFieldName($name);
  }
  
  /**
   * Get the UNIX timestamp of the Date in this Header.
   * @return int
   */
  public function getTimestamp()
  {
    return $this->_timestamp;
  }
  
  /**
   * Set the UNIX timestamp of the Date in this Header.
   * @param int $timestamp
   */
  public function setTimestamp($timestamp)
  {
    if (!is_null($timestamp))
    {
      $timestamp = (int) $timestamp;
    }
    $this->_timestamp = $timestamp;
    $this->setCachedValue(null);
  }
  
  /**
   * Get the string value of the body in this Header.
   * This is not necessarily RFC 2822 compliant since folding white space will
   * not be added at this stage (see {@link toString()} for that).
   * @return string
   * @see toString()
   */
  public function getFieldBody()
  {
    if (!$this->getCachedValue())
    {
      if (isset($this->_timestamp))
      {
        $this->setCachedValue(date('r', $this->_timestamp));
      }
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
    
    if ('date' == $fieldName && 'date' == $field)
    {
      $this->setTimestamp($value);
    }
  }
  
}
