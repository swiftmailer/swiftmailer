<?php

/*
 A base Mime entity in Swift Mailer.
 
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
require_once dirname(__FILE__) . '/ContentEncoder.php';
require_once dirname(__FILE__) . '/../ByteStream.php';
require_once dirname(__FILE__) . '/FieldChangeObserver.php';


/**
 * A MIME part, in a multipart message.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_SimpleMimeEntity implements Swift_Mime_MimeEntity
{
  
  /**
   * The encoder used for tranportation.
   * @var Swift_Mime_ContentEncoder
   * @access private
   */
  private $_encoder;
  
  /**
   * The collection of Headers in this entity.
   * @var Swift_Mime_Header[]
   * @access private
   */
  private $_headers = array();
  
  /**
   * The Content-type of this entity.
   * @var string
   * @access private
   */
  private $_contentType = 'text/plain';
  
  /**
   * The body of this entity, as a string.
   * @var string
   * @access private
   */
  private $_stringBody;
  
  /**
   * Observers which watch for fields being changed in the entity.
   * @var Swift_Mime_FieldChangeObserver[]
   * @access private
   */
  private $_fieldChangeObservers = array();
  
  /**
   * Creates a new SimpleMimeEntity with $headers and $encoder.
   * @param string[] $headers
   * @param Swift_Mime_ContentEncoder $encoder
   */
  public function __construct(array $headers, Swift_Mime_ContentEncoder $encoder)
  {
    $this->setHeaders($headers);
    $this->setEncoder($encoder);
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
   * Set the Headers for this entity.
   * @param Swift_Mime_Header[] $headers
   */
  public function setHeaders(array $headers)
  {
    $this->_headers = $headers;
  }
  
  /**
   * Get the collection of Headers in this Mime entity.
   * @return Swift_Mime_Header[]
   */
  public function getHeaders()
  {
    return $this->_headers;
  }
  
  /**
   * Set the Encoder used for transportation of this entity.
   * @param Swift_Mime_ContentEncoder $encoder
   */
  public function setEncoder(Swift_Mime_ContentEncoder $encoder)
  {
    $this->_encoder = $encoder;
  }
  
  /**
   * Get the Encoder used for transportation of this entity.
   * @return Swift_Mime_ContentEncoder
   */
  public function getEncoder()
  {
    return $this->_encoder;
  }
  
  /**
   * Set the content type of this entity.
   * @param string $contentType
   */
  public function setContentType($contentType)
  {
    $this->_contentType = $contentType;
    $this->_notifyFieldChanged('contenttype', $contentType);
  }
  
  /**
   * Get the content-type of this entity.
   * @return string
   */
  public function getContentType()
  {
    return $this->_contentType;
  }
  
  /**
   * Set the body of this entity as a string.
   * @param string $string
   */
  public function setBodyAsString($stringBody)
  {
    $this->_stringBody = $stringBody;
  }
  
  /**
   * Get the body content of this entity as a string.
   * @return string
   */
  public function getBodyAsString()
  {
    return $this->_stringBody;
  }
  
  /**
   * Register a new observer for changes to fields in this entity.
   * @param Swift_Mime_FieldChangeObserver $observer
   */
  public function registerFieldChangeObserver(
    Swift_Mime_FieldChangeObserver $observer)
  {
    $this->_fieldChangeObservers[] = $observer;
  }
  
  /**
   * Attach an array of other entities to this entity.
   * These will be re-ordered according to their nesting levels.
   * @param Swift_Mime_MimeEntity[] $children
   */
  public function setChildren(array $children)
  {
    $lowestChild = null;
    foreach ($children as $child)
    {
      $level = $child->getNestingLevel();
      if (is_null($lowestChild))
      {
        $lowestChild = $child;
      }
      else
      {
        if ($child->getNestingLevel() < $lowestChild->getNestingLevel())
        {
          $lowestChild = $child;
        }
      }
    }
    
    $lowestLevel = $lowestChild->getNestingLevel();
    
    if ($lowestLevel > self::LEVEL_TOP
      && $lowestLevel <= self::LEVEL_ATTACHMENT)
    {
      $this->setContentType('multipart/mixed');
    }
    elseif ($lowestLevel > self::LEVEL_ATTACHMENT
      && $lowestLevel <= self::LEVEL_EMBEDDED)
    {
      $this->setContentType('multipart/related');
    }
    elseif ($lowestLevel > self::LEVEL_EMBEDDED
      && $lowestLevel <= self::LEVEL_SUBPART)
    {
      $this->setContentType('multipart/alternative');
    }
    
    $this->_notifyFieldChanged('boundary', $this->getBoundary());
  }
  
  /**
   * Get the MIME boundary which separates any nested entities.
   * @return string
   */
  public function getBoundary()
  {
    if (!isset($this->_boundary))
    {
      $this->_boundary = '_=_swift_v4_' . time() . uniqid() . '_=_';
    }
    return $this->_boundary;
  }
  
  /**
   * Get this entire entity in its string form.
   * @return string
   */
  public function toString()
  {
    $string = '';
    foreach ($this->_headers as $header)
    {
      $string .= $header->toString();
    }
    if (is_string($this->_stringBody))
    {
      $string .= "\r\n" . $this->_stringBody;
    }
    return $string;
  }
  
  /**
   * Get this entire entity as a ByteStream.
   * @param Swift_ByteStream $is to write to
   */
  public function toByteStream(Swift_ByteStream $is)
  {
  }
  
  // -- Private methods
  
  /**
   * Notify all observers of a field being changed.
   * @param string $field
   * @param mixed $value
   * @access private
   */
  private function _notifyFieldChanged($field, $value)
  {
    foreach ($this->_fieldChangeObservers as $observer)
    {
      $observer->fieldChanged($field, $value);
    }
  }
  
}
