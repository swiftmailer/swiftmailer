<?php

/*
 Default KeyCache stream writer in Swift Mailer.
 
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

require_once dirname(__FILE__) . '/../KeyCache.php';
require_once dirname(__FILE__) . '/KeyCacheInputStream.php';

/**
 * Writes data to a KeyCache using a stream.
 * @package Swift
 * @subpackage KeyCache
 * @author Chris Corbyn
 */
class Swift_KeyCache_SimpleKeyCacheInputStream
  implements Swift_KeyCache_KeyCacheInputStream
{
  
  /**
   * The KeyCache being written to.
   * @var Swift_KeyCache
   * @access private
   */
  private $_keyCache;
  
  /**
   * The nsKey of the KeyCache being written to.
   * @var string
   * @access private
   */
  private $_nsKey;
  
  /**
   * The itemKey of the KeyCache being written to.
   * @var string
   * @access private
   */
  private $_itemKey;
  
  /**
   * Set the KeyCache to wrap.
   * @param Swift_KeyCache $keyCache
   */
  public function setKeyCache(Swift_KeyCache $keyCache)
  {
    $this->_keyCache = $keyCache;
  }
  
  /**
   * Writes $bytes to the end of the stream.
   * @param string $bytes
   */
  public function write($bytes)
  {
    $this->_keyCache->setString(
      $this->_nsKey, $this->_itemKey, $bytes, Swift_KeyCache::MODE_APPEND
      );
  }
  
  /**
   * Flush the contents of the stream (empty it) and set the internal pointer
   * to the beginning.
   */
  public function flushContents()
  {
    $this->_keyCache->clearKey($this->_nsKey, $this->_itemKey);
  }
  
  /**
   * Set the nsKey which will be written to.
   * @param string $nsKey
   */
  public function setNsKey($nsKey)
  {
    $this->_nsKey = $nsKey;
  }
  
  /**
   * Set the itemKey which will be written to.
   * @param string $itemKey
   */
  public function setItemKey($itemKey)
  {
    $this->_itemKey = $itemKey;
  }
  
  /**
   * Any implementation should be cloneable, allowing the clone to access a
   * separate $nsKey and $itemKey.
   */
  public function __clone()
  {
  }
  
}
