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

require_once dirname(__FILE__) . '/../Header.php';
require_once dirname(__FILE__) . '/../HeaderEncoder.php';
require_once dirname(__FILE__) . '/../HeaderAttributeSet.php';
require_once dirname(__FILE__) . '/../HeaderComponentHelper.php';


/**
 * A Simple MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_UnstructuredHeader implements Swift_Mime_Header
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
   * A helper wtih building MIME headers.
   * @var Swift_Mime_HeaderComponentHelper
   * @access private
   */
  private $_helper;
  
  /**
   * The value of this Header, cached.
   * @var string
   * @access private
   */
  private $_cachedValue = null;
  
  /**
   * Creates a new SimpleHeader with $name and $value.
   * @param string $name
   * @param string $value, optional
   * @param string $charset, optional
   * @param Swift_Mime_HeaderEncoder $encoder, optional
   */
  public function __construct($name, $value = null, $charset = null,
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
    
    $this->_helper = new Swift_Mime_HeaderComponentHelper();
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
   * Get the character set used in this Header.
   * @return string
   */
  public function getCharacterSet()
  {
    return $this->_charset;
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
   * Get the encoder used for encoding this Header.
   * @return Swift_Mime_HeaderEncoder
   */
  public function getEncoder()
  {
    return $this->_encoder;
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
   * Sets the value of this Header as if it's already been prepared for use.
   * Lines needn't be folded since {@link toString()} will fold long lines.
   * @param string $value
   */
  public function setPreparedValue($value)
  {
  }
  
  /**
   * Get the value of this header prepared for rendering.
   * @return string
   */
  public function getPreparedValue()
  {
    if (!$this->getCachedValue())
    {
      $this->setCachedValue(
        str_replace('\\', '\\\\', $this->_helper->encodeWords(
          $this, $this->_value, -1, $this->_charset, $this->_encoder
          ))
        );
    }
    return $this->getCachedValue();
  }
  
  /**
   * Get this Header rendered as a RFC 2822 compliant string.
   * @return string
   */
  public function toString()
  {
    return $this->_tokensToString($this->_toTokens());
  }
  
  // -- Points of extension
  
  /**
   * Generates tokens from the given string which include CRLF as individual tokens.
   * @param string $token
   * @return string[]
   * @access protected
   */
  protected function generateTokenLines($token)
  {
    return preg_split('~(\r\n)~', $token, -1, PREG_SPLIT_DELIM_CAPTURE);
  }
  
  /**
   * Get the HeaderComponentHelper used by this Header.
   * @return Swift_Mime_HeaderComponentHelper
   * @access protected
   */
  protected function getHelper()
  {
    return $this->_helper;
  }
  
  /**
   * Set a value into the cache.
   * @param string $value
   * @access protected
   */
  protected function setCachedValue($value)
  {
    $this->_cachedValue = $value;
  }
  
  /**
   * Get the value in the cache.
   * @return string
   * @access protected
   */
  protected function getCachedValue()
  {
    return $this->_cachedValue;
  }
  
  // -- Private methods
  
  /**
   * Generate a list of all tokens in the final header.
   * @return string[]
   * @access private
   */
  private function _toTokens()
  {
    $tokens = array();
    
    //Generate atoms; split at all invisible boundaries followed by WSP
    foreach (preg_split('~(?=[ \t])~', $this->getPreparedValue()) as $token)
    {
      $tokens = array_merge($tokens, $this->generateTokenLines($token));
    }
    
    //Try creating any attributes
    if (!is_null($this->_attributes))
    {
      foreach ($this->_attributes->toArray() as $attribute)
      {
        //Add the semi-colon separator
        $tokens[count($tokens)-1] .= ';';
        $tokens = array_merge($tokens, $this->generateTokenLines(
          ' ' . str_replace("\r\n", "\r\n ", $attribute->toString())
          ));
      }
    }
    
    return $tokens;
  }
  
  /**
   * Takes an array of tokens which appear in the header and turns them into
   * an RFC 2822 compliant string, adding FWSP where needed.
   * @param string[] $tokens
   * @return string
   * @access private
   */
  private function _tokensToString(array $tokens)
  {
    $lineCount = 0;
    $headerLines = array();
    $headerLines[] = $this->_name . ': ';
    $currentLine =& $headerLines[$lineCount++];
    
    //Build all tokens back into compliant header
    foreach ($tokens as $i => $token)
    {
      //Line longer than specified maximum or token was just a new line
      if ("\r\n" == $token ||
        ($i > 0 && strlen($currentLine . $token) > $this->_lineLength))
      {
        $headerLines[] = '';
        $currentLine =& $headerLines[$lineCount++];
      }
      
      //Append token to the line
      if ("\r\n" != $token)
      {
        $currentLine .= $token;
      }
    }
    
    //Implode with FWS (RFC 2822, 2.2.3)
    return implode("\r\n", $headerLines) . "\r\n";
  }
  
}
