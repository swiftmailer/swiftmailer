<?php

/*
 Factory for creating MIME Headers in Swift Mailer.
 
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

//@require 'Swift/Mime/CharsetObserver.php';

/**
 * Creates MIME headers.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
interface Swift_Mime_HeaderFactory extends Swift_Mime_CharsetObserver
{
  
  /**
   * Create a new Mailbox Header with a list of $addresses.
   * @param string $name
   * @param array|string $addresses
   * @return Swift_Mime_Header
   */
  public function createMailboxHeader($name, $addresses = null);
  
  /**
   * Create a new Date header using $timestamp (UNIX time).
   * @param string $name
   * @param int $timestamp
   * @return Swift_Mime_Header
   */
  public function createDateHeader($name, $timestamp = null);
  
  /**
   * Create a new basic text header with $name and $value.
   * @param string $name
   * @param string $value
   * @return Swift_Mime_Header
   */
  public function createTextHeader($name, $value = null);
  
  /**
   * Create a new ParameterizedHeader with $name, $value and $params.
   * @param string $name
   * @param string $value
   * @param array $params
   * @return Swift_Mime_ParameterizedHeader
   */
  public function createParameterizedHeader($name, $value = null,
    $params = array());
  
  /**
   * Create a new ID header for Message-ID or Content-ID.
   * @param string $name
   * @param string|array $ids
   * @return Swift_Mime_Header
   */
  public function createIdHeader($name, $ids = null);
  
  /**
   * Create a new Path header with an address (path) in it.
   * @param string $name
   * @param string $path
   * @return Swift_Mime_Header
   */
  public function createPathHeader($name, $path = null);
  
}
