<?php

/*
 A mime part in Swift Mailer.
 
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

require_once dirname(__FILE__) . '/MimeEntity.php';
require_once dirname(__FILE__) . '/HeaderSet.php';
require_once dirname(__FILE__) . '/ContentEncoder.php';
require_once dirname(__FILE__) . '/../ByteStream.php';

/**
 * A MIME part, in a multipart message.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_MimePart implements Swift_Mime_MimeEntity
{
  
  /**
   * Set the collection of Headers in this Mime entity.
   * @param Swift_Mime_HeaderSet $headers
   */
  public function setHeaders(Swift_Mime_HeaderSet $headers)
  {
  }
  
  /**
   * Get the collection of Headers in this Mime entity.
   * @return Swift_Mime_HeaderSet
   */
  public function getHeaders()
  {
  }
  
  /**
   * Set the ContentEncoder which encodes this entity prior to rendering.
   * @param Swift_Mime_ContentEncoder $encoder
   */
  public function setEncoder(Swift_Mime_ContentEncoder $encoder)
  {
  }
  
  /**
   * Get the ContentEncoder which encodes this entity prior to rendering.
   * @return Swift_Mime_ContentEncoder
   */
  public function getEncoder()
  {
  }
  
  /**
   * Set the content-type of this entity.
   * e.g. text/html or image/jpeg
   * @param string $type
   */
  public function setContentType($type)
  {
  }
  
  /**
   * Get the content-type of this entity.
   * @return string
   */
  public function getContentType()
  {
  }
  
  /**
   * Set an entities which are direct children of this entity.
   * @param Swift_Mime_MimeEntity[] $children
   */
  public function setChildren($children)
  {
  }
  
  /**
   * Get direct child entities of the entity.
   * @return Swift_Mime_MimeEntity[]
   */
  public function getChildren()
  {
  }
  
  /**
   * Get the level at which this entity shall be nested in final document.
   * @return int
   * @see LEVEL_TOP, LEVEL_ATTACHMENT, LEVEL_EMBEDDED, LEVEL_SUBPART
   */
  public function getNestingLevel()
  {
  }
  
  /**
   * Set the body content of this entity as a string.
   * @param string $string
   */
  public function setBodyAsString($string)
  {
  }
  
  /**
   * Get the body content of this entity as a string.
   * @return string
   */
  public function getBodyAsString()
  {
  }
  
  /**
   * Set the body content of this entity as a ByteStream.
   * @param Swift_ByteStream $os
   */
  public function setBodyAsByteStream(Swift_ByteStream $os)
  {
  }
  
  /**
   * Get the body content of this entity as a ByteStream.
   * @return Swift_ByteStream
   */
  public function getBodyAsByteStream()
  {
  }
  
  /**
   * Get this entire entity in its string form.
   * @return string
   */
  public function toString()
  {
  }
  
  /**
   * Get this entire entity as a ByteStream.
   * @param Swift_ByteStream $is to write to
   */
  public function toByteStream(Swift_ByteStream $is)
  {
  }
  
}
