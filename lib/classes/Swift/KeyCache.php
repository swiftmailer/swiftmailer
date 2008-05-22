<?php

/*
 Cache interface in Swift Mailer.
 
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

//@require 'Swift/InputByteStream.php';
//@require 'Swift/OutputByteStream.php';

/**
 * Provides a mechanism for storing data using two keys.
 * @package Swift
 * @subpackage KeyCache
 * @author Chris Corbyn
 */
interface Swift_KeyCache
{
  
  /** Mode for replacing existing cached data */
  const MODE_WRITE = 1;
  
  /** Mode for appending data to the end of existing cached data */
  const MODE_APPEND = 2;
  
  /**
   * Set a string into the cache under $itemKey for the namespace $nsKey.
   * @param string $nsKey
   * @param string $itemKey
   * @param string $string
   * @param int $mode
   * @see MODE_WRITE, MODE_APPEND
   */
  public function setString($nsKey, $itemKey, $string, $mode);
  
  /**
   * Set a ByteStream into the cache under $itemKey for the namespace $nsKey.
   * @param string $nsKey
   * @param string $itemKey
   * @param Swift_OutputByteStream $os
   * @param int $mode
   * @see MODE_WRITE, MODE_APPEND
   */
  public function importFromByteStream($nsKey, $itemKey, Swift_OutputByteStream $os,
    $mode);
  
  /**
   * Provides a ByteStream which when written to, writes data to $itemKey.
   * NOTE: The stream will always write in append mode.
   * If the optional third parameter is passed all writes will go through $is.
   * @param string $nsKey
   * @param string $itemKey
   * @param Swift_InputByteStream $is, optional
   * @return Swift_InputByteStream
   */
  public function getInputByteStream($nsKey, $itemKey,
    Swift_InputByteStream $is = null);
  
  /**
   * Get data back out of the cache as a string.
   * @param string $nsKey
   * @param string $itemKey
   * @return string
   */
  public function getString($nsKey, $itemKey);
  
  /**
   * Get data back out of the cache as a ByteStream.
   * @param string $nsKey
   * @param string $itemKey
   * @param Swift_InputByteStream $is to write the data to
   */
  public function exportToByteStream($nsKey, $itemKey, Swift_InputByteStream $is);
  
  /**
   * Check if the given $itemKey exists in the namespace $nsKey.
   * @param string $nsKey
   * @param string $itemKey
   * @return boolean
   */
  public function hasKey($nsKey, $itemKey);
  
  /**
   * Clear data for $itemKey in the namespace $nsKey if it exists.
   * @param string $nsKey
   * @param string $itemKey
   */
  public function clearKey($nsKey, $itemKey);
  
  /**
   * Clear all data in the namespace $nsKey if it exists.
   * @param string $nsKey
   */
  public function clearAll($nsKey);
  
}
