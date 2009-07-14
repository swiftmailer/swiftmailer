<?php

/*
 Replacements interface for the Decorator plugin in Swift Mailer.

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
 * Allows customization of Messages on-the-fly.
 * 
 * @package Swift
 * @subpackage Plugins
 * 
 * @author Chris Corbyn
 */
interface Swift_Plugins_Decorator_Replacements
{
  
  /**
   * Return the array of replacements for $address.
   * 
   * This method is invoked once for every single recipient of a message.
   * 
   * If no replacements can be found, an empty value (NULL) should be returned
   * and no replacements will then be made on the message.
   * 
   * @param string $address
   * 
   * @return array
   */
  public function getReplacementsFor($address);
  
}
