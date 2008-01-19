<?php

/*
 A Structured Mime Header in Swift Mailer.
 
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

require_once dirname(__FILE__) . '/UnstructuredHeader.php';
require_once dirname(__FILE__) . '/../HeaderEncoder.php';
require_once dirname(__FILE__) . '/../HeaderComponentHelper.php';

/**
 * A Structured MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_StructuredHeader
  extends Swift_Mime_Header_UnstructuredHeader
{
  
  /**
   * The value of this Header, cached.
   * @var string
   * @access private
   */
  private $_cachedValue = null;
  
  /**
   * Creates a new StructuredHeader with the given $name and $id.
   * @param string $name
   * @param mixed $id, optional as string or string[]
   * @param string $charset, optional
   * @param Swift_Mime_HeaderEncoder $encoder, optional
   */
  public function __construct($name, $value = null, $charset = null,
    Swift_Mime_HeaderEncoder $encoder = null)
  {
    parent::__construct($name, $value, $charset, $encoder);
  }
  
  // -- Protected methods
  
  /**
   * Set a value into the cache.
   * @param string $value
   * @access protected
   */
  protected function setCachedValue($value)
  {
    $this->_cachedValue = $value;
  }
  
  /**
   * Get the value in the cache.
   * @return string
   * @access protected
   */
  protected function getCachedValue()
  {
    return $this->_cachedValue;
  }
  
}
