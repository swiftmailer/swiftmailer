<?php

/*
 A Path Header in Swift Mailer, such a Return-Path.
 
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

/**
 * A Path Header in Swift Mailer, such a Return-Path.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_PathHeader extends Swift_Mime_Header_StructuredHeader
{
  
  /**
   * The address in this Header (if specified).
   * @var string
   * @access private
   */
  private $_address;
  
  /**
   * Creates a new PathHeader with the given $name and $address.
   * @param string $name
   * @param string $address, optional
   */
  public function __construct($name, $address = null)
  {
    parent::__construct($name);
    
    if (!is_null($address))
    {
      $this->setAddress($address);
    }
  }
  
  /**
   * Set the Address which should appear in this Header.
   * @param string $address
   */
  public function setAddress($address)
  {
    if (is_null($address))
    {
      $this->_address = null;
    }
    elseif (preg_match('/^' . $this->getHelper()->getGrammar('addr-spec') . '$/D',
      $address))
    {
      $this->_address = $address;
    }
    else
    {
      throw new Exception(
        'Address set in PathHeader does not comply with addr-spec of RFC 2822.'
        );
    }
    $this->setCachedValue(null);
  }
  
  /**
   * Get the address which is used in this Header (if any).
   * Null is returned if no address is set.
   * @return string
   */
  public function getAddress()
  {
    return $this->_address;
  }
  
  /**
   * Set the value of this Header as a string.
   * The tokens in the string MUST comply with RFC 2822, 3.6.7.
   * The value will be parsed so {@link getAddress()} returns the correct address.
   * Example:
   * <code>
   * <?php
   * //Sets an address in the Header. 
   * $header->setValue('<person@address.com>');
   * //or
   * $header->setValue('<(some comment)>');
   * ?>
   * </code>
   * @param string $value
   * @see __construct()
   * @see setAddress()
   * @see getValue()
   */
  public function setValue($value)
  {
    if (preg_match('/^' . $this->getHelper()->getGrammar('path') . '$/D', $value))
    {
      $path = substr($this->getHelper()->trimCFWS($value), 1, -1); //Remove < and >
      if (preg_match('/^' . $this->getHelper()->getGrammar('addr-spec') . '$/D', $path))
      {
        $address = $path;
      }
      else //Must just be CFWS
      {
        $address = null;
      }
      $this->setAddress($address);
      $this->setCachedValue($value);
    }
    else
    {
      throw new Exception(
        'Value does not comply with the RFC 2822 definition of path.'
        );
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
      $this->setCachedValue('<' . $this->getAddress() . '>');
    }
    return $this->getCachedValue();
  }
  
  /**
   * Sets the value of this Header as if it's already been prepared for use.
   * Lines needn't be folded since {@link toString()} will fold long lines.
   * @param string $value
   */
  public function setPreparedValue($value)
  {
    return $this->setValue($value);
  }
  
  /**
   * Gets the value with all needed tokens prepared for insertion into the Header.
   * @return string
   */
  public function getPreparedValue()
  {
    return $this->getValue();
  }
  
}
