<?php

/*
 The Quoted Printable encoder in Swift Mailer.
 
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


require_once dirname(__FILE__) . '/../Encoder.php';
require_once dirname(__FILE__) . '/../ByteStream.php';
require_once dirname(__FILE__) . '/../CharacterStream.php';

/**
 * Handles Quoted Printable (QP) Encoding in Swift Mailer.
 * Possibly the most accurate RFC 2045 QP implementation found in PHP.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
class Swift_Encoder_QpEncoder implements Swift_Encoder
{
  
  /**
   * The character set being encoded.
   * @var string
   * @access private
   */
  private $_charset;
  
  /**
   * The CharacterStream which is used for reading characters (as opposed to bytes).
   * @var Swift_CharacterStream
   * @access private
   */
  private $_charStream;
  
  /**
   * True if the multibyte encoding library is present.
   * @var boolean
   * @access private
   */
  private $_hasMb = false;
  
  /**
   * Linear whitespace bytes.
   * @var int[]
   * @access private
   */
  private $_lwspBytes = array();
  
  /**
   * Linear whitespace characters.
   * @var string[]
   * @access private
   */
  private $_lwspChars = array();
  
  /**
   * CR and LF bytes.
   * @var int[]
   * @access private
   */
  private $_crlfBytes = array();
  
  /**
   * CR and LF characters.
   * @var string[]
   * @access private
   */
  private $_crlfChars = array();
  
  /**
   * Bytes to allow through the encoder without being translated.
   * @var int[]
   * @access private
   */
  private $_permittedBytes = array();
  
  /**
   * Temporarily gets populated with a ByteStream during some internal writes.
   * @var Swift_ByteStream
   * @access private
   */
  private $_temporaryInputByteStream;
  
  /**
   * Creates a new QpEncoder for the given charset.
   * @param string $charset
   * @param Swift_CharacterStream $charStream to use for reading characters
   */
  public function __construct($charset, Swift_CharacterStream $charStream)
  {
    $this->_charset = $charset;
    $this->_charStream = $charStream;
    $this->_charStream->setCharset($this->_charset);
    
    $this->_hasMb = (
      function_exists('mb_subtr')
      && function_exists('mb_strlen')
      && function_exists('mb_internal_encoding')
      );
    
    $this->_crlfBytes = array('CR' => 0x0D, 'LF' => 0x0A);
    $this->_crlfChars = array(
      'CR' => chr($this->_crlfBytes['CR']), 'LF' => chr($this->_crlfBytes['LF'])
      );
    
    $this->_lwspBytes = array('HT' => 0x09, 'SPACE' => 0x20);
    $this->_lwspChars = array(
      'HT' => chr($this->_lwspBytes['HT']),
      'SPACE' => chr($this->_lwspBytes['SPACE'])
      );
    
    $this->_permittedBytes = array_merge(
      $this->_lwspBytes, range(0x21, 0x3C), range(0x3E, 0x7E)
      );
  }
  
  /**
   * Takes an unencoded string and produces a QP encoded string from it.
   * QP encoded strings have a maximum line length of 76 characters.
   * If the first line needs to be shorter, indicate the difference with
   * $firstLineOffset.
   *
   * @param string $string to encode
   * @param int $firstLineOffset
   * @return string
   */
  public function encodeString($string, $firstLineOffset = 0)
  {
    //RFC 2045, 6.7 (4) -- Treat lines in their canonical form
    $lines = explode("\r\n", $string);
    
    //Apply rules line-for-line
    foreach ($lines as $lineNumber => $line)
    {
      $wrappedLines = array();
      $lineEncoded = '';
      
      //RFC 2045, 6.7 (1 & 2) -- Encode bytes except those permitted
      while (false !== $charEncoded = $this->_encodeCharacter(
        $this->_shiftCharacter($line)))
      {
        //76 -1 to account for = needed for possible soft break
        $maxLength = 75 - $firstLineOffset;
        //Stop using an offset if already line wrapped
        if (0 != count($wrappedLines) || 0 != $lineNumber)
        {
          $firstLineOffset = 0;
        }
        
        //RFC 2045, 6.7 (3) -- No LWSP at line ending
        if (0 == strlen($line))
        {
          //End of line so no need to account for a possible soft break
          ++$maxLength;
          
          $lastOrdinal = ord(substr($charEncoded, -1));
          if (in_array($lastOrdinal, $this->_lwspBytes))
          {
            $charEncodedLwsp = substr($charEncoded, 0, -1) .
              sprintf('=%02X', $lastOrdinal);
            
            //If soft break is going to occur after encoding LWSP
            // then soft break before and don't encode instead
            if (strlen($lineEncoded . $charEncodedLwsp) > $maxLength)
            {
              $wrappedLines[] = $lineEncoded;
              $lineEncoded = '';
            }
            else //Force ending LWSP encoding
            {
              $charEncoded = $charEncodedLwsp;
            }
          }
        }
        
        //RFC 2045, 6.7 (5) -- Soft line breaks before 76 chars
        if (strlen($lineEncoded . $charEncoded) > $maxLength)
        {
          $wrappedLines[] = $lineEncoded;
          $lineEncoded = '';
        }
        
        $lineEncoded .= $charEncoded;
      }
      
      $wrappedLines[] = $lineEncoded;
      
      $lines[$lineNumber] = implode("=\r\n", $wrappedLines);
    }
    
    //RFC 2045, 6.7 (4)
    return implode("\r\n", $lines);
  }
  
  /**
   * Encode stream $in to stream $out.
   * @param Swift_ByteStream $os output stream
   * @param Swift_ByteStream $is input stream
   * @param int $firstLineOffset
   */
  public function encodeByteStream(
    Swift_ByteStream $os, Swift_ByteStream $is, $firstLineOffset = 0)
  {
    //Empty the CharacterStream and import the ByteStream to it
    $this->_charStream->flushContents();
    $this->_charStream->importByteStream($os);
    
    //Set the temporary byte stream to write into
    $this->_temporaryInputByteStream = $is;
    
    //Encode the CharacterStream using an append method as a callback
    $this->_encodeCharacterStreamCallback($this->_charStream,
      array($this, '_appendToTemporaryInputByteStream'), $firstLineOffset
      );
    
    //Unset the temporary ByteStream
    $this->_temporaryInputByteStream = null;
    
    //Return ByteStream with data appended via callback
    return $is;
  }
  
  /**
   * Internal callback method which appends bytes to the end of a ByteStream
   * held internally temporarily.
   * @param string $bytes
   * @access private
   */
  private function _appendToTemporaryInputByteStream($bytes)
  {
    $this->_temporaryInputByteStream->write($bytes);
  }
  
  /**
   * Internal method which does the bulk of the work, repeatedly invoking an
   * internal callback method to append bytes to the output.
   * @param Swift_CharacterStream $charStream to read from
   * @param callback $callback for appending
   * @param int $firstlineOffset
   * @access private
   */
  private function _encodeCharacterStreamCallback(
    Swift_CharacterStream $charStream, $callback, $firstLineOffset = 0)
  {
    $nextChar = null;
    $deferredLwspChar = null;
    $expectedLfChar = false;
    $lineLength = 0;
    $lineCount = 0;
    
    do
    {
      if (0 < $lineCount)
      {
        $firstLineOffset = 0;
      }
      
      //If just starting, read from stream, else use $nextChar from last loop
      $thisChar = is_null($nextChar) ? $charStream->read(1) : $nextChar;
      
      //No characters in stream
      if (false === $thisChar)
      {
        break;
      }
      
      //Always have knowledge of at least two chars at a time
      $nextChar = $charStream->read(1);
      $thisCharEncoded = $this->_encodeCharacter($thisChar);
      
      $maxLineLength = 76 - $firstLineOffset;
      if (false !== $nextChar)
      {
        $maxLineLength--;
      }
      
      //Currently looking at LWSP followed by CR
      if (in_array(ord($thisChar), $this->_lwspBytes)
        && $this->_crlfChars['CR'] == $nextChar)
      {
        $deferredLwspChar = $thisChar;
      }
      //Currently looking at CRLF
      elseif ($this->_crlfChars['CR'] == $thisChar
        && $this->_crlfChars['LF'] == $nextChar)
      {
        //If a LWSP char was deferred due to the CR
        if (!is_null($deferredLwspChar))
        {
          $write = sprintf('=%02X', ord($deferredLwspChar));
          $writeLength = strlen($write);
          if ($maxLineLength < $lineLength + $writeLength)
          {
            $write = "=\r\n" . $write;
            $lineLength = $writeLength;
            $lineCount++;
          }
          else
          {
            $lineLength += $writeLength;
          }
          call_user_func($callback, $write);
          $deferredLwspChar = null;
        }
        
        if ($maxLineLength < $lineLength + 1)
        {
          $thisChar = "=\r\n" . $thisChar;
          $lineLength = 1;
          $lineCount++;
        }
        else
        {
          $lineLength += 1;
        }
        
        //Write CR unencoded and inform loop the next LF is ok
        call_user_func($callback, $thisChar);
        $expectedLfChar = true;
      }
      //Currently looking at an expected LF (following a CR)
      elseif ($this->_crlfChars['LF'] == $thisChar && $expectedLfChar)
      {
        if ($maxLineLength < $lineLength + 1)
        {
          $thisChar = "=\r\n" . $thisChar;
          $lineLength = 1;
          $lineCount++;
        }
        else
        {
          $lineLength += 1;
        }
        
        //Write unencoded
        call_user_func($callback, $thisChar);
        $expectedLfChar = false;
      }
      else
      {
        //If a LWSP was deferred but not used, write it as normal
        if (!is_null($deferredLwspChar))
        {
          if ($maxLineLength < $lineLength + 1)
          {
            $deferredLwspChar = "=\r\n" . $deferredLwspChar;
            $lineLength = 1;
            $lineCount++;
          }
          else
          {
            $lineLength += 1;
          }
        
          call_user_func($callback, $deferredLwspChar);
          $deferredLwspChar = null;
        }
        
        //Write encoded character as usual
        $encodedLength = strlen($thisCharEncoded);
        if ($maxLineLength < $lineLength + $encodedLength)
        {
          $thisCharEncoded = "=\r\n" . $thisCharEncoded;
          $lineLength = $encodedLength;
          $lineCount++;
        }
        else
        {
          $lineLength += $encodedLength;
        }
        
        call_user_func($callback, $thisCharEncoded);
      }
    }
    while(false !== $nextChar);
  }
  
  /**
   * Shift a single character off the start of the string and shorten by 1.
   * The character may contain more than a single byte.
   * @param string &$string
   * @return string
   */
  private function _shiftCharacter(&$string)
  {
    $char = $this->_substr($string, 0, 1, $this->_charset);
    $string = $this->_substr($string, 1, null, $this->_charset);
    return $char;
  }
  
  /**
   * Encode a single character (maybe multi-byte).
   * @param string $char
   * @return string
   */
  private function _encodeCharacter($char)
  {
    if (!is_string($char))
    {
      return false;
    }
    
    $charEncoded = '';
    
    foreach (unpack('C*', $char) as $octet)
    {
      if (!in_array($octet, $this->_permittedBytes))
      {
        $charEncoded .= sprintf('=%02X', $octet);
      }
      else
      {
        $charEncoded .= pack('C', $octet);
      }
    }
    
    return $charEncoded;
  }
  
  /**
   * Selective substr() which uses mb_substr() if possible.
   * @param string $string
   * @param int $start
   * @param int $length
   * @param string $encoding
   * @return string
   */
  private function _substr($string, $start, $length = null, $encoding = null)
  {
    if ($this->_hasMb)
    {
      if (is_null($length))
      {
        $length = mb_strlen($string);
      }
      
      if (is_null($encoding))
      {
        $encoding = mb_internal_encoding();
      }
      
      return mb_substr($string, $start, $length, $encoding);
    }
    else
    {
      if (is_null($length))
      {
        $length = strlen($string);
      }
      
      return substr($string, $start, $length);
    }
  }
  
}
