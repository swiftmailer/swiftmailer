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
//@require 'Swift/Mime/HeaderSet.php';
//@require 'Swift/KeyCache.php';

/**
 * A MIME part, in a multipart message.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_MimePart extends Swift_Mime_SimpleMimeEntity
{
  
  protected $_userFormat;
  protected $_userCharset;
  protected $_userDelSp;
  
  private $_nestingLevel = self::LEVEL_SUBPART;
  
  public function __construct(Swift_Mime_HeaderSet $headers,
    Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache, $charset = null)
  {
    parent::__construct($headers, $encoder, $cache);
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
  
  public function getCharset()
  {
    return $this->_getHeaderParameter('Content-Type', 'charset');
  }
  
  public function setCharset($charset)
  {
    $this->_setHeaderParameter('Content-Type', 'charset', $charset);
    $this->_userCharset = $charset;
    parent::charsetChanged($charset);
    return $this;
  }
  
  public function getFormat()
  {
    return $this->_getHeaderParameter('Content-Type', 'format');
  }
  
  public function setFormat($format)
  {
    $this->_setHeaderParameter('Content-Type', 'format', $format);
    $this->_userFormat = $format;
    return $this;
  }
  
  public function getDelSp()
  {
    return ($this->_getHeaderParameter('Content-Type', 'delsp') == 'yes')
      ? true
      : false;
  }
  
  public function setDelSp($delsp = true)
  {
    $this->_setHeaderParameter('Content-Type', 'delsp', $delsp ? 'yes' : null);
    $this->_userDelSp = $delsp;
    return $this;
  }
  
  public function getNestingLevel()
  {
    return $this->_nestingLevel;
  }
  
  public function charsetChanged($charset)
  {
    $this->setCharset($charset);
  }
  
  // -- Protected methods
  
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
  
  protected function _setNestingLevel($level)
  {
    $this->_nestingLevel = $level;
  }
  
}
