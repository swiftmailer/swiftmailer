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
  public function getValue()
  {
    if (!$this->getCachedValue())
    {
      $values = array();
      foreach ($this->_values as $value)
      {
        $values[] = $this->getHelper()->createPhrase($this, $value,
          $this->getCharacterSet(), $this->getEncoder(), empty($values)
          );
      }
      $this->setCachedValue(implode(', ', $values));
    }
    return $this->getCachedValue();
  }
  
  
  /**
   * Set the value of this Header as a string.
   * The tokens in the string MUST comply with RFC 2822, 3.6.
   * The value will be parsed so {@link getValueList()} returns a valid list.
   * @param string $value
   * @see __construct()
   * @see setValueList()
   * @see getValue()
   */
  public function setValue($value)
  {
    $actualValues = array();
    $values = preg_split('/(?<!\\\\),/', $value);
    foreach ($values as $phrase)
    {
      if (preg_match('/^' . $this->getHelper()->getGrammar('phrase') . '$/D', $phrase))
      {
        $actualValues[] = $this->getHelper()->decodePhrase($phrase);
      }
    }
    $this->setValueList($actualValues);
    $this->setCachedValue($value);
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
