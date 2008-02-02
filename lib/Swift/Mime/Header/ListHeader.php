<?php

/*
 A List Mime Header in Swift Mailer for showing a list of comma separated
 values (such as the Keywords header).
 
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
 * A List Mime Header in Swift Mailer for showing a list of comma separated
 * values (such as the Keywords header).
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_ListHeader
  extends Swift_Mime_Header_StructuredHeader
{
  
  /**
   * The list of values displayed in this Header.
   * @var string[]
   * @access private
   */
  private $_values = array();
  
  /**
   * Creates a new ListHeader with the given $name and $values.
   * @param string $name
   * @param string[] $values, optional
   * @param string $charset, optional
   * @param Swift_Mime_HeaderEncoder $encoder, optional
   */
  public function __construct($name, $values = array(), $charset = null,
    Swift_Mime_HeaderEncoder $encoder = null)
  {
    parent::__construct($name, null, $charset, $encoder);
    
    $this->setValueList($values);
  }
  
  /**
   * Set the list of values to display in this Header.
   * @param string[] $values
   */
  public function setValueList(array $values)
  {
    $this->_values = $values;
    $this->setCachedValue(null);
  }
  
  /**
   * Get the list of values displayed in this Header.
   * @return string[]
   */
  public function getValueList()
  {
    return $this->_values;
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
      $values = array();
      foreach ($this->_values as $value)
      {
        $values[] = $this->getHelper()->createPhrase($this, $value,
          $this->getCharset(), $this->getEncoder(), empty($values)
          );
      }
      $this->setCachedValue(implode(', ', $values));
    }
    return $this->getCachedValue();
  }
  
}
