<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/SimpleMimeEntity.php';
//@require 'Swift/Mime/ContentEncoder.php';
//@require 'Swift/Mime/HeaderSet.php';
//@require 'Swift/KeyCache.php';

/**
 * A MIME part, in a multipart message.
 * 
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_MimePart extends Swift_Mime_SimpleMimeEntity
{
  
  /** The format parameter last specified by the user */
  protected $_userFormat;
  
  /** The charset last specified by the user */
  protected $_userCharset;
  
  /** The delsp parameter last specified by the user */
  protected $_userDelSp;
  
  /** The nesting level of this MimePart */
  private $_nestingLevel = self::LEVEL_ALTERNATIVE;
  
  /**
   * Create a new MimePart with $headers, $encoder and $cache.
   * 
   * @param Swift_Mime_HeaderSet $headers
   * @param Swift_Mime_ContentEncoder $encoder
   * @param Swift_KeyCache $cache
   * @param string $charset
   */
  public function __construct(Swift_Mime_HeaderSet $headers,
    Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache, $charset = null)
  {
    parent::__construct($headers, $encoder, $cache);
    $this->setContentType('text/plain');
    if (!is_null($charset))
    {
      $this->setCharset($charset);
    }
  }
  
  /**
   * Set the body of this entity, either as a string, or as an instance of
   * {@link Swift_OutputByteStream}.
   * 
   * @param mixed $body
   * @param string $contentType optional
   * @param string $charset optional
   */
  public function setBody($body, $contentType = null, $charset = null)
  {
    parent::setBody($body, $contentType);
    if (isset($charset))
    {
      $this->setCharset($charset);
    }
    return $this;
  }
  
  /**
   * Get the character set of this entity.
   * 
   * @return string
   */
  public function getCharset()
  {
    return $this->_getHeaderParameter('Content-Type', 'charset');
  }
  
  /**
   * Set the character set of this entity.
   * 
   * @param string $charset
   */
  public function setCharset($charset)
  {
    $this->_setHeaderParameter('Content-Type', 'charset', $charset);
    if ($charset !== $this->_userCharset)
    {
      $this->_clearCache();
    }
    $this->_userCharset = $charset;
    parent::charsetChanged($charset);
    return $this;
  }
  
  /**
   * Get the format of this entity (i.e. flowed or fixed).
   * 
   * @return string
   */
  public function getFormat()
  {
    return $this->_getHeaderParameter('Content-Type', 'format');
  }
  
  /**
   * Set the format of this entity (flowed or fixed).
   * 
   * @param string $format
   */
  public function setFormat($format)
  {
    $this->_setHeaderParameter('Content-Type', 'format', $format);
    $this->_userFormat = $format;
    return $this;
  }
  
  /**
   * Test if delsp is being used for this entity.
   * 
   * @return boolean
   */
  public function getDelSp()
  {
    return ($this->_getHeaderParameter('Content-Type', 'delsp') == 'yes')
      ? true
      : false;
  }
  
  /**
   * Turn delsp on or off for this entity.
   * 
   * @param boolean $delsp
   */
  public function setDelSp($delsp = true)
  {
    $this->_setHeaderParameter('Content-Type', 'delsp', $delsp ? 'yes' : null);
    $this->_userDelSp = $delsp;
    return $this;
  }
  
  /**
   * Get the nesting level of this entity.
   * 
   * @return int
   * @see LEVEL_TOP, LEVEL_ALTERNATIVE, LEVEL_MIXED, LEVEL_RELATED
   */
  public function getNestingLevel()
  {
    return $this->_nestingLevel;
  }
  
  /**
   * Receive notification that the charset has changed on this document, or a
   * parent document.
   * 
   * @param string $charset
   */
  public function charsetChanged($charset)
  {
    $this->setCharset($charset);
  }
  
  // -- Protected methods
  
  /** Fix the content-type and encoding of this entity */
  protected function _fixHeaders()
  {
    parent::_fixHeaders();
    if (count($this->getChildren()))
    {
      $this->_setHeaderParameter('Content-Type', 'charset', null);
      $this->_setHeaderParameter('Content-Type', 'format', null);
      $this->_setHeaderParameter('Content-Type', 'delsp', null);
    }
    else
    {
      $this->setCharset($this->_userCharset);
      $this->setFormat($this->_userFormat);
      $this->setDelSp($this->_userDelSp);
    }
  }
  
  /** Set the nesting level of this entity */
  protected function _setNestingLevel($level)
  {
    $this->_nestingLevel = $level;
  }
  
}
