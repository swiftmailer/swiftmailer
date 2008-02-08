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

require_once dirname(__FILE__) . '/SimpleMimeEntity.php';


/**
 * A MIME part, in a multipart message.
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
   */
  public function __construct(array $headers,
    Swift_Mime_ContentEncoder $encoder)
  {
    parent::__construct($headers, $encoder);
    $this->setNestingLevel(self::LEVEL_ATTACHMENT);
    $this->setDisposition('attachment');
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
   * Notify this entity that a field has changed to $value in its parent.
   * "Field" is a loose term and refers to class fields rather than
   * header fields.  $field will always be in lowercase and will be alpha.
   * only.
   * An example could be fieldChanged('contenttype', 'text/plain');
   * This of course reflects a change in the body of the Content-Type header.
   * Another example could be fieldChanged('charset', 'us-ascii');
   * This reflects a change in the charset parameter of the Content-Type header.
   * @param string $field in lowercase ALPHA
   * @param mixed $value
   */
  public function fieldChanged($field, $value)
  {
  }
  
}
