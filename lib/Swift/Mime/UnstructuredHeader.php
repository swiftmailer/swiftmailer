<?php

/*
 A Simple Mime Header in Swift Mailer.
 
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

require_once dirname(__FILE__) . '/Header.php';
require_once dirname(__FILE__) . '/HeaderEncoder.php';
require_once dirname(__FILE__) . '/HeaderAttributeSet.php';


/**
 * A Simple MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_UnstructuredHeader implements Swift_Mime_Header
{
  
  /**
   * The name of this Header.
   * @var string
   * @access private
   */
  private $_name;
  
  /**
   * The value of this Header.
   * @var string
   * @access private
   */
  private $_value;
  
  /**
   * HeaderAttributes belonging to this Header.
   * @var Swift_Mime_HeaderAttributeSet
   * @access private
   */
  private $_attributes;
  
  /**
   * The Encoder used to encode this Header.
   * @var Swift_Encoder
   * @access private
   */
  private $_encoder;
  
  /**
   * The maximum length of a line in the header.
   * @var int
   * @access private
   */
  private $_lineLength = 78;
  
  /**
   * The character set of the text in this Header.
   * @var string
   * @access private
   */
  private $_charset;
  
  /**
   * Creates a new SimpleHeader with $name and $value.
   * @param string $name
   * @param string $value
   * @param string $charset, optional
   * @param Swift_Mime_HeaderEncoder $encoder, optional
   */
  public function __construct($name, $value, $charset = null,
    Swift_Mime_HeaderEncoder $encoder = null)
  {
    $this->_name = $name;
    $this->_value = $value;
    if (!is_null($charset))
    {
      $this->_charset = $charset;
    }
    if (!is_null($encoder))
    {
      $this->_encoder = $encoder;
    }
  }
  
  /**
   * Set the character set used in this Header.
   * @param string $charset
   */
  public function setCharacterSet($charset)
  {
    $this->_charset = $charset;
  }
  
  /**
   * Set the encoder used for encoding the header.
   * @param Swift_Mime_HeaderEncoder $encoder
   */
  public function setEncoder(Swift_Mime_HeaderEncoder $encoder)
  {
    $this->_encoder = $encoder;
  }
  
  /**
   * Get the name of this header (e.g. charset).
   * @return string
   */
  public function getName()
  {
    return $this->_name;
  }
  
  /**
   * Get the (unencoded) value of this header.
   * @return string
   */
  public function getValue()
  {
    return $this->_value;
  }
  
  /**
   * Set the (unencoded) value of this header.
   * @param string $value
   */
  public function setValue($value)
  {
    $this->_value = $value;
  }
  
  /**
   * Set a collection of HeaderAttributes to be applied to this Header.
   * @param Swift_Mime_HeaderAttributeSet $attributes
   */
  public function setAttributes(Swift_Mime_HeaderAttributeSet $attributes)
  {
    $this->_attributes = $attributes;
  }
  
  /**
   * Get the collection of HeaderAttributes applied to this Header.
   * @return Swift_Mime_HeaderAttributeSet
   */
  public function getAttributes()
  {
    return $this->_attributes;
  }
  
  /**
   * Set the maximum length of lines in the header (excluding EOL).
   * @param int $lineLength
   */
  public function setMaxLineLength($lineLength)
  {
    $this->_lineLength = $lineLength;
  }
  
  /**
   * Get this Header rendered as a RFC 2822 compliant string.
   * @return string
   */
  public function toString()
  {
    $lineCount = 0;
    $headerLines = array();
    $headerLines[] = $this->_name . ': ';
    $currentLine =& $headerLines[$lineCount++];
    
    //Split at all invisible boundaries followed by WSP
    $tokens = preg_split('~(?=[ \t])~', $this->getPreparedValue());
    
    //Try creating any attributes
    if (!is_null($this->_attributes))
    {
      foreach ($this->_attributes->toArray() as $attribute)
      {
        //Add the semi-colon separator
        $tokens[count($tokens)-1] .= ';';
        $attributeLines = explode("\r\n", $attribute->toString());
        //Prepend each line with WSP
        foreach ($attributeLines as $lineNumber => $attributeLine)
        {
          $tokens[] = ' ' . $attributeLine;
          //Send line break if more lines follow
          if ($lineNumber + 1 < count($attributeLines))
          {
            $tokens[] = "\r\n";
          }
        }
      }
    }
    
    //Build all tokens back into compliant header
    foreach ($tokens as $token)
    {
      $token = rtrim($token, "\r\n");
      
      //Line longer than specified maximum or token was just a new line
      if ('' == $token || strlen($currentLine . $token) > $this->_lineLength)
      {
        $headerLines[] = '';
        $currentLine =& $headerLines[$lineCount++];
      }
      
      //Append token to the line
      $currentLine .= $token;
    }
    
    //Implode with FWSP (RFC 2822, 2.2.3)
    return implode("\r\n", $headerLines) . "\r\n";
  }
  
  // -- Points of extension
  
  /**
   * Get the value of this header prepared for rendering.
   * @return string
   * @access protected
   */
  protected function getPreparedValue()
  {
    $value = '';
    
    //Split at all whitespace boundaries
    $tokens = preg_split('~(?=\s+)|\b~', $this->_value);
    foreach ($tokens as $token)
    {
      //See RFC 2822, Sect 2.2
      if (!preg_match('~^[^\x80-\xFF\r\n]*$~D', $token))
      {
        $encodedText = $this->_encoder->encodeString($token);
        $value .= '=?' . $this->_charset;
        $value .= '?' . $this->_encoder->getName();
        $value .= '?' . $encodedText . '?=';
      }
      else
      {
        $value .= $token;
      }
    }
    
    return str_replace('\\', '\\\\', $value);
  }
  
}
