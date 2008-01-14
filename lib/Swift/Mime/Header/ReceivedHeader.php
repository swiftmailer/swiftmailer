<?php

/*
 A Received (trace) Mime Header in Swift Mailer.
 
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
 * A Received (trace) Mime Header in Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_ReceivedHeader
  extends Swift_Mime_Header_StructuredHeader
{
  
  /**
   * Creates a new ReceivedHeader with the given $name and $info.
   * @param string $name
   * @param string[] $info, optional
   * @param string $charset, optional
   * @param Swift_Mime_HeaderEncoder $encoder, optional
   */
  public function __construct($name, $info = array(), $charset = null,
    Swift_Mime_HeaderEncoder $encoder = null)
  {
    parent::__construct($name, null, $charset, $encoder);
  }
  
}
