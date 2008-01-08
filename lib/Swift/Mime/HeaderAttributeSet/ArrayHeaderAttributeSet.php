<?php

/*
 A HeaderAttributeSet using an array in Swift Mailer.
 
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

require_once dirname(__FILE__) . '/../HeaderAttributeSet.php';
require_once dirname(__FILE__) . '/../HeaderAttribute.php';


/**
 * A collection of MIME HeaderAttributes held in an array.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_HeaderAttributeSet_ArrayHeaderAttributeSet
  implements Swift_Mime_HeaderAttributeSet
{
  
  /**
   * The collection of HeaderAttributes.
   * @var Swift_Mime_HeaderAttribute[]
   * @access private
   */
  private $_attributes = array();
  
  /**
   * Add a HeaderAttribute to this HeaderAttributeSet.
   * @param Swift_Mime_HeaderAttribute $attribute
   */
  public function addAttribute(Swift_Mime_HeaderAttribute $attribute)
  {
    $name = strtolower($attribute->getName());
    $this->_attributes[$name] = $attribute;
  }
  
  /**
   * Get a HeaderAttribute back out of the Set based on its name.
   * @param string $name
   * @return Swift_Mime_HeaderAttribute
   */
  public function getAttributeByName($name)
  {
    $name = strtolower($name);
    if (array_key_exists($name, $this->_attributes))
    {
      return $this->_attributes[$name];
    }
    else
    {
      return null;
    }
  }
  
  /**
   * Remove a HeaderAttribute from this HeaderAttributeSet.
   * @param Swift_Mime_HeaderAttribute $attribute
   */
  public function removeAttribute(Swift_Mime_HeaderAttribute $attribute)
  {
    $name = $attribute->getName();
    return $this->removeAttributeByName($name);
  }
  
  /**
   * Remove a HeaderAttribute from this HeaderAttributeSet based on it's name.
   * @param string $name
   */
  public function removeAttributeByName($name)
  {
    $name = strtolower($name);
    unset($this->_attributes[$name]);
  }
  
  /**
   * Return a standard PHP array of all HeaderAttributes in this HeaderAttributeSet.
   * @return Swift_Mime_HeaderAttribute[]
   */
  public function toArray()
  {
    return array_values($this->_attributes);
  }
  
}
