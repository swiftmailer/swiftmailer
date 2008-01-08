<?php

/*
 A HeaderSet using an array in Swift Mailer.
 
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

require_once dirname(__FILE__) . '/../HeaderSet.php';
require_once dirname(__FILE__) . '/../Header.php';


/**
 * A collection of MIME Headers held in an array.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_HeaderSet_ArrayHeaderSet implements Swift_Mime_HeaderSet
{
  
  /**
   * The collection of Headers.
   * @var Swift_Mime_Header[]
   * @access private
   */
  private $_headers = array();
  
  /**
   * Add a Header to this HeaderSet.
   * @param Swift_Mime_Header $header
   */
  public function addHeader(Swift_Mime_Header $header)
  {
    $name = strtolower($header->getName());
    $this->_headers[$name] = $header;
  }
  
  /**
   * Get a Header back out of the Set based on its name.
   * @param string $name
   * @return Swift_Mime_Header
   */
  public function getHeaderByName($name)
  {
    $name = strtolower($name);
    if (array_key_exists($name, $this->_headers))
    {
      return $this->_headers[$name];
    }
    else
    {
      return null;
    }
  }
  
  /**
   * Remove a Header from this HeaderSet.
   * @param Swift_Mime_Header $header
   */
  public function removeHeader(Swift_Mime_Header $header)
  {
    $name = $header->getName();
    return $this->removeHeaderByName($name);
  }
  
  /**
   * Remove a Header from this HeaderSet based on it's name.
   * @param string $name
   */
  public function removeHeaderByName($name)
  {
    $name = strtolower($name);
    unset($this->_headers[$name]);
  }
  
  /**
   * Return a standard PHP array of all Headers in this HeaderSet.
   * @return Swift_Mime_Header[]
   */
  public function toArray()
  {
    return array_values($this->_headers);
  }
  
}
