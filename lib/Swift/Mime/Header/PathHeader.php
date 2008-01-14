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
require_once dirname(__FILE__) . '/../HeaderEncoder.php';

/**
 * A Path Header in Swift Mailer, such a Return-Path.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_PathHeader extends Swift_Mime_Header_StructuredHeader
{
  
  /**
   * Creates a new PathHeader with the given $name and $path.
   * @param string $name
   * @param string $path, optional
   * @param string $charset, optional
   * @param Swift_Mime_HeaderEncoder $encoder, optional
   */
  public function __construct($name, $path = null, $charset = null,
    Swift_Mime_HeaderEncoder $encoder = null)
  {
    parent::__construct($name, null, $charset, $encoder);
  }
  
}
