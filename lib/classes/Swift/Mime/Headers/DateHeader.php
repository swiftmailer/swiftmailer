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

//@require 'Swift/Mime/Headers/AbstractHeader.php';


/**
 * A Date MIME Header for Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Headers_DateHeader extends Swift_Mime_Headers_AbstractHeader
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
   * $header = new Swift_Mime_Headers_DateHeader('Date', time());
   * ?>
   * </code>
   * @param string $name of Header
   */
  public function __construct($name)
  {
    $this->setFieldName($name);
  }
  
  /**
   * Get the type of Header that this instance represents.
   * @return int
   * @see TYPE_TEXT, TYPE_PARAMETERIZED, TYPE_MAILBOX
   * @see TYPE_DATE, TYPE_ID, TYPE_PATH
   */
  public function getFieldType()
  {
    return self::TYPE_DATE;
  }
  
  /**
   * Set the model for the field body.
   * This method takes a UNIX timestamp.
   * @param int $model
   */
  public function setFieldBodyModel($model)
  {
    $this->setTimestamp($model);
  }
  
  /**
   * Get the model for the field body.
   * This method returns a UNIX timestamp.
   * @return mixed
   */
  public function getFieldBodyModel()
  {
    return $this->getTimestamp();
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
    $this->clearCachedValueIf($this->_timestamp != $timestamp);
    $this->_timestamp = $timestamp;
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
  
}
