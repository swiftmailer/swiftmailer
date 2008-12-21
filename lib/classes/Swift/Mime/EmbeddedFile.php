<?php

/*
 An embedded file (such as image/audio) in Swift Mailer.
 
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

//@require 'Swift/Mime/Attachment.php';
//@require 'Swift/Mime/ContentEncoder.php';
//@require 'Swift/KeyCache.php';
//@require

/**
 * An embedded file, in a multipart message.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_EmbeddedFile extends Swift_Mime_Attachment
{
  
  /**
   * Creates a new Attachment with $headers and $encoder.
   * @param Swift_Mime_HeaderSet $headers
   * @param Swift_Mime_ContentEncoder $encoder
   * @param Swift_KeyCache $cache
   * @param array $mimeTypes optional
   */
  public function __construct(Swift_Mime_HeaderSet $headers,
    Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache,
    $mimeTypes = array())
  {
    parent::__construct($headers, $encoder, $cache, $mimeTypes);
    $this->setDisposition('inline');
    $this->setId($this->getId());
  }
  
  /**
   * Get the nesting level of this EmbeddedFile.
   * Returns {@link LEVEL_RELATED}.
   * @return int
   */
  public function getNestingLevel()
  {
    return self::LEVEL_RELATED;
  }
  
}
