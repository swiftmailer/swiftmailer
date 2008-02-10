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
   * Set the level at which this entity nests.
   * A lower value is closer to the top (i.e. the message itself is zero (0)),
   * and a higher value is nested deeper in.
   * @param int $level
   * @see LEVEL_TOP, LEVEL_ATTACHMENT, LEVEL_EMBEDDED, LEVEL_SUBPART
   */
  public function setNestingLevel($level);
  
  /**
   * Get the level at which this entity shall be nested in final document.
   * The lower the value, the more outermost the entity will be nested.
   * @return int
   * @see LEVEL_TOP, LEVEL_ATTACHMENT, LEVEL_EMBEDDED, LEVEL_SUBPART
   */
  public function getNestingLevel();
  
  /**
   * Get the qualified content-type of this mime entity.
   * @return string
   */
  public function getContentType();
  
  /**
   * Returns a unique ID for this entity.
   * For most entities this will likely be the Content-ID, though it has
   * no explicit semantic meaning and can be considered an identifier for
   * programming logic purposes.
   * If a Content-ID header is present, this value SHOULD match the value of
   * the header.
   * @return string
   */
  public function getId();
  
  /**
   * Get all children nested inside this entity.
   * These are not just the immediate children, but all children.
   * @return Swift_Mime_MimeEntity[]
   */
  public function getChildren();
  
  /**
   * Set all children nested inside this entity.
   * This includes grandchildren.
   * @param Swift_Mime_MimeEntity[] $children
   */
  public function setChildren(array $children);
  
  /**
   * Get the collection of Headers in this Mime entity.
   * @return Swift_Mime_Header[]
   */
  public function getHeaders();
  
  /**
   * Get the body content of this entity as a string.
   * Returns NULL if no body has been set.
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
