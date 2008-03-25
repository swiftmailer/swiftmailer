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

//@require 'Swift/InputByteStream.php';
//@require 'Swift/OutputByteStream.php';
//@require 'Swift/KeyCache.php';
//@require 'Swift/Mime/MimeEntity.php';
//@require 'Swift/Mime/Header.php';
//@require 'Swift/Mime/ContentEncoder.php';

/**
 * A MIME entity, in a multipart message.
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
   * Model data for Header fields where the actual Header is not present.
   * @var array
   * @access private
   */
  private $_fieldModels = array();
  
  /**
   * Parameters set on header fields where the header is not present.
   * @var array
   * @access private
   */
  private $_parameters = array();
  
  /**
   * The preferred Content-type of this entity.
   * @var string
   * @access private
   */
  private $_preferredContentType;
  
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
   * The body of this entity, as a ByteStream.
   * @var Swift_OutputByteStream
   * @access private
   */
  private $_streamBody;
  
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
   * The level at which this entity nests.
   * @var int
   * @access private
   */
  private $_nestingLevel = self::LEVEL_SUBPART;
  
  /**
   * Encodings which are safe to use on composite media types.
   * @var string[]
   * @access private
   */
  private $_compositeSafeEncodings = array('7bit', '8bit', 'binary');
  
  /**
   * Maps nesting levels (integers) with the composite media types they
   * nest inside.
   * @var array
   * @see LEVEL_TOP, LEVEL_ATTACHMENT, LEVEL_EMBEDDED, LEVEL_SUBPART
   * @access private
   */
  private $_compositeRanges = array(
    'multipart/mixed' => array(self::LEVEL_TOP, self::LEVEL_ATTACHMENT),
    'multipart/related' => array(self::LEVEL_ATTACHMENT, self::LEVEL_EMBEDDED),
    'multipart/alternative' => array(self::LEVEL_EMBEDDED, self::LEVEL_SUBPART)
    );
  
  /**
   * The order of preference for any media types which appear in this entity.
   * @var array
   * @access private
   */
  private $_typeOrderPreference = array();
  
  /**
   * The KeyCache used when generating content.
   * @var Swift_KeyCache
   * @access private
   */
  private $_cache;
  
  /**
   * A key to access the cache with.
   * @var string
   * @access private
   */
  private $_cacheKey;
  
  /**
   * Creates a new SimpleMimeEntity with $headers and $encoder.
   * @param string[] $headers
   * @param Swift_Mime_ContentEncoder $encoder
   * @param Swift_KeyCache $cache
   */
  public function __construct(array $headers,
    Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache)
  {
    $this->_cacheKey = uniqid(microtime(true), true);
    $this->_cache = $cache;
    $this->setHeaders($headers);
    $this->setEncoder($encoder);
    $this->setId($this->_generateId());
    $this->setContentType('text/plain');
    $this->setChildren(array());
  }
  
  /**
   * Set the level at which this entity nests.
   * A lower value is closer to the top (i.e. the message itself is zero (0)),
   * and a higher value is nested deeper in.
   * Returns a reference to itself for fluid interface.
   * @param int $level
   * @return Swift_Mime_SimpleMimeEntity
   * @see Swift_Mime_MimeEntity::LEVEL_SUBPART
   * @see Swift_Mime_MimeEntity::LEVEL_ATTACHMENT
   * @see Swift_Mime_MimeEntity::LEVEL_EMBEDDED
   */
  public function setNestingLevel($level)
  {
    $this->_nestingLevel = $level;
    return $this;
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
   * Returns a reference to itself for fluid interface.
   * @param Swift_Mime_Header[] $headers
   * @return Swift_Mime_SimpleMimeEntity
   */
  public function setHeaders(array $headers)
  {
    $this->_headers = $headers;
    $this->_cache->clearKey($this->_cacheKey, 'headers');
    return $this;
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
   * Get a single Header with $name.
   * @param string $name
   * @return Swift_Mime_Header
   */
  public function getHeader($name)
  {
    $lname = strtolower($name);
    foreach ($this->_headers as $header)
    {
      if ($lname == strtolower($header->getFieldName()))
      {
        return $header;
      }
    }
  }
  
  /**
   * Get all headers with $name, in an array.
   * @param string $name
   * @return Swift_Mime_Header[]
   */
  public function getHeaderCollection($name)
  {
    $collection = array();
    $lname = strtolower($name);
    foreach ($this->_headers as $header)
    {
      if ($lname == strtolower($header->getFieldName()))
      {
        $collection[] = $header;
      }
    }
    return $collection;
  }
  
  /**
   * Add a Header to the existing list of Headers.
   * @param Swift_Mime_Header $header
   */
  public function addHeader(Swift_Mime_Header $header)
  {
    $this->_headers[] = $header;
  }
  
  /**
   * Remove a Header from the existing list of Headers based on its name.
   * @param string $name
   */
  public function removeHeader($name)
  {
    $lname = strtolower($name);
    foreach ($this->_headers as $k => $header)
    {
      if ($lname == strtolower($header->getFieldName()))
      {
        unset($this->_headers[$k]);
      }
    }
  }
  
  /**
   * Set the Encoder used for transportation of this entity.
   * Returns a reference to itself for fluid interface.
   * @param Swift_Mime_ContentEncoder $encoder
   * @return Swift_Mime_SimpleMimeEntity
   */
  public function setEncoder(Swift_Mime_ContentEncoder $encoder)
  {
    $this->_encoder = $encoder;
    $this->_setHeaderModel('content-transfer-encoding', $encoder->getName());
    $this->_notifyEncoderChanged($encoder);
    $this->_cache->clearAll($this->_cacheKey);
    return $this;
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
   * Returns a reference to itself for fluid interface.
   * @param string $contentType
   * @return Swift_Mime_SimpleMimeEntity
   */
  public function setContentType($contentType)
  {
    $ltype = strtolower($contentType);
    $this->_preferredContentType = $contentType;
    $str = (array) sscanf($ltype, '%[^/]');
    if ('multipart' == array_shift($str)
      || empty($this->_children))
    {
      $this->_setHeaderModel('content-type', $contentType);
      $this->_cache->clearKey($this->_cacheKey, 'headers');
    }
    return $this;
  }
  
  /**
   * Get the content-type of this entity.
   * @return string
   */
  public function getContentType()
  {
    return $this->_getHeaderModel('content-type');
  }
  
  /**
   * Set the Content-ID.
   * @param string $id
   */
  public function setId($id)
  {
    return $this->_setHeaderModel('content-id', $id);
  }
  
  /**
   * Get the Content-ID.
   * @return string
   */
  public function getId()
  {
    $model = (array) $this->_getHeaderModel('content-id');
    return current($model);
  }
  
  /**
   * Set an optional description for this mime entity.
   * Returns a reference to itself for fluid interface.
   * @param string $description
   * @return Swift_Mime_SimpleMimeEntity
   */
  public function setDescription($description)
  {
    return $this->_setHeaderModel('content-description', $description);
  }
  
  /**
   * Get the optional description this mime entity, or null of not set.
   * @return string
   */
  public function getDescription()
  {
    return $this->_getHeaderModel('content-description');
  }
  
  /**
   * Set the maximum length before lines are wrapped in this entity.
   * Returns a reference to itself for fluid interface.
   * @param int $length
   * @return Swift_Mime_SimpleMimeEntity
   */
  public function setMaxLineLength($length)
  {
    $this->_maxLineLength = (int) $length;
    $this->_cache->clearKey($this->_cacheKey, 'body');
    return $this;
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
   * A selective setBody() which accepts either a string or a ByteStream for
   * convenience.
   * @param mixed $body
   */
  public function setBody($body)
  {
    if ($body instanceof Swift_OutputByteStream)
    {
      $this->setBodyAsByteStream($body);
    }
    else
    {
      $this->setBodyAsString($body);
    }
    return $this;
  }
  
  /**
   * Set the body of this entity as a string.
   * Returns a reference to itself for fluid interface.
   * @param string $string
   * @return Swift_Mime_SimpleMimeEntity
   */
  public function setBodyAsString($stringBody)
  {
    $this->_stringBody = $stringBody;
    $this->_streamBody = null;
    $this->_cache->clearKey($this->_cacheKey, 'body');
    return $this;
  }
  
  /**
   * Get the body content of this entity as a string.
   * Returns NULL if no body has been set.
   * @return string
   */
  public function getBodyAsString()
  {
    if (isset($this->_stringBody))
    {
      return $this->_stringBody;
    }
    elseif (isset($this->_streamBody))
    {
      $this->_streamBody->setReadPointer(0);
      $string = '';
      while (false !== $bytes = $this->_streamBody->read(8192))
      {
        $string .= $bytes;
      }
      $this->_streamBody->setReadPointer(0);
      return $string;
    }
  }
  
  /**
   * Set the body of this entity as a ByteStream.
   * Returns a reference to itself for fluid interface.
   * @param Swift_OutputByteStream $stream
   * @return Swift_Mime_SimpleMimeEntity
   */
  public function setBodyAsByteStream(Swift_OutputByteStream $streamBody)
  {
    $this->_streamBody = $streamBody;
    $this->_stringBody = null;
    $this->_cache->clearKey($this->_cacheKey, 'body');
    return $this;
  }
  
  /**
   * Attach an array of other entities to this entity.
   * These will be re-ordered according to their nesting levels.
   * Returns a reference to itself for fluid interface.
   * @param Swift_Mime_MimeEntity[] $children
   * @return Swift_Mime_SimpleMimeEntity
   */
  public function setChildren(array $children)
  {
    $immediateChildren = array();
    $grandchildren = array();
    $newContentType = $this->_preferredContentType;
    
    foreach ($children as $child)
    {
      $level = $child->getNestingLevel();
      if (empty($immediateChildren)) //first iteration
      {
        $immediateChildren = array($child);
      }
      else
      {
        $nextLevel = $immediateChildren[0]->getNestingLevel();
        if ($nextLevel == $level)
        {
          $immediateChildren[] = $child;
        }
        elseif ($level < $nextLevel)
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
    
    if (!empty($immediateChildren))
    {
      $lowestLevel = $immediateChildren[0]->getNestingLevel();
      //Determine which composite media type is needed to accomodate the
      // immediate children
      foreach ($this->_compositeRanges as $mediaType => $range)
      {
        if ($lowestLevel > $range[0]
          && $lowestLevel <= $range[1])
        {
          $newContentType = $mediaType;
          break;
        }
      }
      //Put any grandchildren in a subpart
      if (!empty($grandchildren))
      {
        $subentity = $this->_createChild();
        $subentity->setNestingLevel($lowestLevel);
        $subentity->setChildren($grandchildren);
        array_unshift($immediateChildren, $subentity);
      }
    }
    
    //Store the direct descendants
    $this->_immediateChildren = $immediateChildren;
    //Store all descendants
    $this->_children = $children;
    //Update the content-type
    $this->_overrideContentType($newContentType);
    //Make sure the boundary is integral
    $this->_refreshBoundary();
    //Logically order the parts if conclusively possible
    $this->_repairOrdering();
    
    $this->_cache->clearKey($this->_cacheKey, 'headers');
    
    return $this;
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
   * Returns a reference to itself for fluid interface.
   * @param string $boundary
   * @return Swift_Mime_SimpleMimeEntity
   */
  public function setBoundary($boundary)
  {
    if (preg_match(
      '/^[a-z0-9\'\(\)\+_\-,\.\/:=\?\ ]{0,69}[a-z0-9\'\(\)\+_\-,\.\/:=\?]$/Di',
      $boundary))
    {
      $this->_boundary = $boundary;
      if (!empty($this->_children))
      {
        $this->_setHeaderParameter('content-type', 'boundary', $boundary);
      }
      else
      {
        $this->_setHeaderParameter('content-type', 'boundary', null);
      }
    }
    else
    {
      trigger_error('Mime boundary set is not RFC 2046 compliant.');
    }
    $this->_cache->clearAll($this->_cacheKey);
    return $this;
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
   * Get the integer ranges in which different composite media types are used.
   * The return array uses the media type as the key, with min and max nesting
   * levels in an array.
   * @return array
   */
  public function getCompositeRanges()
  {
    return $this->_compositeRanges;
  }
  
  /**
   * Set the valid ranges for nested composite types.
   * @param array $ranges
   */
  public function setCompositeRanges(array $ranges)
  {
    $this->_compositeRanges = $ranges;
    $this->_cache->clearAll($this->_cacheKey);
  }
  
  /**
   * Get the ordering of types in this media.
   * Types are mapped as MIME type/subtype, corresponding to a
   * numeric value.  The lower the value, the less preferable it is.
   * @return array
   */
  public function getTypeOrderPreference()
  {
    return $this->_typeOrderPreference;
  }
  
  /**
   * Set the order preference of any types in this media.
   * @param array $order
   */
  public function setTypeOrderPreference(array $order)
  {
    $this->_typeOrderPreference = $order;
    $this->setChildren($this->_children);
  }
  
  /**
   * Get this entire entity in its string form.
   * @return string
   */
  public function toString()
  {
    $string = '';
    $hasChildren = count($this->_children) > 0;
    $requiredFields = $this->getRequiredFields();
    
    //Append headers
    if (!$this->_cache->hasKey($this->_cacheKey, 'headers'))
    {
      foreach ($this->_headers as $header)
      {
        if ($header->getFieldBody() == ''
          && !in_array(strtolower($header->getFieldName()), $requiredFields))
        { //Empty fields need not be displayed
          continue;
        }
    
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
        $this->_cache->setString($this->_cacheKey, 'headers', $header->toString(),
          Swift_KeyCache::MODE_APPEND
          );
      }
    }
    
    $string .= $this->_cache->getString($this->_cacheKey, 'headers');
    
    //Append body
    if (!$hasChildren && (isset($this->_stringBody) || isset($this->_streamBody)))
    {
      if (!$this->_cache->hasKey($this->_cacheKey, 'body'))
      {
        $this->_cache->setString($this->_cacheKey, 'body',
          "\r\n" . $this->_encodeStringBody(), Swift_KeyCache::MODE_APPEND
          );
      }
      $string .= $this->_cache->getString($this->_cacheKey, 'body');
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
   * The ByteStream will be appended to (it will not be flushed first).
   * @param Swift_InputByteStream $is to write to
   */
  public function toByteStream(Swift_InputByteStream $is)
  {
    $hasChildren = count($this->_children) > 0;
    $requiredFields = $this->getRequiredFields();
    
    //Append headers
    if (!$this->_cache->hasKey($this->_cacheKey, 'headers'))
    {
      foreach ($this->_headers as $header)
      {
        if ($header->getFieldBody() == ''
          && !in_array(strtolower($header->getFieldName()), $requiredFields))
        { //Empty fields need not be displayed
          continue;
        }
      
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
        $this->_cache->setString($this->_cacheKey, 'headers', $header->toString(),
          Swift_KeyCache::MODE_APPEND
          );
      }
    }
    
    $this->_cache->exportToByteStream($this->_cacheKey, 'headers', $is);
    
    //Append body
    if (!$hasChildren)
    {
      if (!$this->_cache->hasKey($this->_cacheKey, 'body'))
      {
        if (is_string($this->_stringBody))
        {
          $this->_cache->setString($this->_cacheKey, 'body',
            "\r\n" . $this->_encodeStringBody(), Swift_KeyCache::MODE_WRITE
            );
        }
        elseif (isset($this->_streamBody))
        {
          $this->_cache->setString($this->_cacheKey, 'body', "\r\n",
            Swift_KeyCache::MODE_WRITE
            );
          $this->_streamBody->setReadPointer(0);
          $this->_encodeByteStreamBody(
            $this->_cache->getInputByteStream($this->_cacheKey, 'body')
            );
          $this->_streamBody->setReadPointer(0);
        }
      }
      $this->_cache->exportToByteStream($this->_cacheKey, 'body', $is);
    }
    
    //Nest children
    if (!empty($this->_immediateChildren))
    {
      foreach ($this->_immediateChildren as $child)
      {
        $is->write("\r\n--" . $this->getBoundary() . "\r\n");
        $child->toByteStream($is); //Get the child to append it's own data
      }
      $is->write("\r\n--" . $this->getBoundary() . "--\r\n");
    }
  }
  
  /**
   * Get a list of (lowercased) header field names which will always be displayed.
   * @return string[]
   */
  public function getRequiredFields()
  {
    return array();
  }
  
  /**
   * Flush the cache so the message is forced to be re-rendered.
   */
  public function flushCache()
  {
    $this->_cache->clearAll($this->_cacheKey);
  }
  
  /**
   * Notify this observer that the observed entity's ContentEncoder has changed.
   * @param Swift_Mime_ContentEncoder $encoder
   */
  public function encoderChanged(Swift_Mime_ContentEncoder $encoder)
  {
    $this->_notifyEncoderChanged($encoder);
  }
  
  /**
   * Notify this observer that the observed entity's charset has changed.
   * @param string $charset
   */
  public function charsetChanged($charset)
  {
    $this->_notifyCharsetChanged($charset);
  }
  
  // -- Protected methods
  
  /**
   * Set Model data which belongs in the Headers of the Entity.
   * @param string $field
   * @param mixed $model
   * @return Swift_Mime_SimpleMimeEntity
   * @access protected
   */
  protected function _setHeaderModel($field, $model)
  {
    if ($header = $this->getHeader($field))
    {
      $header->setFieldBodyModel($model);
    }
    else
    {
      $field = strtolower($field);
      $this->_fieldModels[$field] = $model;
    }
    return $this;
  }
  
  /**
   * Get Model data from the Headers of the Entity.
   * @return mixed
   * @access protected
   */
  protected function _getHeaderModel($field)
  {
    if ($header = $this->getHeader($field))
    {
      return $header->getFieldBodyModel();
    }
    else
    {
      $field = strtolower($field);
      return array_key_exists($field, $this->_fieldModels)
        ? $this->_fieldModels[$field]
        : null;
    }
  }
  
  /**
   * Set a parameter in a Header.
   * @param string $field
   * @param string $parameter
   * @param string $value
   * @access protected
   */
  protected function _setHeaderParameter($field, $parameter, $value)
  {
    if (($header = $this->getHeader($field))
      && ($header instanceof Swift_Mime_ParameterizedHeader))
    {
      $header->setParameter($parameter, $value);
    }
    else
    {
      $field = strtolower($field);
      if (!isset($this->_parameters[$field]))
      {
        $this->_parameters[$field] = array();
      }
      $this->_parameters[$field][$parameter] = $value;
    }
    return $this;
  }
  
  /**
   * Get a parameter in a Header.
   * @param string $field
   * @param string $parameter
   * @access protected
   */
  protected function _getHeaderParameter($field, $parameter)
  {
    if (($header = $this->getHeader($field))
      && ($header instanceof Swift_Mime_ParameterizedHeader))
    {
      $value = $header->getParameter($parameter);
    }
    else
    {
      $field = strtolower($field);
      if (!isset($this->_parameters[$field]))
      {
        $this->_parameters[$field] = array();
      }
      $value = isset($this->_parameters[$field][$parameter])
        ? $this->_parameters[$field][$parameter]
        : null
        ;
    }
    return $value;
  }
  
  /**
   * Create a base entity which allow for nesting.
   * @return Swift_Mime_MimeEntity
   * @access protected
   */
  protected function _createChild()
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
    $entity = new self($headers, $this->_encoder, $this->_cache);
    return $entity;
  }
  
  /**
   * Get the content-type which was set by the user, not by the system.
   * @return string
   * @access protected
   */
  protected function _getPreferredContentType()
  {
    return $this->_preferredContentType;
  }
  
  /**
   * Get the encoded body as a string.
   * @return string
   * @access protected
   */
  protected function _encodeStringBody()
  {
    return $this->_encoder->encodeString(
      $this->getBodyAsString(), 0, $this->_maxLineLength
      );
  }
  
  /**
   * Write the encoded body to $is.
   * @param Swift_InputByteStream $is
   * @access protected
   */
  protected function _encodeByteStreamBody(Swift_InputByteStream $is)
  {
    $this->_encoder->encodeByteStream(
      $this->_streamBody, $is, 0, $this->_maxLineLength
      );
  }
  
  /**
   * Get the body of this entity as the string it was set with.
   * Returns null if not set.
   * @return string
   * @access protected
   */
  protected function _getStringBody()
  {
    return $this->_stringBody;
  }
  
  /**
   * Get the body of this entity as the ByteStream it was set with.
   * Returns null if not set.
   * @return Swift_OutputByteStream
   * @access protected
   */
  protected function _getStreamBody()
  {
    return $this->_streamBody;
  }
  
  /**
   * Forecfully override the content type.
   * @param string $contentType
   * @access protected
   */
  protected function _overrideContentType($contentType)
  {
    $this->_setHeaderModel('content-type', $contentType);
    $this->_cache->clearKey($this->_cacheKey, 'headers');
  }
  
  /**
   * Get the KeyCache instance.
   * @return Swift_KeyCache
   * @access protected
   */
  protected function _getCache()
  {
    return $this->_cache;
  }
  
  /**
   * Get the key to access the KeyCache with.
   * @return string
   * @access protected
   */
  protected function _getCacheKey()
  {
    return $this->_cacheKey;
  }
  
  // -- Private methods
  
  /**
   * Generates a new unique ID for this entity.
   * @return string
   * @access private
   */
  private function _generateId()
  {
    $idLeft = time() . '.' . uniqid();
    $idRight = !empty($_SERVER['SERVER_NAME'])
      ? $_SERVER['SERVER_NAME']
      : 'swift.generated';
    return $idLeft . '@' . $idRight;
  }
  
  /**
   * Inform observers of the currently active boundary.
   * @access private
   */
  private function _refreshBoundary()
  {
    $this->setBoundary($this->getBoundary());
  }
  
  /**
   * User defined callback for sorting children when they are nested.
   * This helps to ensure that all children appear in a logical order.
   * @param object $a
   * @param object $b
   * @return int
   * @access protected
   */
  private function _sortChildren($a, $b)
  {
    $typePrefs = array();
    $types = array(
      strtolower($a->getContentType()),
      strtolower($b->getContentType())
      );
    foreach ($types as $type)
    {
      $typePrefs[] = (array_key_exists($type, $this->_typeOrderPreference))
        ? $this->_typeOrderPreference[$type]
        : (max($this->_typeOrderPreference) + 1);
    }
    return ($typePrefs[0] >= $typePrefs[1]) ? 1 : -1;
  }
  
  /**
   * Logically order the children in this entity of getSortOrderPreference()
   * returns a conclusive result.
   * @access private
   */
  private function _repairOrdering()
  {
    $shouldSort = true;
    foreach ($this->_immediateChildren as $child)
    {
      if (!array_key_exists(
        strtolower($child->getContentType()),
        $this->_typeOrderPreference))
      {
        $shouldSort = false;
        break;
      }
    }
    //Sort in order of preference, if there is one
    if ($shouldSort)
    {
      usort($this->_immediateChildren, array($this, '_sortChildren'));
    }
  }
  
  /**
   * Notify all children that the Encoder has been changed.
   */
  private function _notifyEncoderChanged(Swift_Mime_ContentEncoder $encoder)
  {
    foreach ($this->_children as $child)
    {
      $child->encoderChanged($encoder);
    }
  }
  
  /**
   * Notify all children and encoder that the charset has been changed.
   */
  private function _notifyCharsetChanged($charset)
  {
    $this->_encoder->charsetChanged($charset);
    foreach ($this->_children as $child)
    {
      $child->charsetChanged($charset);
    }
  }
  
  /**
   * Destructor.
   */
  public function __destruct()
  {
    $this->_cache->clearAll($this->_cacheKey);
  }
  
}
