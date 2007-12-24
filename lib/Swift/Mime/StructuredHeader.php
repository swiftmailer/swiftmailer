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


/**
 * A Structured MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_StructuredHeader extends Swift_Mime_UnstructuredHeader
{
  
  /**
   * Special characters which need to be escaped.
   * @var string[]
   * @access private
   */
  private $_specials = array(
    '\\', '(', ')', '<', '>', '[', ']', ':', ';', '@', ',', '.', '"'
    );
  
  /**
   * Gets the value of this header with all character having special meaning escaped.
   * This overrides a point of extension in UnstructuredHeader.
   * @return string
   * @access protected
   */
  protected function getPreparedValue()
  { 
    $value = $this->getValue();
    foreach ($this->_specials as $special)
    {
      $value = str_replace($special, '\\' . $special, $value);
    }
    return $value;
  }
  
}
