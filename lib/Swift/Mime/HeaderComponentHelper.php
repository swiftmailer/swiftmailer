<?php

/*
 Header building component helper in Swift Mailer.
 
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


/**
 * A helper for components which build MIME headers.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_HeaderComponentHelper
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
   * Creates a new HeaderComponentHelper.
   */
  public function __construct()
  {
    
    $this->_specials = array(
      '\\', '(', ')', '<', '>', '[', ']',
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
    $this->_grammar['specials'] = '[\\\\\(\)<>\[\]:;@,\."]';
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
    $this->_grammar['utext'] = '(?:' . $this->_grammar['NO-WS-CTL'] . '|[\x21-\x7E])';
    $this->_grammar['unstructured'] = '(?:(?:' . $this->_grammar['FWS'] . '?' .
        $this->_grammar['utext'] . ')*' . $this->_grammar['FWS'] . '?)';
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
    $this->_grammar['msg-id'] = '(?:' . $this->_grammar['CFWS'] . '?<' .
        $this->_grammar['id-left'] . '@' . $this->_grammar['id-right'] . '>' .
        $this->_grammar['CFWS'] . '?)';
    
    //Addresses, mailboxes and paths
    $this->_grammar['display-name'] = $this->_grammar['phrase'];
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
    $this->_grammar['path'] = '(?:' . $this->_grammar['CFWS'] . '?<(?:' .
        $this->_grammar['CFWS'] . '|' . $this->_grammar['addr-spec'] . ')?>' .
        $this->_grammar['CFWS'] . '?)';
    $this->_grammar['angle-addr'] = '(?:' . $this->_grammar['CFWS'] . '?<' .
        $this->_grammar['addr-spec'] . '>' . $this->_grammar['CFWS'] . '?)';
    $this->_grammar['name-addr'] = '(?:'. $this->_grammar['display-name'] . '?' .
        $this->_grammar['angle-addr'] . ')';
    $this->_grammar['mailbox'] = '(?:' . $this->_grammar['name-addr'] . '|' .
        $this->_grammar['addr-spec'] . ')';
    $this->_grammar['mailbox-list'] = '(?:' . $this->_grammar['mailbox'] . '(?:,' .
        $this->_grammar['mailbox'] . ')*)';
    $this->_grammar['group'] = '(?:' . $this->_grammar['display-name'] . ':(?:' .
        $this->_grammar['mailbox-list'] . '|' . $this->_grammar['CFWS'] . ')?;' .
        $this->_grammar['CFWS'] . '?)';
    $this->_grammar['address'] = '(?:' . $this->_grammar['mailbox'] . '|' .
        $this->_grammar['group'] . ')';
    $this->_grammar['address-list'] = '(?:' . $this->_grammar['address'] . '(?:,' .
        $this->_grammar['address'] . ')*)';
    
    
    //Encoded words (RFC 2047)
    $this->_grammar['token'] = '(?:[\x21\x23-\x27\x2A\x2B\x2D\x30-\x39\x41-\x5A\x5C\x5E-\x7E]+)';
    $this->_grammar['charset'] = $this->_grammar['token'];
    $this->_grammar['encoding'] = $this->_grammar['token'];
    $this->_grammar['encoded-text'] = '(?:[\x21-\x3E\x40-\x7E]+)';
    $this->_grammar['encoded-word'] = '(?:=\?' . $this->_grammar['charset'] .
        '\?' . $this->_grammar['encoding'] . '\?' .
        $this->_grammar['encoded-text'] . '\?=)';
    
    //Date and time
    $this->_grammar['day-name'] = '(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun)';
    $this->_grammar['day-of-week'] = '(?:' . $this->_grammar['FWS'] . '?' .
        $this->_grammar['day-name'] . ')';
    $this->_grammar['day'] = '(?:' . $this->_grammar['FWS'] . '?[0-9]{1,2})';
    $this->_grammar['month-name'] = '(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)';
    $this->_grammar['month'] = '(?:' . $this->_grammar['FWS'] .
        $this->_grammar['month-name'] . $this->_grammar['FWS'] . ')';
    $this->_grammar['year'] = '(?:[0-9]{4,})';
    $this->_grammar['date'] = '(?:' . $this->_grammar['day'] .
        $this->_grammar['month'] . $this->_grammar['year'] . ')';
    $this->_grammar['hour'] = '(?:[0-9]{2})';
    $this->_grammar['minute'] = '(?:[0-9]{2})';
    $this->_grammar['second'] = '(?:[0-9]{2})';
    $this->_grammar['time-of-day'] = '(?:' . $this->_grammar['hour'] . ':' .
        $this->_grammar['minute'] . '(?::' . $this->_grammar['second'] . ')?)';
    $this->_grammar['zone'] = '(?:[\+\-][0-9]{4})';
    $this->_grammar['time'] = '(?:' . $this->_grammar['time-of-day'] .
        $this->_grammar['FWS'] . $this->_grammar['zone'] . ')';
    $this->_grammar['date-time'] = '(?:(?:' . $this->_grammar['day-of-week'] .
        ',)?' . $this->_grammar['date'] . $this->_grammar['FWS'] .
        $this->_grammar['time'] . $this->_grammar['CFWS'] . '?)';
    
    //Received headers
    $this->_grammar['item-name'] = '(?:[a-zA-Z](?:-?[a-zA-Z0-9])*)';
    $this->_grammar['item-value'] = '(?:' . $this->_grammar['angle-addr'] . '+|' .
        $this->_grammar['addr-spec'] . '|' . $this->_grammar['atom'] . '|' .
        $this->_grammar['domain'] . '|' . $this->_grammar['msg-id'] . ')';
    $this->_grammar['name-val-pair'] = '(?:' . $this->_grammar['item-name'] .
        $this->_grammar['CFWS'] . $this->_grammar['item-value'] . ')';
    $this->_grammar['name-val-list'] = '(?:' . $this->_grammar['CFWS'] . '?(?:' .
        $this->_grammar['name-val-pair'] . '(?:' . $this->_grammar['CFWS'] .
        $this->_grammar['name-val-pair'] . ')*)?)';
  }
  
  /**
   * Get the grammar defined for $name token.
   * @param string $name execatly as written in the RFC
   * @return string
   */
  public function getGrammar($name)
  {
    if (array_key_exists($name, $this->_grammar))
    {
      return $this->_grammar[$name];
    }
    else
    {
      throw new Exception("No such grammar '" . $name . "' defined.");
    }
  }
  
  /**
   * Escape special characters in a string (convert to quoted-pairs).
   * @param string $token
   * @param string[] $include additonal chars to escape
   * @param string[] $exclude chars from escaping
   * @return string
   */
  public function escapeSpecials($token, $include = array(), $exclude = array())
  {
    foreach (array_merge($this->_specials, $include) as $char)
    {
      if (in_array($char, $exclude))
      {
        continue;
      }
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
  public function createPhrase(Swift_Mime_Header $header, $string, $charset,
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
        $phraseStr = $this->escapeSpecials($phraseStr);
        $phraseStr = '"' . $phraseStr . '"';
      }
      else // ... otherwise it needs encoding
      {
        //Determine space remaining on line if first line
        if ($shorten)
        {
          $usedLength = strlen($header->getName() . ': ');
        }
        else
        {
          $usedLength = 0;
        }
        $phraseStr = $this->encodeWords(
          $header, $string, $usedLength, $charset, $encoder
          );
      }
    }
    
    return $phraseStr;
  }
  
  /**
   * Encode needed word tokens within a string of input.
   * @param string $input
   * @param string $usedLength, optional
   * @param string $charset
   * @param Swift_Mime_HeaderEncoder $encoder
   * @return string
   */
  public function encodeWords(Swift_Mime_Header $header, $input,
    $usedLength = -1, $charset, Swift_Mime_HeaderEncoder $encoder = null)
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
          $usedLength = strlen($header->getName() . ': ') + strlen($value);
        }
        $value .= $this->getTokenAsEncodedWord(
          $token, $usedLength, $charset, $encoder
          );
        
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
  public function tokenNeedsEncoding($token)
  {
    return preg_match('~[\x00-\x08\x10-\x19\x7F-\xFF\r\n]~', $token);
  }
  
  /**
   * Splits a string into tokens in blocks of words which can be encoded quickly.
   * @param string $string
   * @return string[]
   */
  public function getEncodableWordTokens($string)
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
   * @param string $charset
   * @param Swift_Mime_HeaderEncoder $encoder
   * @return string
   */
  public function getTokenAsEncodedWord($token, $firstLineOffset = 0, $charset,
    Swift_Mime_HeaderEncoder $encoder)
  {
    //Adjust $firstLineOffset to account for space needed for syntax
    $firstLineOffset += strlen(
      '=?' . $charset . '?' . $encoder->getName() . '??='
      );
    
    if ($firstLineOffset >= 75) //Does this logic need to be here?
    {
      $firstLineOffset = 0;
    }
    
    $encodedTextLines = explode("\r\n",
      $encoder->encodeString($token, $firstLineOffset, 75)
      );
    
    foreach ($encodedTextLines as $lineNum => $line)
    {
      $encodedTextLines[$lineNum] = '=?' . $charset .
        '?' . $encoder->getName() .
        '?' . $line . '?=';
    }
    
    return implode("\r\n ", $encodedTextLines);
  }
  
  /**
   * Decode/parse out a RFC 2822 compliant display-name to get the actual
   * text value.
   * @param string $phrase
   * @return string
   * @access protected
   */
  public function decodePhrase($phrase)
  {
    //Get rid of any CFWS
    $string = $this->trimCFWS($phrase);
    if ('' == $string)
    {
      return null;
    }
    
    if (!preg_match('/^' . $this->_grammar['phrase'] . '$/D', $string))
    {
      throw new Exception('Invalid RFC 2822 phrase token.');
    }
    
    $string = $this->unfoldWhitespace($string);
    
    if (substr($string, 0, 1) == '"') //Name is a quoted-string
    {
      $string = preg_replace('/\\\\(.)/', '$1', substr($string, 1, -1));
    }
    else //Name is a simple list of words
    {
      $string = $this->decodeEncodedWords($string);
    }
    return $string;
  }
  
  /**
   * Decode/parse out a RFC 2822 compliant unstructured string to get the actual
   * text value.
   * @param string $text
   * @return string
   * @access protected
   */
  public function decodeText($text)
  {
    //Get rid of any CFWS
    $string = $this->trimFWS($text);
    if ('' == $string)
    {
      return null;
    }
    
    if (!preg_match('/^' . $this->_grammar['unstructured'] . '$/D', $string))
    {
      throw new Exception('Invalid RFC 2822 unstructured token.');
    }
    
    $string = $this->unfoldWhitespace($string);
    $string = $this->decodeEncodedWords($string);
    
    return $string;
  }
  
  /**
   * Remove FWS from the left and right of the given token.
   * @param string $token
   * @param string $sides to trim from
   * @return string
   * @access protected
   */
  public function trimFWS($token, $sides = 'both')
  {
    switch ($sides)
    {
      case 'right':
        $pattern = '/' . $this->_grammar['FWS'] . '$/';
        break;
      case 'left':
        $pattern = '/^' . $this->_grammar['FWS'] . '/';
        break;
      case 'both':
      default:
        $pattern = '/^' . $this->_grammar['FWS'] . '|' .
      $this->_grammar['FWS'] . '$/';
    }
    return preg_replace($pattern, '', $token);
  }
  
  /**
   * Remove CFWS from the left and right of the given token.
   * @param string $token
   * @param string $sides to trim from
   * @return string
   * @access protected
   */
  public function trimCFWS($token, $sides = 'both')
  {
    switch ($sides)
    {
      case 'right':
        $pattern = '/' . $this->_grammar['CFWS'] . '$/';
        break;
      case 'left':
        $pattern = '/^' . $this->_grammar['CFWS'] . '/';
        break;
      case 'both':
      default:
        $pattern = '/^' . $this->_grammar['CFWS'] . '|' .
      $this->_grammar['CFWS'] . '$/';
    }
    return preg_replace($pattern, '', $token);
  }
  
  /**
   * Removes all CFWS from the given token.
   * @param string $token
   * @return string
   * @access protected
   */
  public function stripCFWS($token)
  {
    return preg_replace('/' . $this->_grammar['CFWS'] . '/', '', $token);
  }
  
  /**
   * Decodes encoded-word tokens as defined by RFC 2047.
   * @param string $token
   * @return string
   * @access protected
   */
  public function decodeEncodedWords($token)
  {
    return preg_replace_callback(
      '/(?:' . $this->_grammar['encoded-word'] .
      $this->_grammar['FWS'] . '+)*' .
      $this->_grammar['encoded-word'] . '/',
      array($this, '_decodeEncodedWordList'),
      $token
      );
  }
  
  public function unfoldWhiteSpace($token)
  {
    return preg_replace('/\r\n([ \t])/', '$1', $token);
  }
  
  // -- Private methods
  
  /**
   * Callback which decodes adjacent groups of encoded-word tokens.
   * @param string[] $matches from PCRE backreferences.
   * @return string
   * @access private
   */
  private function _decodeEncodedWordList($matches)
  {
    $decodedWords = array();
    $encodedWords = preg_split('/' . $this->_grammar['FWS'] . '+/',
      $matches[0]
      );
    foreach ($encodedWords as $word)
    {
      $word = substr($word, 2, -2); //Remove the =? and ?=
      $tokens = explode('?', $word);
      $encoding = strtoupper($tokens[1]);
      switch ($encoding)
      {
        case 'Q':
          $decodedWords[] = quoted_printable_decode(
            str_replace('_', ' ', $tokens[2])
            );
          break;
        case 'B':
          $decodedWords[] = base64_decode($tokens[2]);
          break;
        default: //Not a known encoding scheme
          $decodedWords[] = $word;
      }
    }
    return implode('', $decodedWords);
  }
  
}
