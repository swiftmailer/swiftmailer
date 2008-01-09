<?php

/*
 An Address Mime Header in Swift Mailer.
 
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
 * An Address MIME Header for something like To or Cc.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_AddressHeader
  extends Swift_Mime_Header_StructuredHeader
{
  
  /**
   * The address used in this Header.
   * @var string
   * @access private
   */
  private $_address;
  
  /**
   * Creates a new AddressHeader with $name and $address.
   * @param string $name of Header
   * @param string $address, optional
   * @param string $charset, optional
   * @param Swift_Mime_HeaderEncoder $encoder, optional
   */
  public function __construct($name, $address = null, $charset = null,
    Swift_Mime_HeaderEncoder $encoder = null)
  {
    parent::__construct($name, null, $charset, $encoder);
    
    if (!is_null($address))
    {
      $this->setAddress($address);
    }
  }
  
  /**
   * Set the address of this Header.
   * @param string $address
   */
  public function setAddress($address)
  {
    $this->_address = $address;
    $this->setValue($address);
  }
  
  /**
   * Get the address of this Header.
   * @return string
   */
  public function getAddress()
  {
    return $this->_address;
  }
  
}
