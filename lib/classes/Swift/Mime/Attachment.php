<?php

/*
 An attachment in Swift Mailer.
 
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

//@require 'Swift/Mime/SimpleMimeEntity.php';
//@require 'Swift/Mime/ContentEncoder.php';
//@require 'Swift/FileStream.php';
//@require 'Swift/KeyCache.php';

/**
 * An attachment, in a multipart message.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Attachment extends Swift_Mime_SimpleMimeEntity
{
  
  /**
   * The disposition of this attachment (inline or attachment).
   * @var string
   * @access private
   */
  private $_disposition;
  
  /**
   * The name of this attachment when saved as a file (optional).
   * @var string
   * @access private
   */
  private $_filename;
  
  /**
   * The creation-date attribute of this attachment if specified.
   * @var int
   * @access private
   */
  private $_creationDate;
  
  /**
   * The modification-date attribute of this attachment if specified.
   * @var int
   * @access private
   */
  private $_modificationDate;
  
  /**
   * The read-date attribute of this attachment if specified.
   * @var int
   * @access private
   */
  private $_readDate;
  
  /**
   * The size of this attachment in bytes (if set).
   * @var int
   * @access private
   */
  private $_size;
  
  /**
   * Creates a new Attachment with $headers and $encoder.
   * @param string[] $headers
   * @param Swift_Mime_ContentEncoder $encoder
   * @param Swift_KeyCache $cache
   */
  public function __construct(array $headers,
    Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache)
  {
    parent::__construct($headers, $encoder, $cache);
    $this->setNestingLevel(self::LEVEL_ATTACHMENT);
    $this->setDisposition('attachment');
    $this->setContentType('application/octet-stream');
  }
  
  /**
   * Set a file into this EmbeddedFile entity.
   * The data from the file will be used as the body, and the filename will be
   * used by default.  You can override this with {@link setFilename()}.
   * This method returns an instance of $this so can be used in a fluid interface.
   * @param Swift_FileStream $file
   * @return Swift_Mime_MimeEntity
   */
  public function setFile(Swift_FileStream $file)
  {
    $this->setBodyAsByteStream($file);
    $this->setFilename(basename($file->getPath()));
    return $this;
  }
  
  /**
   * Set the disposition of this attachment.
   * Valid values according to RFC 2183 are 'inline' or 'attachment'.
   * @param string $disposition
   */
  public function setDisposition($disposition)
  {
    $this->_disposition = $disposition;
    $this->_notifyFieldChanged('disposition', $disposition);
    $this->_getCache()->clearKey($this->_getCacheKey(), 'headers');
    return $this;
  }
  
  /**
   * Get the disposition of this attachment.
   * @return string
   */
  public function getDisposition()
  {
    return $this->_disposition;
  }
  
  /**
   * Set the filename of this attachment if it is downloaded by the client.
   * This is an optional setting but it is STRONGLY advised a filename be
   * assigned to it, otherwise the client behaviour may be unpredictable.
   * @param string $filename
   */
  public function setFilename($filename)
  {
    $this->_filename = $filename;
    $this->_notifyFieldChanged('filename', $filename);
    $this->_getCache()->clearKey($this->_getCacheKey(), 'headers');
    return $this;
  }
  
  /**
   * Get the filename of this attachment if it's to be downloaded by the client.
   * Returns NULL if none set.
   * @return string
   */
  public function getFilename()
  {
    return $this->_filename;
  }
  
  /**
   * Set the creation-date of this attachment as a UNIX timestamp.
   * This is an optional setting.
   * @param int $date
   */
  public function setCreationDate($creationDate)
  {
    $this->_creationDate = $creationDate;
    $this->_notifyFieldChanged('creationdate', $creationDate);
    $this->_getCache()->clearKey($this->_getCacheKey(), 'headers');
    return $this;
  }
  
  /**
   * Get the creation-date of this attachment as a UNIX timestamp if set.
   * Returns NULL if none set.
   * @return int
   */
  public function getCreationDate()
  {
    return $this->_creationDate;
  }
  
  /**
   * Set the modificaton-date of this attachment as a UNIX timestamp.
   * This is an optional setting.
   * @param int $date
   */
  public function setModificationDate($modificationDate)
  {
    $this->_modificationDate = $modificationDate;
    $this->_notifyFieldChanged('modificationdate', $modificationDate);
    $this->_getCache()->clearKey($this->_getCacheKey(), 'headers');
    return $this;
  }
  
  /**
   * Get the modification-date of this attachment as a UNIX timestamp if set.
   * Returns NULL if none set.
   * @return int
   */
  public function getModificationDate()
  {
    return $this->_modificationDate;
  }
  
  /**
   * Set the read-date of this attachment as a UNIX timestamp.
   * This is an optional setting.
   * @param int $date
   */
  public function setReadDate($readDate)
  {
    $this->_readDate = $readDate;
    $this->_notifyFieldChanged('readdate', $readDate);
    $this->_getCache()->clearKey($this->_getCacheKey(), 'headers');
    return $this;
  }
  
  /**
   * Get the read-date of this attachment as a UNIX timestamp if set.
   * Returns NULL if none set.
   * @return int
   */
  public function getReadDate()
  {
    return $this->_readDate;
  }
  
  /**
   * Set the size of this attachment in bytes.
   * This is an optional setting.
   * @param int $size
   */
  public function setSize($size)
  {
    $this->_size = $size;
    $this->_notifyFieldChanged('size', $size);
    $this->_getCache()->clearKey($this->_getCacheKey(), 'headers');
    return $this;
  }
  
  /**
   * Get the size of this attachment in bytes if set.
   * Returns NULL if none set.
   * @return int
   */
  public function getSize()
  {
    return $this->_size;
  }
  
  /**
   * Overridden to prevent conflict.
   * @param string $field in lowercase ALPHA
   * @param mixed $value
   */
  public function fieldChanged($field, $value)
  {
  }
  
}
