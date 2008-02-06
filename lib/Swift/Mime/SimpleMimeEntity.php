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
require_once dirname(__FILE__) . '/EntityFactory.php';


/**
 * A MIME part, in a multipart message.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_SimpleMimeEntity
  implements Swift_Mime_MimeEntity, Swift_Mime_EntityFactory
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
   * The unique ID of this entity.
   * @var string
   * @access private
   */
  private $_id;
  
  /**
   * The optional description of this entity.
   * @var string
   * @access private
   */
  private $_description;
  
  /**
   * The maximum length of all lines in this entity (excluding the CRLF).
   * @var int
   * @access private
   */
  private $_maxLineLength = 78;
  
  /**
   * The body of this entity, as a string.
   * @var string
   * @access private
   */
  private $_stringBody;
  
  /**
   * Children which are nested anywhere inside this mime entity or it's children.
   * @var Swift_Mime_MimeEntity[]
   * @access private
   */
  private $_children = array();
  
  /**
   * Children which are directly nested inside this entity.
   * @var Swift_Mime_MimeEntity[]
   * @access private
   */
  private $_immediateChildren = array();
  
  /**
   * Observers which watch for fields being changed in the entity.
   * @var Swift_Mime_FieldChangeObserver[]
   * @access private
   */
  private $_fieldChangeObservers = array();
  
  /**
   * The level at which this entity nests.
   * @var int
   * @access private
   */
  private $_nestingLevel = self::LEVEL_SUBPART;
  
  /**
   * A factory which creates new skeleton mime entities.
   * @var Swift_Mime_EntityFactory
   * @access private
   */
  private $_entityFactory;
  
  /**
   * Encodings which are safe to use on composite media types.
   * @var string[]
   * @access private
   */
  private $_compositeSafeEncodings = array('7bit', '8bit', 'binary');
  
  /**
   * Creates a new SimpleMimeEntity with $headers and $encoder.
   * @param string[] $headers
   * @param Swift_Mime_ContentEncoder $encoder
   */
  public function __construct(array $headers,
    Swift_Mime_ContentEncoder $encoder)
  {
    $this->setHeaders($headers);
    $this->setEncoder($encoder);
    $this->setId($this->_generateId());
  }
  
  /**
   * Set the level at which this entity nests.
   * A lower value is closer to the top (i.e. the message itself is zero (0)),
   * and a higher value is nested deeper in.
   * @param int $level
   * @see Swift_Mime_MimeEntity::LEVEL_SUBPART
   * @see Swift_Mime_MimeEntity::LEVEL_ATTACHMENT
   * @see Swift_Mime_MimeEntity::LEVEL_EMBEDDED
   */
  public function setNestingLevel($level)
  {
    $this->_nestingLevel = $level;
  }
  
  /**
   * Get the level at which this entity shall be nested in final document.
   * @return int
   * @see LEVEL_TOP, LEVEL_ATTACHMENT, LEVEL_EMBEDDED, LEVEL_SUBPART
   */
  public function getNestingLevel()
  {
    return $this->_nestingLevel;
  }
  
  /**
   * Set the Headers for this entity.
   * @param Swift_Mime_Header[] $headers
   */
  public function setHeaders(array $headers)
  {
    foreach ($headers as $header)
    {
      if ($header instanceof Swift_Mime_FieldChangeObserver)
      {
        $this->registerFieldChangeObserver($header);
      }
    }
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
    $this->_notifyFieldChanged('encoding', $encoder->getName());
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
   * Set the unique ID of this mime entity.
   * This should be valid syntax for a Content-ID header (i.e. xxx@yyy).
   * @param string $id
   */
  public function setId($id)
  {
    $this->_id = $id;
    $this->_notifyFieldChanged('id', $id);
  }
  
  /**
   * Get the unique identifier for this mime entity.
   * @return string
   */
  public function getId()
  {
    return $this->_id;
  }
  
  /**
   * Set an optional description for this mime entity.
   * @param string $description
   */
  public function setDescription($description)
  {
    $this->_description = $description;
    $this->_notifyFieldChanged('description', $description);
  }
  
  /**
   * Get the optional description this mime entity, or null of not set.
   * @return string
   */
  public function getDescription()
  {
    return $this->_description;
  }
  
  /**
   * Set the maximum length before lines are wrapped in this entity.
   * @param int $length
   */
  public function setMaxLineLength($length)
  {
    $this->_maxLineLength = (int) $length;
  }
  
  /**
   * Get the maximum length before lines are wrapped in this entity.
   * @return int
   */
  public function getMaxLineLength()
  {
    return $this->_maxLineLength;
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
    $immediateChildren = array();
    $grandchildren = array();
    
    foreach ($children as $child)
    {
      $level = $child->getNestingLevel();
      if (empty($immediateChildren)) //first iteration
      {
        $immediateChildren = array($child);
      }
      else
      {
        if ($child->getNestingLevel() == $immediateChildren[0]->getNestingLevel())
        {
          $immediateChildren[] = $child;
        }
        elseif ($child->getNestingLevel() < $immediateChildren[0]->getNestingLevel())
        {
          //Re-assign immediateChildren to grandchilden
          $grandchildren = array_merge($grandchildren, $immediateChildren);
          //Set new children
          $immediateChildren = array($child);
        }
        else
        {
          $grandchildren[] = $child;
        }
      }
    }
    
    $lowestLevel = $immediateChildren[0]->getNestingLevel();
    
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
    
    //Put any grandchildren in a subpart
    if (!empty($grandchildren))
    {
      $subentity = $this->getEntityFactory()->createBaseEntity();
      $subentity->setNestingLevel($lowestLevel);
      $subentity->setChildren($grandchildren);
      array_unshift($immediateChildren, $subentity);
    }
    
    //Store the direct descendants
    $this->_immediateChildren = $immediateChildren;
    
    //Store all descendants
    $this->_children = $children;
    
    $this->_notifyFieldChanged('boundary', $this->getBoundary());
  }
  
  /**
   * Get all children nested inside this entity.
   * These are not just the immediate children, but all children.
   * @return Swift_Mime_MimeEntity[]
   */
  public function getChildren()
  {
    return $this->_children;
  }
  
  /**
   * Set a mime boundary for this mime part if other parts are to be added to it.
   * @param string $boundary
   */
  public function setBoundary($boundary)
  {
    if (preg_match(
      '/^[a-zA-Z0-9\'\(\)\+_\-,\.\/:=\?\ ]{0,69}[a-zA-Z0-9\'\(\)\+_\-,\.\/:=\?]$/D',
      $boundary
      ))
    {
      $this->_boundary = $boundary;
    }
    else
    {
      throw new Exception('Mime boundary set is not RFC 2046 compliant.');
    }
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
    
    $hasChildren = count($this->_children) > 0;
    
    //Append headers
    foreach ($this->_headers as $header)
    {
      if ($hasChildren
        && strtolower($header->getFieldName()) == 'content-transfer-encoding'
        && !in_array(
          strtolower($header->getFieldBody()),
          $this->_compositeSafeEncodings
          )
        )
      { //RFC 2045 says Content-Transfer-Encoding can only be 7bit, 8bit or
        // binary on composite media types
        continue;
      }
      $string .= $header->toString();
    }
    
    //Append body
    if (!$hasChildren && is_string($this->_stringBody))
    {
      $string .= "\r\n" . $this->_encoder->encodeString(
        $this->_stringBody, 0, $this->_maxLineLength
        );
    }
    
    //Nest children
    if (!empty($this->_immediateChildren))
    {
      foreach ($this->_immediateChildren as $child)
      {
        $string .= "\r\n--" . $this->getBoundary() . "\r\n";
        $string .= $child->toString();
      }
      $string .= "\r\n--" . $this->getBoundary() . "--\r\n";
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
  
  /**
   * Create a base entity which contains at most, the headers
   * Content-Type, Content-Transfer-Encoding, Content-ID and Description.
   * @return Swift_Mime_MimeEntity
   */
  public function createBaseEntity()
  {
    $headers = array();
    foreach ($this->_headers as $header)
    {
      if (in_array(
        strtolower($header->getFieldName()),
        array('content-type', 'content-transfer-encoding')))
      {
        $headers[] = clone $header;
      }
    }
    $entity = new self($headers, $this->_encoder);
    $entity->setContentType('text/plain');
    return $entity;
  }
  
  /**
   * Set a factory object which creates new mime entities (for nesting).
   * @param Swift_Mime_EntityFactory $entityFactory
   */
  public function setEntityFactory(Swift_Mime_EntityFactory $entityFactory)
  {
    $this->_entityFactory = $entityFactory;
  }
  
  /**
   * Get a factory object which creates new mime entities (for nesting).
   * @return Swift_Mime_EntityFactory
   */
  public function getEntityFactory()
  {
    if (!isset($this->_entityFactory))
    {
      return $this;
    }
    else
    {
      return $this->_entityFactory;
    }
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
  
  /**
   * Generates a new unique ID for this entity.
   * @return string
   * @access private
   */
  private function _generateId()
  {
    $idLeft = time() . '.' . uniqid();
    if (!empty($_SERVER['SERVER_NAME']))
    {
      $idRight = $_SERVER['SERVER_NAME'];
    }
    else
    {
      $idRight = 'swift.generated';
    }
    return $idLeft . '@' . $idRight;
  }
  
}
