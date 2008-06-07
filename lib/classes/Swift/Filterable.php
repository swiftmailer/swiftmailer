<?php

/*
 Filterable interface from Swift Mailer.
 
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

//@require 'Swift/StreamFilter.php';

/**
 * Allows StreamFilters to operate on a stream.
 * @package Swift
 * @author Chris Corbyn
 */
interface Swift_Filterable
{
  
  /**
   * Add a new StreamFilter, referenced by $key.
   * @param Swift_StreamFilter $filter
   * @param string $key
   */
  public function addFilter(Swift_StreamFilter $filter, $key);
  
  /**
   * Remove an existing filter using $key.
   * @param string $key
   */
  public function removeFilter($key);
  
}
