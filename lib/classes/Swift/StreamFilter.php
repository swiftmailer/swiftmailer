<?php

/*
 StreamFilter interface from Swift Mailer.
 
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
 * Processes bytes as they pass through a stream and performs filtering.
 * @package Swift
 * @author Chris Corbyn
 */
interface Swift_StreamFilter
{
  
  /**
   * Based on the buffer given, this returns true if more buffering is needed.
   * @param mixed $buffer
   * @return boolean
   */
  public function shouldBuffer($buffer);
  
  /**
   * Filters $buffer and returns the changes.
   * @param mixed $buffer
   * @return mixed
   */
  public function filter($buffer);
  
}
