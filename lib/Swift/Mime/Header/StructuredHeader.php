<?php

/*
 A Structured Mime Header in Swift Mailer.
 
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

require_once dirname(__FILE__) . '/UnstructuredHeader.php';
require_once dirname(__FILE__) . '/../HeaderEncoder.php';

/**
 * A Structured MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_StructuredHeader
  extends Swift_Mime_Header_UnstructuredHeader
{
  
  /**
   * The value of this Header, cached.
   * @var string
   * @access private
   */
  private $_cachedValue = null;
  
  /**
   * Special characters used in the syntax which need to be escaped.
   * @var string[]
   * @access private
   */
  private $_specials = array();
  
  /**
   * Tokens defined in RFC 2822 (and some related RFCs).
   * @var string[]
   * @access protected
   */
  protected $grammar = array();
  
  /**
   * Creates a new StructuredHeader with the given $name and $id.
   * @param string $name
   * @param mixed $id, optional as string or string[]
   * @param string $charset, optional
   * @param Swift_Mime_HeaderEncoder $encoder, optional
   */
  public function __construct($name, $value = null, $charset = null,
    Swift_Mime_HeaderEncoder $encoder = null)
  {
    parent::__construct($name, $value, $charset, $encoder);
    
    $this->_specials = array(
      '\\', '(', ')', '<', '>', '[', ']',
      ':', ';', '@', ',', '.', '"'
      );
      
    //TODO: unstructured, address-list
    
    /*** Refer to RFC 2822 for ABNF grammar ***/
    
    //All basic building blocks
    $this->grammar['NO-WS-CTL'] = '[\x01-\x08\x0B\x0C\x0E-\x19\x7F]';
    $this->grammar['WSP'] = '[ \t]';
    $this->grammar['CRLF'] = '(?:\r\n)';
    $this->grammar['FWS'] = '(?:(?:' . $this->grammar['WSP'] . '*' .
        $this->grammar['CRLF'] . ')?' . $this->grammar['WSP'] . ')';
    $this->grammar['text'] = '[\x00-\x08\x0B\x0C\x0E-\x7F]';
    $this->grammar['quoted-pair'] = '(?:\\\\' . $this->grammar['text'] . ')';
    $this->grammar['ctext'] = '(?:' . $this->grammar['NO-WS-CTL'] .
        '|[\x21-\x27\x2A-\x5B\x5D-\x7E])';
    //Uses recursive PCRE (?1) -- could be a weak point??
    $this->grammar['ccontent'] = '(?:' . $this->grammar['ctext'] . '|' .
        $this->grammar['quoted-pair'] . '|(?1))';
    $this->grammar['comment'] = '(\((?:' . $this->grammar['FWS'] . '|' .
        $this->grammar['ccontent']. ')*' . $this->grammar['FWS'] . '?\))';
    $this->grammar['CFWS'] = '(?:(?:' . $this->grammar['FWS'] . '?' .
        $this->grammar['comment'] . ')*(?:(?:' . $this->grammar['FWS'] . '?' .
        $this->grammar['comment'] . ')|' . $this->grammar['FWS'] . '))';
    $this->grammar['qtext'] = '(?:' . $this->grammar['NO-WS-CTL'] .
        '|[\x21\x23-\x5B\x5D-\x7E])';
    $this->grammar['qcontent'] = '(?:' . $this->grammar['qtext'] . '|' .
        $this->grammar['quoted-pair'] . ')';
    $this->grammar['quoted-string'] = '(?:' . $this->grammar['CFWS'] . '?"' .
        '(' . $this->grammar['FWS'] . '?' . $this->grammar['qcontent'] . ')*' .
        $this->grammar['FWS'] . '?"' . $this->grammar['CFWS'] . '?)';
    $this->grammar['atext'] = '[a-zA-Z0-9!#\$%&\'\*\+\-\/=\?\^_`\{\}\|~]';
    $this->grammar['atom'] = '(?:' . $this->grammar['CFWS'] . '?' .
        $this->grammar['atext'] . '+' . $this->grammar['CFWS'] . '?)';
    $this->grammar['dot-atom-text'] = '(?:' . $this->grammar['atext'] . '+' .
        '(\.' . $this->grammar['atext'] . '+)*)';
    $this->grammar['dot-atom'] = '(?:' . $this->grammar['CFWS'] . '?' .
        $this->grammar['dot-atom-text'] . '+' . $this->grammar['CFWS'] . '?)';
    $this->grammar['word'] = '(?:' . $this->grammar['atom'] . '|' .
        $this->grammar['quoted-string'] . ')';
    $this->grammar['phrase'] = '(?:' . $this->grammar['word'] . '+?)';
    $this->grammar['no-fold-quote'] = '(?:"(?:' . $this->grammar['qtext'] .
        '|' . $this->grammar['quoted-pair'] . ')*")';
    $this->grammar['dtext'] = '(?:' . $this->grammar['NO-WS-CTL'] .
        '|[\x21-\x5A\x5E-\x7E])';
    $this->grammar['no-fold-literal'] = '(?:\[(?:' . $this->grammar['dtext'] .
        '|' . $this->grammar['quoted-pair'] . ')*\])';
    
    //Message IDs
    $this->grammar['id-left'] = '(?:' . $this->grammar['dot-atom-text'] . '|' .
        $this->grammar['no-fold-quote'] . ')';
    $this->grammar['id-right'] = '(?:' . $this->grammar['dot-atom-text'] . '|' .
        $this->grammar['no-fold-literal'] . ')';
    $this->grammar['msg-id'] = '(?:' . $this->grammar['CFWS'] . '?<' .
        $this->grammar['id-left'] . '@' . $this->grammar['id-right'] . '>' .
        $this->grammar['CFWS'] . '?)';
    
    //Addresses, mailboxes and paths
    $this->grammar['display-name'] = $this->grammar['phrase'];
    $this->grammar['local-part'] = '(?:' . $this->grammar['dot-atom'] . '|' .
        $this->grammar['quoted-string'] . ')';
    $this->grammar['dcontent'] = '(?:' . $this->grammar['dtext'] . '|' .
        $this->grammar['quoted-pair'] . ')';
    $this->grammar['domain-literal'] = '(?:' . $this->grammar['CFWS'] . '?\[(' .
        $this->grammar['FWS'] . '?' . $this->grammar['dcontent'] . ')*?' .
        $this->grammar['FWS'] . '?\]' . $this->grammar['CFWS'] . '?)';
    $this->grammar['domain'] = '(?:' . $this->grammar['dot-atom'] . '|' .
        $this->grammar['domain-literal'] . ')';
    $this->grammar['addr-spec'] = '(?:' . $this->grammar['local-part'] . '@' .
        $this->grammar['domain'] . ')';
    $this->grammar['path'] = '(?:' . $this->grammar['CFWS'] . '?<(?:' .
        $this->grammar['CFWS'] . '|' . $this->grammar['addr-spec'] . ')?>' .
        $this->grammar['CFWS'] . '?)';
    $this->grammar['angle-addr'] = '(?:' . $this->grammar['CFWS'] . '?<' .
        $this->grammar['addr-spec'] . '>' . $this->grammar['CFWS'] . '?)';
    $this->grammar['name-addr'] = '(?:'. $this->grammar['display-name'] . '?' .
        $this->grammar['angle-addr'] . ')';
    $this->grammar['mailbox'] = '(?:' . $this->grammar['name-addr'] . '|' .
        $this->grammar['addr-spec'] . ')';
    $this->grammar['mailbox-list'] = '(?:' . $this->grammar['mailbox'] . '(?:,' .
        $this->grammar['mailbox'] . ')*)';
    
    //Encoded words (RFC 2047)
    $this->grammar['token'] = '(?:[^\(\)<>@,;:"\/\[\]\?\.=]+)';
    $this->grammar['charset'] = $this->grammar['token'];
    $this->grammar['encoding'] = $this->grammar['token'];
    $this->grammar['encoded-text'] = '(?:[\x21-\x3E\x40-\x7E]+)';
    $this->grammar['encoded-word'] = '(?:=\?' . $this->grammar['charset'] .
        '\?' . $this->grammar['encoding'] . '\?' .
        $this->grammar['encoded-text'] . '\?=)';
    
    //Date and time
    $this->grammar['day-name'] = '(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun)';
    $this->grammar['day-of-week'] = '(?:' . $this->grammar['FWS'] . '?' .
        $this->grammar['day-name'] . ')';
    $this->grammar['day'] = '(?:' . $this->grammar['FWS'] . '?[0-9]{1,2})';
    $this->grammar['month-name'] = '(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)';
    $this->grammar['month'] = '(?:' . $this->grammar['FWS'] .
        $this->grammar['month-name'] . $this->grammar['FWS'] . ')';
    $this->grammar['year'] = '(?:[0-9]{4,})';
    $this->grammar['date'] = '(?:' . $this->grammar['day'] .
        $this->grammar['month'] . $this->grammar['year'] . ')';
    $this->grammar['hour'] = '(?:[0-9]{2})';
    $this->grammar['minute'] = '(?:[0-9]{2})';
    $this->grammar['second'] = '(?:[0-9]{2})';
    $this->grammar['time-of-day'] = '(?:' . $this->grammar['hour'] . ':' .
        $this->grammar['minute'] . '(?::' . $this->grammar['second'] . ')?)';
    $this->grammar['zone'] = '(?:[\+\-][0-9]{4})';
    $this->grammar['time'] = '(?:' . $this->grammar['time-of-day'] .
        $this->grammar['FWS'] . $this->grammar['zone'] . ')';
    $this->grammar['date-time'] = '(?:(?:' . $this->grammar['day-of-week'] .
        ',)?' . $this->grammar['date'] . $this->grammar['FWS'] .
        $this->grammar['time'] . $this->grammar['CFWS'] . '?)';
    
    //Received headers
    $this->grammar['item-name'] = '(?:[a-zA-Z](?:-?[a-zA-Z0-9])*)';
    $this->grammar['item-value'] = '(?:' . $this->grammar['angle-addr'] . '+|' .
        $this->grammar['addr-spec'] . '|' . $this->grammar['atom'] . '|' .
        $this->grammar['domain'] . '|' . $this->grammar['msg-id'] . ')';
    $this->grammar['name-val-pair'] = '(?:' . $this->grammar['item-name'] .
        $this->grammar['CFWS'] . $this->grammar['item-value'] . ')';
    $this->grammar['name-val-list'] = '(?:' . $this->grammar['CFWS'] . '?(?:' .
        $this->grammar['name-val-pair'] . '(?:' . $this->grammar['CFWS'] .
        $this->grammar['name-val-pair'] . ')*)?)';
  }
  
  // -- Protected methods
  
  /**
   * Escape special characters in a string (convert to quoted-pairs).
   * @param string $token
   * @return string
   * @access protected
   */
  protected function escapeSpecials($token)
  {
    foreach ($this->_specials as $char)
    {
      $token = str_replace($char, '\\' . $char, $token);
    }
    return $token;
  }
  
  /**
   * Produces a compliant, formatted RFC 2822 'phrase' based on the string given.
   * @param string $string as displayed
   * @param boolean $shorten the first line to make remove for header name
   * @return string
   * @access protected
   */
  protected function createPhrase($string, $shorten = false)
  {
    //Treat token as exactly what was given
    $phraseStr = $string;
    
    //If it's not valid
    if (!preg_match('/^' . $this->grammar['phrase'] . '$/D', $phraseStr))
    {
      // .. but it is just ascii text, try escaping some characters
      // and make it a quoted-string
      if (preg_match('/^' . $this->grammar['text'] . '*$/D', $phraseStr))
      {
        $phraseStr = $this->escapeSpecials($phraseStr);
        $phraseStr = '"' . $phraseStr . '"';
      }
      else // ... otherwise it needs encoding
      {
        //Determine space remaining on line if first line
        if ($shorten)
        {
          $usedLength = strlen($this->getName() . ': ');
        }
        else
        {
          $usedLength = 0;
        }
        $phraseStr = $this->encodeWords($string, $usedLength);
      }
    }
    
    return $phraseStr;
  }
  
  /**
   * Decode/parse out a RFC 2822 compliant display-name to get the actual
   * text value.
   * @param string $displayName
   * @return string
   * @access protected
   */
  protected function decodePhrase($phrase)
  {
    //Get rid of any CFWS
    $string = $this->trimCFWS($phrase);
    if ('' == $string)
    {
      return null;
    }
    
    if (!preg_match('/^' . $this->grammar['phrase'] . '$/D', $string))
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
   * Remove CFWS from the left and right of the given token.
   * @param string $token
   * @param string $sides to trim from
   * @return string
   * @access protected
   */
  protected function trimCFWS($token, $sides = 'both')
  {
    switch ($sides)
    {
      case 'right':
        $pattern = '/' . $this->grammar['CFWS'] . '$/';
        break;
      case 'left':
        $pattern = '/^' . $this->grammar['CFWS'] . '/';
        break;
      case 'both':
      default:
        $pattern = '/^' . $this->grammar['CFWS'] . '|' .
      $this->grammar['CFWS'] . '$/';
    }
    return preg_replace($pattern, '', $token);
  }
  
  /**
   * Removes all CFWS from the given token.
   * @param string $token
   * @return string
   * @access protected
   */
  protected function stripCFWS($token)
  {
    return preg_replace('/' . $this->grammar['CFWS'] . '/', '', $token);
  }
  
  /**
   * Decodes encoded-word tokens as defined by RFC 2047.
   * @param string $token
   * @return string
   * @access protected
   */
  protected function decodeEncodedWords($token)
  {
    return preg_replace_callback(
      '/(?:' . $this->grammar['encoded-word'] .
      $this->grammar['FWS'] . '+)*' .
      $this->grammar['encoded-word'] . '/',
      array($this, '_decodeEncodedWordList'),
      $token
      );
  }
  
  protected function unfoldWhiteSpace($token)
  {
    return preg_replace('/\r\n([ \t])/', '$1', $token);
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
   * Callback which decodes adjacent groups of encoded-word tokens.
   * @param string[] $matches from PCRE backreferences.
   * @return string
   * @access private
   */
  private function _decodeEncodedWordList($matches)
  {
    $decodedWords = array();
    $encodedWords = preg_split('/' . $this->grammar['FWS'] . '+/',
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
