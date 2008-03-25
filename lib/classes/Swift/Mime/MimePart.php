<?php

/*
 A Mime part in Swift Mailer.
 
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
//@require 'Swift/InputByteStream.php';
//@require 'Swift/KeyCache.php';

/**
 * A MIME part, in a multipart message.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_MimePart extends Swift_Mime_SimpleMimeEntity
{
  
  /**
   * The charset which the user specified, even if it can't be used.
   * @var string
   * @access private
   */
  private $_preferredCharset;
  
  /**
   * The format the user specified, even if it can't be used.
   * @var string
   * @access private
   */
  private $_preferredFormat;
  
  /**
   * DelSp as specified by the user, even if it not being used.
   * @var boolean
   * @access private
   */
  private $_preferredDelSp;
  
  /**
   * Creates a new MimePart with $headers and $encoder.
   * @param string[] $headers
   * @param Swift_Mime_ContentEncoder $encoder
   * @param Swift_KeyCache $cache
   * @param string $charset, optional.
   */
  public function __construct(array $headers,
    Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache, $charset = null)
  {
    parent::__construct($headers, $encoder, $cache);
    $this->setNestingLevel(self::LEVEL_SUBPART);
    $this->setContentType('text/plain');
    if (!is_null($charset))
    {
      $this->setCharset($charset);
    }
    $this->setTypeOrderPreference(array(
      'text/plain' => 1,
      'text/html' => 2
      ));
  }
  
  /**
   * Set the charset of this mime part.
   * @param string
   */
  public function setCharset($charset)
  {
    $this->_preferredCharset = $charset;
    if (count($this->getChildren()) == 0)
    {
      $this->_setHeaderParameter('content-type', 'charset', $charset);
      $this->_getCache()->clearAll($this->_getCacheKey());
    }
    parent::charsetChanged($charset);
    return $this;
  }
  
  /**
   * Get the charset of this mime part.
   * A NULL return value indicates that the encoding is unspecified
   * This defaults to unspecified, which according to RFC 2046 is US-ASCII.
   * @return string
   */
  public function getCharset()
  {
    return $this->_getHeaderParameter('content-type', 'charset');
  }
  
  /**
   * Set the format of this mime part (i.e. fixed, flowed or NULL).
   * @param string
   */
  public function setFormat($format)
  {
    $this->_preferredFormat = $format;
    if (count($this->getChildren()) == 0)
    {
      $this->_setHeaderParameter('content-type', 'format', $format);
      $this->_getCache()->clearKey($this->_getCacheKey(), 'headers');
    }
    return $this;
  }
  
  /**
   * Get the format of this mime part.
   * A NULL return value indicates that the format is unspecified
   * This defaults to unspecified, which according to RFC 3676 is 'fixed'.
   * @return string
   */
  public function getFormat()
  {
    return $this->_getHeaderParameter('content-type', 'format');
  }
  
  /**
   * Set whether delsp should be used, as per RFC 3676.
   * @param boolean $delSp
   */
  public function setDelSp($delSp)
  {
    $value = $delSp ? 'yes' : null;
    $this->_preferredDelSp = $delSp;
    if (count($this->getChildren()) == 0)
    {
      $this->_setHeaderParameter('content-type', 'delsp', $value);
      $this->_getCache()->clearKey($this->_getCacheKey(), 'headers');
    }
    return $this;
  }
  
  /**
   * Get the whather delsp is used as per RFC 3676.
   * This defaults to false ('no').
   * @return boolean
   */
  public function getDelSp()
  {
    $value = $this->_getHeaderParameter('content-type', 'delsp');
    return (strtolower($value) == 'yes');
  }
  
  /**
   * Overridden from SimpleMimeEntity to fix conflicts.
   * @param Swift_Mime_MimeEntity[] $children
   * @return Swift_Mime_SimpleMimeEntity
   */
  public function setChildren(array $children)
  {
    parent::setChildren($children);
    if (!empty($children))
    {
      $this->_overrideCharset(null);
      $this->_overrideFormat(null);
      $this->_overrideDelSp(null);
    }
    else
    {
      $this->setCharset($this->_preferredCharset);
      $this->setFormat($this->_preferredFormat);
      $this->setDelSp($this->_preferredDelSp);
    }
    return $this;
  }
  
  /**
   * Get this entire message in its string form.
   * @return string
   */
  public function toString()
  {
    $children = $this->getChildren();
    $modified = $this->_moveBody($children);
    $string = parent::toString();
    if ($modified)
    {
      $this->setChildren($children);
    }
    return $string;
  }
  
  /**
   * Get this entire message as a ByteStream.
   * The ByteStream will be appended to (it will not be flushed first).
   * @param Swift_InputByteStream $is to write to
   */
  public function toByteStream(Swift_InputByteStream $stream)
  {
    $children = $this->getChildren();
    $modified = $this->_moveBody($children);
    parent::toByteStream($stream);
    if ($modified)
    {
      $this->setChildren($children);
    }
  }
  
  /**
   * Overrides the superclass implementation to update this part's charset.
   * @param string $charset
   */
  public function charsetChanged($charset)
  {
    $this->setCharset($charset);
  }
  
  // -- Protected methods
  
  /**
   * Create a new child for nesting.
   * Due to intricices involved (i.e. typing) this is declared final.
   * @return Swift_Mime_MimeEntity
   * @access protected
   */
  final protected function _createChild()
  {
    $headers = array();
    foreach ($this->getHeaders() as $header)
    {
      if (in_array(
        strtolower($header->getFieldName()),
        array('content-type', 'content-transfer-encoding')))
      {
        $headers[] = clone $header;
      }
    }
    $part = new self($headers, $this->getEncoder(), $this->_getCache());
    return $part;
  }
  
  /**
   * Forcefully override the character set of this mime part.
   * @param string $charset
   * @access protected
   */
  protected function _overrideCharset($charset)
  {
    $this->_setHeaderParameter('content-type', 'charset', $charset);
  }
  
  /**
   * Forcefully override the format of this mime part.
   * @param string $format
   * @access protected
   */
  protected function _overrideFormat($format)
  {
    $this->_setHeaderParameter('content-type', 'format', $format);
  }
  
  /**
   * Forcefully override delsp in this mime part.
   * @param string $delSp
   * @access protected
   */
  protected function _overrideDelSp($delSp)
  {
    $this->_setHeaderParameter('content-type', 'delsp', $delSp);
  }
  
  /**
   * Get the charset specified by the user, not by the system.
   * @return string
   * @access protected
   */
  protected function _getPreferredCharset()
  {
    return $this->_preferredCharset;
  }
  
  /**
   * Get the format specified by the user, not by the system.
   * @return string
   * @access protected
   */
  protected function _getPreferredFormat()
  {
    return $this->_preferredFormat;
  }
  
  /**
   * Get the delsp as specified by the user, not by the system.
   * @return boolean
   * @access protected
   */
  protected function _getPreferredDelSp()
  {
    return $this->_preferredDelSp;
  }
  
  // -- Private methods
  
  /**
   * Injects its own body as a subpart of the overall structure so it's still
   * readable.
   * Returns true only if the structure was modified.
   */
  private function _moveBody(array $children)
  {
    if (!empty($children))
    {
      $highestLevel = $this->getNestingLevel();
      $newChildren = array();
      foreach ($children as $child)
      {
        $newChildren[] = $child;
        $childLevel = $child->getNestingLevel();
        if ($highestLevel < $childLevel)
        {
          $highestLevel = $childLevel;
        }
      }
      
      //If this entity has it's own body it needs to be displayed
      $body = is_null($b = $this->_getStringBody())
        ? $this->_getStreamBody() : $b;
      
      if (!is_null($body))
      {
        $subentity = $this->_createChild();
        $subentity->setContentType($this->_getPreferredContentType());
        $subentity->setCharset($this->_getPreferredCharset());
        $subentity->setDelSp($this->_getPreferredDelSp());
        $subentity->setFormat($this->_getPreferredFormat());
        $subentity->setNestingLevel($highestLevel);
        $subentity->setBody($body);
        array_unshift($newChildren, $subentity);
        $this->setChildren($newChildren);
        return true;
      }
    }
    
    return false;
  }
  
}
