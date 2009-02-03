<?php

/*
 An abstract base MIME Header in Swift Mailer.
 
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

//@require 'Swift/Mime/Header.php';
//@require 'Swift/Mime/HeaderEncoder.php';
//@require 'Swift/RfcComplianceException.php';

/**
 * An abstract base MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
abstract class Swift_Mime_Headers_AbstractHeader implements Swift_Mime_Header
{
  
  /**
   * Special characters used in the syntax which need to be escaped.
   * @var string[]
   * @access private
   */
  private $_specials = array();
  
  /**
   * Tokens defined in RFC 2822 (and some related RFCs).
   * @var string[]
   * @access private
   */
  private $_grammar = array();
  
  /**
   * The name of this Header.
   * @var string
   * @access private
   */
  private $_name;
  
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
   * The language used in this Header.
   * @var string
   */
  private $_lang;
  
  /**
   * The character set of the text in this Header.
   * @var string
   * @access private
   */
  private $_charset = 'utf-8';
  
  /**
   * The value of this Header, cached.
   * @var string
   * @access private
   */
  private $_cachedValue = null;
  
  /**
   * Set the character set used in this Header.
   * @param string $charset
   */
  public function setCharset($charset)
  {
    $this->clearCachedValueIf($charset != $this->_charset);
    $this->_charset = $charset;
    if (isset($this->_encoder))
    {
      $this->_encoder->charsetChanged($charset);
    }
  }
  
  /**
   * Get the character set used in this Header.
   * @return string
   */
  public function getCharset()
  {
    return $this->_charset;
  }
  
  /**
   * Set the language used in this Header.
   * For example, for US English, 'en-us'.
   * This can be unspecified.
   * @param string $lang
   */
  public function setLanguage($lang)
  {
    $this->clearCachedValueIf($this->_lang != $lang);
    $this->_lang = $lang;
  }
  
  /**
   * Get the language used in this Header.
   * @return string
   */
  public function getLanguage()
  {
    return $this->_lang;
  }
  
  /**
   * Set the encoder used for encoding the header.
   * @param Swift_Mime_HeaderEncoder $encoder
   */
  public function setEncoder(Swift_Mime_HeaderEncoder $encoder)
  {
    $this->_encoder = $encoder;
    $this->setCachedValue(null);
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
  public function getFieldName()
  {
    return $this->_name;
  }
  
  /**
   * Set the maximum length of lines in the header (excluding EOL).
   * @param int $lineLength
   */
  public function setMaxLineLength($lineLength)
  {
    $this->clearCachedValueIf($this->_lineLength != $lineLength);
    $this->_lineLength = $lineLength;
  }
  
  /**
   * Get the maximum permitted length of lines in this Header.
   * @return int
   */
  public function getMaxLineLength()
  {
    return $this->_lineLength;
  }
  
  /**
   * Get this Header rendered as a RFC 2822 compliant string.
   * @return string
   * @throws Swift_RfcComplianceException
   */
  public function toString()
  {
    return $this->_tokensToString($this->toTokens());
  }
  
  // -- Points of extension
  
  /**
   * Set the name of this Header field.
   * @param string $name
   * @access protected
   */
  protected function setFieldName($name)
  {
    $this->_name = $name;
  }
  
  /**
   * Initialize some RFC 2822 (and friends) ABNF grammar definitions.
   * @access protected
   */
  protected function initializeGrammar()
  {
    $this->_specials = array(
      '(', ')', '<', '>', '[', ']',
      ':', ';', '@', ',', '.', '"'
      );
    
    /*** Refer to RFC 2822 for ABNF grammar ***/
    
    //All basic building blocks
    $this->_grammar['NO-WS-CTL'] = '[\x01-\x08\x0B\x0C\x0E-\x19\x7F]';
    $this->_grammar['WSP'] = '[ \t]';
    $this->_grammar['CRLF'] = '(?:\r\n)';
    $this->_grammar['FWS'] = '(?:(?:' . $this->_grammar['WSP'] . '*' .
        $this->_grammar['CRLF'] . ')?' . $this->_grammar['WSP'] . ')';
    $this->_grammar['text'] = '[\x00-\x08\x0B\x0C\x0E-\x7F]';
    $this->_grammar['quoted-pair'] = '(?:\\\\' . $this->_grammar['text'] . ')';
    $this->_grammar['ctext'] = '(?:' . $this->_grammar['NO-WS-CTL'] .
        '|[\x21-\x27\x2A-\x5B\x5D-\x7E])';
    //Uses recursive PCRE (?1) -- could be a weak point??
    $this->_grammar['ccontent'] = '(?:' . $this->_grammar['ctext'] . '|' .
        $this->_grammar['quoted-pair'] . '|(?1))';
    $this->_grammar['comment'] = '(\((?:' . $this->_grammar['FWS'] . '|' .
        $this->_grammar['ccontent']. ')*' . $this->_grammar['FWS'] . '?\))';
    $this->_grammar['CFWS'] = '(?:(?:' . $this->_grammar['FWS'] . '?' .
        $this->_grammar['comment'] . ')*(?:(?:' . $this->_grammar['FWS'] . '?' .
        $this->_grammar['comment'] . ')|' . $this->_grammar['FWS'] . '))';
    $this->_grammar['qtext'] = '(?:' . $this->_grammar['NO-WS-CTL'] .
        '|[\x21\x23-\x5B\x5D-\x7E])';
    $this->_grammar['qcontent'] = '(?:' . $this->_grammar['qtext'] . '|' .
        $this->_grammar['quoted-pair'] . ')';
    $this->_grammar['quoted-string'] = '(?:' . $this->_grammar['CFWS'] . '?"' .
        '(' . $this->_grammar['FWS'] . '?' . $this->_grammar['qcontent'] . ')*' .
        $this->_grammar['FWS'] . '?"' . $this->_grammar['CFWS'] . '?)';
    $this->_grammar['atext'] = '[a-zA-Z0-9!#\$%&\'\*\+\-\/=\?\^_`\{\}\|~]';
    $this->_grammar['atom'] = '(?:' . $this->_grammar['CFWS'] . '?' .
        $this->_grammar['atext'] . '+' . $this->_grammar['CFWS'] . '?)';
    $this->_grammar['dot-atom-text'] = '(?:' . $this->_grammar['atext'] . '+' .
        '(\.' . $this->_grammar['atext'] . '+)*)';
    $this->_grammar['dot-atom'] = '(?:' . $this->_grammar['CFWS'] . '?' .
        $this->_grammar['dot-atom-text'] . '+' . $this->_grammar['CFWS'] . '?)';
    $this->_grammar['word'] = '(?:' . $this->_grammar['atom'] . '|' .
        $this->_grammar['quoted-string'] . ')';
    $this->_grammar['phrase'] = '(?:' . $this->_grammar['word'] . '+?)';
    $this->_grammar['no-fold-quote'] = '(?:"(?:' . $this->_grammar['qtext'] .
        '|' . $this->_grammar['quoted-pair'] . ')*")';
    $this->_grammar['dtext'] = '(?:' . $this->_grammar['NO-WS-CTL'] .
        '|[\x21-\x5A\x5E-\x7E])';
    $this->_grammar['no-fold-literal'] = '(?:\[(?:' . $this->_grammar['dtext'] .
        '|' . $this->_grammar['quoted-pair'] . ')*\])';
    
    //Message IDs
    $this->_grammar['id-left'] = '(?:' . $this->_grammar['dot-atom-text'] . '|' .
        $this->_grammar['no-fold-quote'] . ')';
    $this->_grammar['id-right'] = '(?:' . $this->_grammar['dot-atom-text'] . '|' .
        $this->_grammar['no-fold-literal'] . ')';
    
    //Addresses, mailboxes and paths
    $this->_grammar['local-part'] = '(?:' . $this->_grammar['dot-atom'] . '|' .
        $this->_grammar['quoted-string'] . ')';
    $this->_grammar['dcontent'] = '(?:' . $this->_grammar['dtext'] . '|' .
        $this->_grammar['quoted-pair'] . ')';
    $this->_grammar['domain-literal'] = '(?:' . $this->_grammar['CFWS'] . '?\[(' .
        $this->_grammar['FWS'] . '?' . $this->_grammar['dcontent'] . ')*?' .
        $this->_grammar['FWS'] . '?\]' . $this->_grammar['CFWS'] . '?)';
    $this->_grammar['domain'] = '(?:' . $this->_grammar['dot-atom'] . '|' .
        $this->_grammar['domain-literal'] . ')';
    $this->_grammar['addr-spec'] = '(?:' . $this->_grammar['local-part'] . '@' .
        $this->_grammar['domain'] . ')';
  }
  
  /**
   * Get the grammar defined for $name token.
   * @param string $name execatly as written in the RFC
   * @return string
   */
  protected function getGrammar($name)
  {
    if (array_key_exists($name, $this->_grammar))
    {
      return $this->_grammar[$name];
    }
    else
    {
      throw new Swift_RfcComplianceException(
        "No such grammar '" . $name . "' defined."
        );
    }
  }
  
  /**
   * Escape special characters in a string (convert to quoted-pairs).
   * @param string $token
   * @param string[] $include additonal chars to escape
   * @param string[] $exclude chars from escaping
   * @return string
   */
  protected function escapeSpecials($token, $include = array(),
    $exclude = array())
  {
    foreach (
      array_merge(array('\\'), array_diff($this->_specials, $exclude), $include) as $char)
    {
      $token = str_replace($char, '\\' . $char, $token);
    }
    return $token;
  }
  
  /**
   * Produces a compliant, formatted RFC 2822 'phrase' based on the string given.
   * @param Swift_Mime_Header $header
   * @param string $string as displayed
   * @param string $charset of the text
   * @param Swift_Mime_HeaderEncoder $encoder
   * @param boolean $shorten the first line to make remove for header name
   * @return string
   */
  protected function createPhrase(Swift_Mime_Header $header, $string, $charset,
    Swift_Mime_HeaderEncoder $encoder = null, $shorten = false)
  {
    //Treat token as exactly what was given
    $phraseStr = $string;
    //If it's not valid
    if (!preg_match('/^' . $this->_grammar['phrase'] . '$/D', $phraseStr))
    {
      // .. but it is just ascii text, try escaping some characters
      // and make it a quoted-string
      if (preg_match('/^' . $this->_grammar['text'] . '*$/D', $phraseStr))
      {
        $phraseStr = $this->escapeSpecials(
          $phraseStr, array('"'), $this->_specials
          );
        $phraseStr = '"' . $phraseStr . '"';
      }
      else // ... otherwise it needs encoding
      {
        //Determine space remaining on line if first line
        if ($shorten)
        {
          $usedLength = strlen($header->getFieldName() . ': ');
        }
        else
        {
          $usedLength = 0;
        }
        $phraseStr = $this->encodeWords($header, $string, $usedLength);
      }
    }
    
    return $phraseStr;
  }
  
  /**
   * Encode needed word tokens within a string of input.
   * @param string $input
   * @param string $usedLength, optional
   * @return string
   */
  protected function encodeWords(Swift_Mime_Header $header, $input,
    $usedLength = -1)
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
        switch($firstChar)
        {
          case ' ':
          case "\t":
            $value .= $firstChar;
            $token = substr($token, 1);
        }
        
        if (-1 == $usedLength)
        {
          $usedLength = strlen($header->getFieldName() . ': ') + strlen($value);
        }
        $value .= $this->getTokenAsEncodedWord($token, $usedLength);
        
        $header->setMaxLineLength(76); //Forefully override
      }
      else
      {
        $value .= $token;
      }
    }
    
    return $value;
  }
  
  /**
   * Test if a token needs to be encoded or not.
   * @param string $token
   * @return boolean
   */
  protected function tokenNeedsEncoding($token)
  {
    return preg_match('~[\x00-\x08\x10-\x19\x7F-\xFF\r\n]~', $token);
  }
  
  /**
   * Splits a string into tokens in blocks of words which can be encoded quickly.
   * @param string $string
   * @return string[]
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
   * Get a token as an encoded word for safe insertion into headers.
   * @param string $token to encode
   * @param int $firstLineOffset, optional
   * @return string
   */
  protected function getTokenAsEncodedWord($token, $firstLineOffset = 0)
  {
    //Adjust $firstLineOffset to account for space needed for syntax
    $charsetDecl = $this->_charset;
    if (isset($this->_lang))
    {
      $charsetDecl .= '*' . $this->_lang;
    }
    $encodingWrapperLength = strlen(
      '=?' . $charsetDecl . '?' . $this->_encoder->getName() . '??='
      );
    
    if ($firstLineOffset >= 75) //Does this logic need to be here?
    {
      $firstLineOffset = 0;
    }
    
    $encodedTextLines = explode("\r\n",
      $this->_encoder->encodeString(
        $token, $firstLineOffset, 75 - $encodingWrapperLength
        )
      );
    
    foreach ($encodedTextLines as $lineNum => $line)
    {
      $encodedTextLines[$lineNum] = '=?' . $charsetDecl .
        '?' . $this->_encoder->getName() .
        '?' . $line . '?=';
    }
    
    return implode("\r\n ", $encodedTextLines);
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
  
  /**
   * Clear the cached value if $condition is met.
   * @param boolean $condition
   * @access protected
   */
  protected function clearCachedValueIf($condition)
  {
    if ($condition)
    {
      $this->setCachedValue(null);
    }
  }
  
  // -- Private methods
  
  /**
   * Generate a list of all tokens in the final header.
   * @param string $string input, optional
   * @return string[]
   * @access private
   */
  protected function toTokens($string = null)
  {
    if (is_null($string))
    {
      $string = $this->getFieldBody();
    }
    
    $tokens = array();
    
    //Generate atoms; split at all invisible boundaries followed by WSP
    foreach (preg_split('~(?=[ \t])~', $string) as $token)
    {
      $tokens = array_merge($tokens, $this->generateTokenLines($token));
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
      if (("\r\n" == $token) ||
        ($i > 0 && strlen($currentLine . $token) > $this->_lineLength)
        && 0 < strlen($currentLine))
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
