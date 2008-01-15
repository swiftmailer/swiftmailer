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
    return $this->_tokensToString($this->_toTokens());
  }
  
  // -- Points of extension
  
  /**
   * Get the value of this header prepared for rendering.
   * @return string
   * @access protected
   */
  protected function getPreparedValue()
  {
    return str_replace('\\', '\\\\', $this->encodeWords($this->_value));
  }
  
  /**
   * Encode needed word tokens within a string of input.
   * @param string $input
   * @param string $usedLength, optional
   * @return string
   * @access protected
   */
  protected function encodeWords($input, $usedLength = -1)
  {
    $value = '';
    
    $tokens = $this->getEncodableWordTokens($input);
    
    foreach ($tokens as $token)
    {
      //See RFC 2822, Sect 2.2 (really 2.2 ??)
      if ($this->tokenNeedsEncoding($token))
      {
        //Don't encode starting WSP
        $firstChar = substr($token, 0, 1);
        if (in_array($firstChar, array(' ', "\t")))
        {
          $value .= $firstChar;
          $token = substr($token, 1);
        }
        
        if (-1 == $usedLength)
        {
          $usedLength = strlen($this->getName() . ': ') + strlen($value);
        }
        $value .= $this->getTokenAsEncodedWord($token, $usedLength);
        
        $this->setMaxLineLength(76); //Forefully override
      }
      else
      {
        $value .= $token;
      }
    }
    
    return $value;
  }
  
  /**
   * Get a token as an encoded word for safe insertion into headers.
   * @param string $token to encode
   * @param int $firstLineOffset, optional
   * @return string
   * @access protected
   */
  protected function getTokenAsEncodedWord($token, $firstLineOffset = 0)
  {
    //Adjust $firstLineOffset to account for space needed for syntax
    $firstLineOffset += strlen(
      '=?' . $this->_charset . '?' . $this->_encoder->getName() . '??='
      );
    
    if ($firstLineOffset >= 75) //Does this logic need to be here?
    {
      $firstLineOffset = 0;
    }
    
    $encodedTextLines = explode("\r\n",
      $this->_encoder->encodeString($token, $firstLineOffset, 75)
      );
    
    foreach ($encodedTextLines as $lineNum => $line)
    {
      $encodedTextLines[$lineNum] = '=?' . $this->_charset .
        '?' . $this->_encoder->getName() .
        '?' . $line . '?=';
    }
    
    return implode("\r\n ", $encodedTextLines);
  }
  
  /**
   * Test if a token needs to be encoded or not.
   * @param string $token
   * @return boolean
   * @access protected
   */
  protected function tokenNeedsEncoding($token)
  {
    return preg_match('~[\x00-\x08\x10-\x19\x7F-\xFF\r\n]~', $token);
  }
  
  /**
   * Splits a string into tokens in blocks of words which can be encoded quickly.
   * @param string $string
   * @return string[]
   * @access protected
   */
  protected function getEncodableWordTokens($string)
  {
    $tokens = array();
    
    $encodedToken = '';
    //Split at all whitespace boundaries
    foreach (preg_split('~(?=[\t ])~', $string) as $token)
    {
      if ($this->tokenNeedsEncoding($token))
      {
        $encodedToken .= $token;
      }
      else
      {
        if (strlen($encodedToken) > 0)
        {
          $tokens[] = $encodedToken;
          $encodedToken = '';
        }
        $tokens[] = $token;
      }
    }
    if (strlen($encodedToken))
    {
      $tokens[] = $encodedToken;
    }
    
    return $tokens;
  }
  
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
    foreach ($tokens as $token)
    {
      //Line longer than specified maximum or token was just a new line
      if ("\r\n" == $token || strlen($currentLine . $token) > $this->_lineLength)
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
