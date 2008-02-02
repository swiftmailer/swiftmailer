<?php

/*
 HeaderSet Interface in Swift Mailer.
 
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


/**
 * A collection of MIME Headers.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
interface Swift_Mime_HeaderSet
{
  
  /**
   * Add a Header to this HeaderSet.
   * @param string $name
   * @param mixed $value
   */
  public function set($name, $value);
  
  /**
   * Get a Header back out of the Set based on its name.
   * @param string $name
   * @return Swift_Mime_Header
   */
  public function get($name);
  
  /**
   * Remove a Header from this HeaderSet.
   * @param string $name
   */
  public function remove($name);
  
  /**
   * Return a standard PHP array of all Headers in this HeaderSet.
   * @return Swift_Mime_Header[]
   */
  public function toArray();
  
}
