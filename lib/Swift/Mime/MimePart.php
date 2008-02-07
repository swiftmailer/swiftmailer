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

require_once dirname(__FILE__) . '/SimpleMimeEntity.php';


/**
 * A MIME part, in a multipart message.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_MimePart extends Swift_Mime_SimpleMimeEntity
{
  
  /**
   * The charset of this mime part.
   * This defaults to unspecified, which according to RFC 2046 is US-ASCII.
   * @var string
   * @access private
   */
  private $_charset;
  
  /**
   * The format of this mime part (i.e. flowed or fixed).
   * If unspecified, the default is fixed as per RFC 3676.
   * @var string
   * @access private
   */
  private $_format;
  
  /**
   * Whether delsp is turned on or off according to RFC 3676.
   * @var boolean
   * @access private
   */
  private $_delSp = false;
  
  /**
   * Creates a new MimePart with $headers and $encoder.
   * @param string[] $headers
   * @param Swift_Mime_ContentEncoder $encoder
   */
  public function __construct(array $headers,
    Swift_Mime_ContentEncoder $encoder)
  {
    parent::__construct($headers, $encoder);
    $this->setNestingLevel(self::LEVEL_SUBPART);
  }
  
  /**
   * Set the charset of this mime part.
   * @param string
   */
  public function setCharset($charset)
  {
    $this->_charset = $charset;
    $this->_notifyFieldChanged('charset', $charset);
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
    return $this->_charset;
  }
  
  /**
   * Set the format of this mime part (i.e. fixed, flowed or NULL).
   * @param string
   */
  public function setFormat($format)
  {
    $this->_format = $format;
    $this->_notifyFieldChanged('format', $format);
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
    return $this->_format;
  }
  
  /**
   * Set whether delsp should be used, as per RFC 3676.
   * @param boolean $delSp
   */
  public function setDelSp($delSp)
  {
    $this->_delSp = $delSp;
    $this->_notifyFieldChanged('delsp', $delSp);
    return $this;
  }
  
  /**
   * Get the whather delsp is used as per RFC 3676.
   * This defaults to false ('no').
   * @return boolean
   */
  public function getDelSp()
  {
    return $this->_delSp;
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
    if ('encoder' == $field && ($value instanceof Swift_Mime_ContentEncoder))
    {
      $this->setEncoder($value);
    }
  }
  
}
