<?php

/*
 KeyCache stream writer in Swift Mailer.
 
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

//@require 'Swift/KeyCache.php';
//@require 'Swift/InputByteStream.php';

/**
 * Writes data to a KeyCache using a stream.
 * @package Swift
 * @subpackage KeyCache
 * @author Chris Corbyn
 */
interface Swift_KeyCache_KeyCacheInputStream extends Swift_InputByteStream
{
  
  /**
   * Set the KeyCache to wrap.
   * @param Swift_KeyCache $keyCache
   */
  public function setKeyCache(Swift_KeyCache $keyCache);
  
  /**
   * Set the nsKey which will be written to.
   * @param string $nsKey
   */
  public function setNsKey($nsKey);
  
  /**
   * Set the itemKey which will be written to.
   * @param string $itemKey
   */
  public function setItemKey($itemKey);
  
  /**
   * Specify a stream to write through for each write().
   * @param Swift_InputByteStream $is
   */
  public function setWriteThroughStream(Swift_InputByteStream $is);
  
  /**
   * Any implementation should be cloneable, allowing the clone to access a
   * separate $nsKey and $itemKey.
   */
  public function __clone();
  
}
