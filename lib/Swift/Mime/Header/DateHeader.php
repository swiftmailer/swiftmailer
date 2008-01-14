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

require_once dirname(__FILE__) . '/StructuredHeader.php';
require_once dirname(__FILE__) . '/../HeaderEncoder.php';


/**
 * A Date MIME Header for Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_DateHeader extends Swift_Mime_Header_StructuredHeader
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
   * @param int $timestamp, optional
   * @param string $charset, optional
   * @param Swift_Mime_HeaderEncoder $encoder, optional
   */
  public function __construct($name, $timestamp = null, $charset = null,
    Swift_Mime_HeaderEncoder $encoder = null)
  {
    parent::__construct($name, null, $charset, $encoder);
    
    if (!is_null($timestamp))
    {
      $this->setTimestamp($timestamp);
    }
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
    $this->_timestamp = (int) $timestamp;
    $this->setCachedValue(null);
  }
  
  /**
   * Set the value of this Header as a string.
   * The tokens in the string MUST comply with RFC 2822, 3.3.
   * The value will be parsed so {@link getTimestamp()} returns a valid value.
   * Example:
   * <code>
   * <?php
   * $header->setValue('Mon, 14 Jan 2008 22:59:31 +1100');
   * ?>
   * </code>
   * @param string $value
   * @see __construct()
   * @see setTimestamp()
   * @see getValue()
   */
  public function setValue($value)
  {
    if (preg_match('/^' . $this->rfc2822Tokens['date-time'] . '$/D', $value))
    {
      $this->setTimestamp(
        strtotime($this->unfoldWhiteSpace($this->trimCFWS($value)))
        );
      $this->setCachedValue($value);
    }
    else
    {
      throw new Exception('Date value does not comply with RFC 2822, 3.4.');
    }
  }
  
  /**
   * Get the string value of the body in this Header.
   * This is not necessarily RFC 2822 compliant since folding white space will
   * not be added at this stage (see {@link toString()} for that).
   * @return string
   * @see toString()
   */
  public function getValue()
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
  
  // -- Overridden points of extension
  
  /**
   * Gets the value with all needed tokens prepared for insertion into the Header.
   * @return string
   * @access protected
   */
  protected function getPreparedValue()
  {
    return $this->getValue();
  }
  
}
