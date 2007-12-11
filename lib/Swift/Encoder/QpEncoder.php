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
   * The CharacterStream which is used for reading characters (as opposed to bytes).
   * @var Swift_CharacterStream
   * @access private
   */
  private $_charStream;
  
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
   * Temporarily grows as a string to be returned during some internal writes.
   * @var string
   * @access private
   */
  private $_temporaryReturnString;
  
  /**
   * Creates a new QpEncoder for the given CharacterStream.
   * @param Swift_CharacterStream $charStream to use for reading characters
   */
  public function __construct(Swift_CharacterStream $charStream)
  {
    $this->_charStream = $charStream;
    
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
   * @param string $string to encode
   * @param int $firstLineOffset
   * @return string
   */
  public function encodeString($string, $firstLineOffset = 0)
  {
    //Empty the CharacterStream and import the string to it
    $this->_charStream->flushContents();
    $this->_charStream->importString($string);
    
    //Set the temporary string to write into
    $this->_temporaryReturnString = '';
    
    //Encode the CharacterStream using an append method as a callback
    $this->_encodeCharacterStreamCallback($this->_charStream,
      array($this, '_appendToTemporaryReturnString'), $firstLineOffset
      );
    
    //Copy the temporary return value
    $ret = $this->_temporaryReturnString;
    
    //Unset the temporary return value
    $this->_temporaryReturnString = null;
    
    //Return string with data appended via callback
    return $ret;
  }
  
  /**
   * Encode stream $in to stream $out.
   * QP encoded strings have a maximum line length of 76 characters.
   * If the first line needs to be shorter, indicate the difference with
   * $firstLineOffset.
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
    //Variables used for tracking
    $nextChar = null; $deferredLwspChar = null;
    $expectedLfChar = false;
    $lineLength = 0; $lineCount = 0;
    
    do
    {
      //Zero the firstLineOffset if no longer on first line
      $firstLineOffset = $lineCount > 0 ? 0 : $firstLineOffset;
      
      //If just starting, read from stream, else use $nextChar from last loop
      if (false === $thisChar = is_null($nextChar) ?
        $charStream->read(1) : $nextChar)
      {
        break;
      }
      
      //Always have knowledge of at least two chars at a time
      $nextChar = $charStream->read(1);
      $thisCharEncoded = $this->_encodeCharacter($thisChar);
      
      $maxLineLength = (false !== $nextChar ? 75 : 76) - $firstLineOffset;
      
      //Currently looking at LWSP followed by CR
      if (in_array(ord($thisChar), $this->_lwspBytes)
        && $this->_crlfChars['CR'] == $nextChar)
      {
        $deferredLwspChar = $thisChar;
      }
      //Looking at LWSP at end of string
      elseif (in_array(ord($thisChar), $this->_lwspBytes)
        && false === $nextChar)
      {
        $this->_writeSequenceToCallback(sprintf('=%02X', ord($thisChar)),
            $callback, $maxLineLength, $lineLength, $lineCount);
      }
      //Currently looking at CRLF
      elseif ($this->_crlfChars['CR'] == $thisChar
        && $this->_crlfChars['LF'] == $nextChar)
      {
        //If a LWSP char was deferred due to the CR
        if (!is_null($deferredLwspChar))
        {
          $this->_writeSequenceToCallback(sprintf('=%02X', ord($deferredLwspChar)),
            $callback, $maxLineLength, $lineLength, $lineCount);
          $deferredLwspChar = null;
        }
        
        $this->_writeSequenceToCallback($thisChar, $callback, $maxLineLength,
          $lineLength, $lineCount);
        $expectedLfChar = true;
      }
      //Currently looking at an expected LF (following a CR)
      elseif ($this->_crlfChars['LF'] == $thisChar && $expectedLfChar)
      {
        $this->_writeSequenceToCallback($thisChar, $callback, $maxLineLength,
          $lineLength, $lineCount);
        $expectedLfChar = false;
      }
      //Nothing special about this character, just write it
      else
      {
        //If a LWSP was deferred but not used, write it as normal
        if (!is_null($deferredLwspChar))
        {
          $this->_writeSequenceToCallback($deferredLwspChar, $callback,
            $maxLineLength, $lineLength, $lineCount);
          $deferredLwspChar = null;
        }
        
        //Write the endoded character as normal
        $this->_writeSequenceToCallback($thisCharEncoded, $callback,
            $maxLineLength, $lineLength, $lineCount);
      }
    }
    while(false !== $nextChar);
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
   * Internal method to write a sequence of bytes into a callback method.
   * @param string $sequence of bytes
   * @param callback $callback to send $sequence to
   * @param int $maxLineLength
   * @param int &$lineLength currently
   * @param int &$lineCount currently
   * @access private
   */
  private function _writeSequenceToCallback($sequence, $callback, $maxLineLength,
    &$lineLength, &$lineCount)
  {
    $sequenceLength = strlen($sequence);
    $lineLength += $sequenceLength;
    if ($maxLineLength < $lineLength)
    {
      $sequence = "=\r\n" . $sequence;
      $lineLength = $sequenceLength;
      ++$lineCount;
    }
    
    call_user_func($callback, $sequence);
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
   * Internal callback method which appends bytes to the end of a string
   * held internally temporarily.
   * @param string $bytes
   * @access private
   */
  private function _appendToTemporaryReturnString($bytes)
  {
    $this->_temporaryReturnString .= $bytes;
  }
  
}
