<?php

/*
 A simple Header Attribute in Swift Mailer.
 
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

require_once dirname(__FILE__) . '/../HeaderAttribute.php';
require_once dirname(__FILE__) . '/../../Encoder.php';
require_once dirname(__FILE__) . '/../HeaderComponentHelper.php';


/**
 * An attribute to a MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_HeaderAttribute_SimpleHeaderAttribute
  implements Swift_Mime_HeaderAttribute
{
  
  /**
   * The name of this header attribute.
   * @var string
   * @access private
   */
  private $_name;
  
  /**
   * The value of this header attribute.
   * @var string
   * @access private
   */
  private $_value;
  
  /**
   * The maximum length of a line in this HeaderAttribute.
   * @var int
   * @access private
   */
  private $_maxLineLength = 77; //78 - 1 for FWS
  
  /**
   * The Encoder used to encode non-ascii attributes.
   * @var Swift_Encoder
   * @access private
   */
  private $_encoder;
  
  /**
   * The character set used in this HeaderAttribute.
   * @var string
   * @access private
   */
  private $_charset;
  
  /**
   * The language used in this HeaderAttribute.
   * @var string
   * @access private
   */
  private $_lang;
  
  /**
   * ABNF gammar defined in RFC 2045, converted to PCRE.
   * @var string[]
   * @access private
   */
  private $_grammar = array();
  
  /**
   * A HeaderComponentHelper to aid in encoding attributes etc.
   * @var Swift_Mime_HeaderComponentHelper
   * @access private
   */
  private $_helper;
  
  /**
   * Creates a new HeaderAttribute with $name and $value.
   * @param string $name
   * @param string $value
   * @param string $charset, optional
   * @param string $lang, optional
   * @param Swift_Encoder $encoder, optional
   */
  public function __construct($name, $value, $charset = null, $lang = null,
    Swift_Encoder $encoder = null)
  {
    $this->_name = $name;
    $this->_value = $value;
    
    $this->_helper = new Swift_Mime_HeaderComponentHelper();
    
    $this->_grammar['token'] =
      '(?:[\x21\x23-\x27\x2A\x2B\x2D\x2E\x30-\x39\x41-\x5A\x5E-\x7E]+)';
    
    if (!is_null($encoder))
    {
      $this->_encoder = $encoder;
    }
    
    if (!is_null($charset))
    {
      $this->_charset = $charset;
    }
    
    if (!is_null($lang))
    {
      $this->_lang = $lang;
    }
  }
  
  /**
   * Set the encoder used for encoding the attribute.
   * @param Swift_Encoder $encoder
   */
  public function setEncoder(Swift_Encoder $encoder)
  {
    $this->_encoder = $encoder;
  }
  
  /**
   * Set the maximum length of lines in the attribute.
   * @param int $length
   */
  public function setMaxLineLength($length)
  {
    $this->_maxLineLength = $length;
  }
  
  /**
   * Set the language used in this HeaderAttribute.
   * For example, for US English, 'en-us'.
   * This can be unspecified.
   * @param string $lang
   */
  public function setLanguage($lang)
  {
    $this->_lang = $lang;
  }
  
  /**
   * Get the language used in this HeaderAttribute.
   * @return string
   */
  public function getLanguage()
  {
    return $this->_lang;
  }
  
  /**
   * Set the charset used in this HeaderAttribute.
   * @param string $charset
   */
  public function setCharacterSet($charset)
  {
    $this->_charset = $charset;
  }
  
  /**
   * Get the charset used in this HeaderAttribute.
   * @return string
   */
  public function getCharacterSet()
  {
    return $this->_charset;
  }
  
  /**
   * Get the name of this attribute (e.g. charset).
   * @return string
   */
  public function getName()
  {
    return $this->_name;
  }
  
  /**
   * Get the (unencoded) value of this header attribute.
   * @return string
   */
  public function getValue()
  {
    return $this->_value;
  }
  
  /**
   * Set the (unencoded) value of this header attribute.
   * @param string $value
   */
  public function setValue($value)
  {
    $this->_value = $value;
  }
  
  /**
   * Get this attribute rendered as a RFC 2045 compliant string.
   * @return string
   */
  public function toString()
  {
    $value = addslashes($this->_value);
    
    $needsEncoding = false;
    //Allow room for parameter name, indices, "=" and DQUOTEs
    $maxValueLength = $this->_maxLineLength -
      strlen($this->getName() . '=*N""');
    $firstLineOffset = 0;
    
    //If it's not already a valid parameter value...
    if (!preg_match('/^' . $this->_grammar['token'] . '$/D', $value))
    {
      //TODO: text, or something else??
      //... and it's not ascii
      if (!preg_match('/^' . $this->_helper->getGrammar('text') . '*$/D', $value))
      {
        $needsEncoding = true;
        //Allow space for the indices, charset and language
        $maxValueLength = $this->_maxLineLength -
          strlen($this->getName() . '*N*=""');
        $firstLineOffset = strlen(
          $this->getCharacterSet() . "'" . $this->getLanguage() . "'"
          );
      }
    }
    
    //Encode if we need to
    if ($needsEncoding || strlen($value) > $maxValueLength)
    {
      $value = $this->_encoder->encodeString(
        $this->_value, $maxValueLength, $firstLineOffset
      );
    }
    
    $valueLines = explode("\r\n", $value);
    
    //Need to add indices
    if (count($valueLines) > 1)
    {
      $attributeLines = array();
      foreach ($valueLines as $i => $line)
      {
        $attributeLines[] = $this->_name . '*' . $i .
          $this->_getEndOfParameterValue($line, ($needsEncoding && $i == 0));
      }
      return implode("\r\n", $attributeLines);
    }
    else
    {
      return $this->_name . $this->_getEndOfParameterValue(
        $valueLines[0], $needsEncoding
        );
    }
  }
  
  // -- Private methods
  
  /**
   * Returns the parameter value from the "=" and beyond.
   * @param string $value to append
   * @param boolean $addEncodingInfo
   * @return string
   * @access private
   */
  private function _getEndOfParameterValue($value, $addEncodingInfo = false)
  {
    if (!preg_match('/^' . $this->_grammar['token'] . '$/D', $value))
    {
      $value = '"' . $value . '"';
    }
    if ($addEncodingInfo)
    {
      return '*=' . $this->getCharacterSet() . "'" . $this->getLanguage() .
        "'" . $value;
    }
    else
    {
      return '=' . $value;
    }
  }
  
}
