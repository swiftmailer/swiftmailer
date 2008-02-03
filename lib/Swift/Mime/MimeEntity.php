<?php

/*
 MimeEntity Interface in Swift Mailer, for attachments, mime-parts etc.
 
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

require_once dirname(__FILE__) . '/ContentEncoder.php';
require_once dirname(__FILE__) . '/../ByteStream.php';


/**
 * A MIME entity, such as an attachment.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
interface Swift_Mime_MimeEntity
{
  
  /** Main message document; there can only be one of these */
  const LEVEL_TOP = 0;
  
  /** An entity which nests with the same precedence as an attachment */
  const LEVEL_ATTACHMENT = 10;
  
  /** An entity which nests with the same precedence as embedded content */
  const LEVEL_EMBEDDED = 20;
  
  /** An entity which nests with the same precedence as a mime part */
  const LEVEL_SUBPART = 30;
  
  /**
   * Get the level at which this entity shall be nested in final document.
   * @return int
   * @see LEVEL_TOP, LEVEL_ATTACHMENT, LEVEL_EMBEDDED, LEVEL_SUBPART
   */
  public function getNestingLevel();
  
  /**
   * Get the collection of Headers in this Mime entity.
   * @return Swift_Mime_Header[]
   */
  public function getHeaders();
  
  /**
   * Get the content-type of this entity.
   * @return string
   */
  public function getContentType();
  
  /**
   * Get the body content of this entity as a string.
   * @return string
   */
  public function getBodyAsString();
  
  /**
   * Get this entire entity in its string form.
   * @return string
   */
  public function toString();
  
  /**
   * Get this entire entity as a ByteStream.
   * @param Swift_ByteStream $is to write to
   */
  public function toByteStream(Swift_ByteStream $is);
  
}
