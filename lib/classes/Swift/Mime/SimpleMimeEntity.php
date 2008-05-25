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

//@require 'Swift/Mime/HeaderSet.php';
//@require 'Swift/OutputByteStream.php';
//@require 'Swift/Mime/ContentEncoder.php';
//@require 'Swift/KeyCache.php';

/**
 * A MIME entity, in a multipart message.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_SimpleMimeEntity implements Swift_Mime_MimeEntity
{
  
  private $_headers;
  private $_body;
  private $_encoder;
  private $_boundary;
  private $_compositeRanges = array(
    'multipart/mixed' => array(self::LEVEL_TOP, self::LEVEL_MIXED),
    'multipart/related' => array(self::LEVEL_MIXED, self::LEVEL_RELATED),
    'multipart/alternative' => array(self::LEVEL_RELATED, self::LEVEL_ALTERNATIVE)
    );
  private $_nestingLevel = self::LEVEL_ALTERNATIVE;
  private $_cache;
  private $_immediateChildren = array();
  private $_children = array();
  private $_maxLineLength = 78;
  private $_typeOrderPreference = array();
  private $_id;
  private $_cacheKey;
  
  protected $_userContentType;
  
  public function __construct(Swift_Mime_HeaderSet $headers,
    Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache)
  {
    $this->_cacheKey = uniqid();
    $this->_headers = $headers;
    $this->setEncoder($encoder);
    $this->_cache = $cache;
    $this->_headers->defineOrdering(
      array('Content-Type', 'Content-Transfer-Encoding')
      );
    $this->_generateId();
  }
  
  public function getHeaders()
  {
    return $this->_headers;
  }
  
  public function getNestingLevel()
  {
    return $this->_nestingLevel;
  }
  
  public function getContentType()
  {
    return $this->_getHeaderFieldModel('Content-Type');
  }
  
  public function setContentType($type)
  {
    $this->_setContentTypeInHeaders($type);
    $this->_userContentType = $type;
    return $this;
  }
  
  public function getId()
  {
    return $this->_headers->has($this->_getIdField())
      ? current((array) $this->_getHeaderFieldModel($this->_getIdField()))
      : $this->_id;
  }
  
  public function setId($id)
  {
    if (!$this->_setHeaderFieldModel($this->_getIdField(), $id))
    {
      $this->_headers->addIdHeader($this->_getIdField(), $id);
    }
    $this->_id = $id;
    return $this;
  }
  
  public function getDescription()
  {
    return $this->_getHeaderFieldModel('Content-Description');
  }
  
  public function setDescription($description)
  {
    if (!$this->_setHeaderFieldModel('Content-Description', $description))
    {
      $this->_headers->addTextHeader('Content-Description', $description);
    }
    return $this;
  }
  
  public function getMaxLineLength()
  {
    return $this->_maxLineLength;
  }
  
  public function setMaxLineLength($length)
  {
    $this->_maxLineLength = $length;
    return $this;
  }
  
  public function getChildren()
  {
    return $this->_children;
  }
  
  public function setChildren(array $children) //TODO: Try to refactor this logic
  {
    $immediateChildren = array();
    $grandchildren = array();
    $newContentType = $this->_userContentType;
    
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
        $subentity->_setNestingLevel($lowestLevel);
        $subentity->setChildren($grandchildren);
        array_unshift($immediateChildren, $subentity);
      }
    }
    
    $this->_immediateChildren = $immediateChildren;
    $this->_children = $children;
    $this->_setContentTypeInHeaders($newContentType);
    $this->_fixHeaders();
    $this->_sortChildren();
    
    return $this;
  }
  
  public function getBody()
  {
    return ($this->_body instanceof Swift_OutputByteStream)
      ? $this->_readStream($this->_body)
      : $this->_body;
  }
  
  public function setBody($body)
  {
    $this->_body = $body;
    return $this;
  }
  
  public function getEncoder()
  {
    return $this->_encoder;
  }
  
  public function setEncoder(Swift_Mime_ContentEncoder $encoder)
  {
    $this->_encoder = $encoder;
    $this->_setEncoding($encoder->getName());
    $this->_notifyEncoderChanged($encoder);
    return $this;
  }
  
  public function getBoundary()
  {
    if (!isset($this->_boundary))
    {
      $this->_boundary = '_=_swift_v4_' . time() . uniqid() . '_=_';
    }
    return $this->_boundary;
  }
  
  public function setBoundary($boundary)
  {
    $this->_assertValidBoundary($boundary);
    $this->_boundary = $boundary;
    return $this;
  }
  
  public function charsetChanged($charset)
  {
    $this->_notifyCharsetChanged($charset);
  }
  
  public function encoderChanged(Swift_Mime_ContentEncoder $encoder)
  {
    $this->_notifyEncoderChanged($encoder);
  }
  
  public function setTypeOrderPreference(array $order)
  {
    $this->_typeOrderPreference = $order;
    $this->setChildren($this->_children);
  }
  
  public function toString()
  {
    $string = $this->_headers->toString();
    if (isset($this->_body) && empty($this->_immediateChildren))
    {
      if ($this->_cache->hasKey($this->_cacheKey, 'body'))
      {
        $body = $this->_cache->getString($this->_cacheKey, 'body');
      }
      else
      {
        $body = "\r\n" . $this->_encoder->encodeString($this->getBody(), 0,
          $this->getMaxLineLength()
          );
        $this->_cache->setString($this->_cacheKey, 'body', $body,
          Swift_KeyCache::MODE_WRITE
          );
      }
      $string .= $body;
    }
    
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
  
  public function toByteStream(Swift_InputByteStream $is)
  {
    $is->write($this->_headers->toString());
    if (empty($this->_immediateChildren))
    {
      if (isset($this->_body))
      {
        //if ($this->_cache->hasKey($this->_cacheKey, 'body'))
        //{
        //  $this->_cache->exportToByteStream($this->_cacheKey, 'body', $is);
        //}
        //else
        //{
          $is->write("\r\n",
            $this->_cache->getInputByteStream($this->_cacheKey, 'body')
            );
          if ($this->_body instanceof Swift_OutputByteStream)
          {
            $this->_encoder->encodeByteStream($this->_body,
              $this->_cache->getInputByteStream($this->_cacheKey, 'body', $is),
              0, $this->getMaxLineLength()
              );
          }
          else
          {
            $is->write($this->_encoder->encodeString($this->getBody(), 0,
              $this->getMaxLineLength()),
              $this->_cache->getInputByteStream($this->_cacheKey, 'body')
              );
          }
        //}
      }
    }
    
    if (!empty($this->_immediateChildren))
    {
      foreach ($this->_immediateChildren as $child)
      {
        $is->write("\r\n--" . $this->getBoundary() . "\r\n");
        $child->toByteStream($is);
      }
      $is->write("\r\n--" . $this->getBoundary() . "--\r\n");
    }
  }
  
  // -- Protected methods
  
  protected function _getIdField()
  {
    return 'Content-ID';
  }
  
  protected function _getHeaderFieldModel($field)
  {
    if ($this->_headers->has($field))
    {
      return $this->_headers->get($field)->getFieldBodyModel();
    }
  }
  
  protected function _setHeaderFieldModel($field, $model)
  {
    if ($this->_headers->has($field))
    {
      $this->_headers->get($field)->setFieldBodyModel($model);
      return true;
    }
    else
    {
      return false;
    }
  }
  
  protected function _getHeaderParameter($field, $parameter)
  {
    if ($this->_headers->has($field))
    {
      return $this->_headers->get($field)->getParameter($parameter);
    }
  }
  
  protected function _setHeaderParameter($field, $parameter, $value)
  {
    if ($this->_headers->has($field))
    {
      $this->_headers->get($field)->setParameter($parameter, $value);
      return true;
    }
    else
    {
      return false;
    }
  }
  
  protected function _fixHeaders()
  {
    if (count($this->_immediateChildren))
    {
      $this->_setHeaderParameter('Content-Type', 'boundary',
        $this->getBoundary()
        );
      $this->_headers->remove('Content-Transfer-Encoding');
    }
    else
    {
      $this->_setHeaderParameter('Content-Type', 'boundary', null);
      $this->_setEncoding($this->_encoder->getName());
    }
  }
  
  protected function _getCache()
  {
    return $this->_cache;
  }
  
  protected function _clearCache()
  {
    $this->_cache->clearKey($this->_cacheKey, 'body');
  }
  
  // -- Private methods
  
  private function _readStream(Swift_OutputByteStream $os)
  {
    $string = '';
    while (false !== $bytes = $os->read(8192))
    {
      $string .= $bytes;
    }
    return $string;
  }
  
  private function _setEncoding($encoding)
  {
    if (!$this->_setHeaderFieldModel('Content-Transfer-Encoding', $encoding))
    {
      $this->_headers->addTextHeader('Content-Transfer-Encoding', $encoding);
    }
  }
  
  private function _generateId()
  {
    $idLeft = time() . '.' . uniqid();
    $idRight = !empty($_SERVER['SERVER_NAME'])
      ? $_SERVER['SERVER_NAME']
      : 'swift.generated';
    $this->_id = $idLeft . '@' . $idRight;
  }
  
  private function _assertValidBoundary($boundary)
  {
    if (!preg_match(
      '/^[a-z0-9\'\(\)\+_\-,\.\/:=\?\ ]{0,69}[a-z0-9\'\(\)\+_\-,\.\/:=\?]$/Di',
      $boundary))
    {
      throw new Exception('Mime boundary set is not RFC 2046 compliant.');
    }
  }
  
  private function _setContentTypeInHeaders($type)
  {
    if (!$this->_setHeaderFieldModel('Content-Type', $type))
    {
      $this->_headers->addParameterizedHeader('Content-Type', $type);
    }
  }
  
  private function _setNestingLevel($level)
  {
    $this->_nestingLevel = $level;
  }
  
  private function _createChild()
  {
    return new self($this->_headers->newInstance(),
      $this->_encoder, $this->_cache);
  }
  
  private function _notifyEncoderChanged(Swift_Mime_ContentEncoder $encoder)
  {
    foreach ($this->_immediateChildren as $child)
    {
      $child->encoderChanged($encoder);
    }
  }
  
  private function _notifyCharsetChanged($charset)
  {
    $this->_encoder->charsetChanged($charset);
    $this->_headers->charsetChanged($charset);
    foreach ($this->_immediateChildren as $child)
    {
      $child->charsetChanged($charset);
    }
  }
  
  private function _sortChildren()
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
      usort($this->_immediateChildren, array($this, '_childSortAlgorithm'));
    }
  }
  
  private function _childSortAlgorithm($a, $b)
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
  
  // -- Destructor
  
  /**
   * Empties it's own contents from the cache.
   */
  public function __destruct()
  {
    $this->_cache->clearAll($this->_cacheKey);
  }
  
}
